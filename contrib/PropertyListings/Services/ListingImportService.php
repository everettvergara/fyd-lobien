<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ListingImportService
{
    public function __construct(
        protected ListingExportService $export,
        protected ListingPersistenceService $persistence,
        protected ListingLookupRegistry $registry,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return $this->export->headers();
    }

    /**
     * @return array{
     *     headers: array<int, string>,
     *     rows: array<int, array<string, mixed>>,
     *     summary: array{create: int, update: int, errors: int},
     *     row_errors: array<int, array<int, string>>
     * }
     */
    public function preview(UploadedFile $file): array
    {
        $parsed = $this->parseCsv($file);

        return [
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'summary' => $this->summarize($parsed['rows']),
            'row_errors' => $this->validateRows($parsed['rows']),
        ];
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     units_created: int,
     *     units_updated: int,
     *     errors: array<int, array<int, string>>
     * }
     */
    public function commit(UploadedFile $file): array
    {
        $parsed = $this->parseCsv($file);
        $rowErrors = $this->validateRows($parsed['rows']);

        if ($rowErrors !== []) {
            return [
                'created' => 0,
                'updated' => 0,
                'units_created' => 0,
                'units_updated' => 0,
                'errors' => $rowErrors,
            ];
        }

        $grouped = collect($parsed['rows'])->groupBy(fn (array $row) => (string) $row['code']);
        $created = 0;
        $updated = 0;
        $unitsCreated = 0;
        $unitsUpdated = 0;

        DB::transaction(function () use ($grouped, &$created, &$updated, &$unitsCreated, &$unitsUpdated) {
            foreach ($grouped as $code => $rows) {
                /** @var array<int, array<string, mixed>> $rows */
                $first = $rows->first();
                $listing = Listing::query()->where('code', $code)->first();
                $payload = $this->listingPayloadFromRow($first);
                $payload['spec'] = $this->specPayloadFromRow($first);
                $payload['building_service'] = $this->buildingPayloadFromRow($first);
                $payload['other_info'] = $this->otherInfoPayloadFromRow($first);
                $payload['units'] = $this->unitPayloadsFromRows($rows->all(), $listing);

                if ($listing === null) {
                    $this->persistence->create($payload);
                    $created++;
                } else {
                    $beforeCount = $listing->units()->count();
                    $this->persistence->update($listing, $payload);
                    $listing->refresh();
                    $afterCount = $listing->units()->count();
                    $updated++;

                    $unitsCreated += max(0, $afterCount - $beforeCount);
                    $unitsUpdated += min($beforeCount, $rows->count());
                }

                if ($listing === null) {
                    $unitsCreated += count($payload['units']);
                }
            }
        });

        ActivityLogger::log('property-listings', 'imported', new Listing, [
            'created' => $created,
            'updated' => $updated,
            'units_created' => $unitsCreated,
            'units_updated' => $unitsUpdated,
        ]);

        return [
            'created' => $created,
            'updated' => $updated,
            'units_created' => $unitsCreated,
            'units_updated' => $unitsUpdated,
            'errors' => [],
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, mixed>>}
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return ['headers' => $this->headers(), 'rows' => []];
        }

        $rawHeaders = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $rawHeaders);
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }

            $rows[] = $row;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{create: int, update: int, errors: int}
     */
    protected function summarize(array $rows): array
    {
        $codes = collect($rows)->pluck('code')->filter()->unique();
        $existing = Listing::query()->whereIn('code', $codes->all())->pluck('code')->all();

        $rowErrors = $this->validateRows($rows);

        return [
            'create' => $codes->diff($existing)->count(),
            'update' => $codes->intersect($existing)->count(),
            'errors' => count($rowErrors),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<int, string>>
     */
    protected function validateRows(array $rows): array
    {
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowErrors = [];

            if (trim((string) ($row['code'] ?? '')) === '') {
                $rowErrors[] = 'Code is required.';
            }

            if (trim((string) ($row['name'] ?? '')) === '') {
                $rowErrors[] = 'Name is required.';
            }

            if (trim((string) ($row['province'] ?? '')) === '') {
                $rowErrors[] = 'Province is required.';
            }

            if (trim((string) ($row['city'] ?? '')) === '') {
                $rowErrors[] = 'City is required.';
            }

            foreach ($this->lookupFields() as $field => $group) {
                $value = trim((string) ($row[$field] ?? ''));

                if ($value === '') {
                    continue;
                }

                if (! $this->registry->hasValue($group, $value)) {
                    $rowErrors[] = "Invalid {$field} value \"{$value}\".";
                }
            }

            if ($rowErrors !== []) {
                $errors[$rowNumber] = $rowErrors;
            }
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    protected function lookupFields(): array
    {
        return [
            'completion_status' => ListingLookupGroups::COMPLETION_STATUS,
            'grade' => ListingLookupGroups::GRADE,
            'peza_accreditation' => ListingLookupGroups::PEZA_ACCREDITATION,
            'handover_condition' => ListingLookupGroups::HANDOVER_CONDITION,
            'availability' => ListingLookupGroups::AVAILABILITY,
            'bedrooms' => ListingLookupGroups::BEDROOMS,
            'property_type' => ListingLookupGroups::PROPERTY_TYPE,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function listingPayloadFromRow(array $row): array
    {
        return [
            'code' => trim((string) $row['code']),
            'name' => trim((string) $row['name']),
            'province' => trim((string) $row['province']),
            'city' => trim((string) $row['city']),
            'brgy' => $this->nullableString($row['brgy'] ?? null),
            'address' => $this->nullableString($row['address'] ?? null),
            'office_rental_rate' => $this->nullableDecimal($row['office_rental_rate'] ?? null),
            'total_area_size' => $this->nullableDecimal($row['total_area_size'] ?? null),
            'unit_market_size' => $this->nullableDecimal($row['unit_market_size'] ?? null),
            'retail_market_rate' => $this->nullableDecimal($row['retail_market_rate'] ?? null),
            'completion_status' => $this->nullableString($row['completion_status'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function specPayloadFromRow(array $row): array
    {
        return [
            'developer' => $this->nullableString($row['developer'] ?? null),
            'grade' => $this->nullableString($row['grade'] ?? null),
            'completion_year' => $this->nullableInt($row['completion_year'] ?? null),
            'completion_qtr' => $this->nullableString($row['completion_qtr'] ?? null),
            'no_of_floors' => $this->nullableString($row['no_of_floors'] ?? null),
            'no_of_basement' => $this->nullableString($row['no_of_basement'] ?? null),
            'density_ratio' => $this->nullableString($row['density_ratio'] ?? null),
            'parking_allocation' => $this->nullableString($row['parking_allocation'] ?? null),
            'floor_to_ceiling_height' => $this->nullableString($row['floor_to_ceiling_height'] ?? null),
            'gross_leasable_area' => $this->nullableDecimal($row['gross_leasable_area'] ?? null),
            'typical_floor_area' => $this->nullableDecimal($row['typical_floor_area'] ?? null),
            'typical_retail_floor_area' => $this->nullableDecimal($row['typical_retail_floor_area'] ?? null),
            'floor_efficiency' => $this->nullableDecimal($row['floor_efficiency'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function buildingPayloadFromRow(array $row): array
    {
        return [
            'operating_hours' => $this->nullableString($row['operating_hours'] ?? null),
            'ac_system' => $this->nullableString($row['ac_system'] ?? null),
            'no_of_lifts_passenger' => $this->nullableInt($row['no_of_lifts_passenger'] ?? null),
            'no_of_lifts_service' => $this->nullableInt($row['no_of_lifts_service'] ?? null),
            'telco' => $this->nullableString($row['telco'] ?? null),
            'backup_power' => $this->nullableInt($row['backup_power'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function otherInfoPayloadFromRow(array $row): array
    {
        return [
            'peza_accreditation' => $this->nullableString($row['peza_accreditation'] ?? null),
            'sustainability' => $this->nullableString($row['sustainability'] ?? null),
            'other_info_visible' => $this->nullableBool($row['other_info_visible'] ?? null) ?? true,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function unitPayloadsFromRows(array $rows, ?Listing $listing): array
    {
        $units = [];

        foreach (array_values($rows) as $index => $row) {
            if ($this->isUnitRowEmpty($row)) {
                continue;
            }

            $payload = [
                'floor' => $this->nullableString($row['floor'] ?? null),
                'unit' => $this->nullableString($row['unit'] ?? null),
                'area_size' => $this->nullableDecimal($row['area_size'] ?? null),
                'rental' => $this->nullableDecimal($row['rental'] ?? null),
                'handover_condition' => $this->nullableString($row['handover_condition'] ?? null),
                'availability' => $this->nullableString($row['availability'] ?? null),
                'bedrooms' => $this->nullableString($row['bedrooms'] ?? null),
                'selling_price' => $this->nullableDecimal($row['selling_price'] ?? null),
                'property_type' => $this->nullableString($row['property_type'] ?? null),
                'for_lease' => $this->nullableBool($row['for_lease'] ?? null) ?? false,
                'for_sale' => $this->nullableBool($row['for_sale'] ?? null) ?? false,
                'last_remarks' => $this->nullableString($row['last_remarks'] ?? null),
                'sort_order' => $this->nullableInt($row['unit_sort_order'] ?? null) ?? $index,
            ];

            $unitId = $this->nullableInt($row['unit_id'] ?? null);
            if ($unitId !== null && $listing !== null) {
                $existing = ListingUnit::query()
                    ->where('listing_id', $listing->id)
                    ->whereKey($unitId)
                    ->first();

                if ($existing !== null) {
                    $payload['id'] = $existing->id;
                }
            }

            $units[] = $payload;
        }

        return $units;
    }

    protected function isUnitRowEmpty(array $row): bool
    {
        $fields = [
            'floor', 'unit', 'area_size', 'rental', 'handover_condition', 'availability',
            'bedrooms', 'selling_price', 'property_type', 'for_lease', 'for_sale', 'last_remarks',
        ];

        foreach ($fields as $field) {
            if (trim((string) ($row[$field] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function nullableDecimal(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function nullableBool(mixed $value): ?bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'y'], true);
    }
}
