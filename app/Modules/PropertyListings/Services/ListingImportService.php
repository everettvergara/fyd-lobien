<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingSlugHelper;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use App\Services\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ListingImportService
{
    protected const IMPORT_MEMORY_LIMIT = '512M';

    public function __construct(
        protected ListingExportService $export,
        protected ListingPersistenceService $persistence,
        protected ListingLookupRegistry $registry,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headers(string $type = 'header'): array
    {
        return $this->export->headers($type);
    }

    /**
     * @return array{
     *     headers: array<int, string>,
     *     rows: array<int, array<string, mixed>>,
     *     summary: array{create: int, update: int, errors: int},
     *     row_errors: array<int, array<int, string>>,
     *     field_errors: array<int, array<string, array<int, string>>>,
     *     warnings: array<int, string>,
     *     field_warnings: array<int, array<string, array<int, string>>>
     * }
     */
    public function preview(UploadedFile $file, string $type = 'header'): array
    {
        $this->ensureImportMemoryLimit();

        $type = $this->normalizeType($type);
        $parsed = $this->parseCsv($file);
        $validation = $this->validateParsed($type, $parsed['headers'], $parsed['rows']);

        return [
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'summary' => $this->summarize($type, $parsed['rows'], $validation),
            'row_errors' => $validation['row_errors'],
            'field_errors' => $validation['field_errors'],
            'batch_errors' => $validation['batch_errors'],
            'warnings' => $validation['warnings'],
            'field_warnings' => $validation['field_warnings'],
        ];
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     errors: array<int, array<int, string>>,
     *     warnings: array<int, string>
     * }
     */
    public function commit(UploadedFile $file, string $type = 'header'): array
    {
        $this->ensureImportMemoryLimit();

        $type = $this->normalizeType($type);
        $parsed = $this->parseCsv($file);
        $validation = $this->validateParsed($type, $parsed['headers'], $parsed['rows']);
        $importRows = $this->importableRows($type, $parsed['rows']);

        if ($validation['row_errors'] !== [] || $validation['batch_errors'] !== []) {
            return [
                'created' => 0,
                'updated' => 0,
                'errors' => $validation['row_errors'],
                'batch_errors' => $validation['batch_errors'],
                'warnings' => $validation['warnings'],
            ];
        }

        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($type, $importRows, &$created, &$updated) {
            foreach ($importRows as $row) {
                $result = match ($type) {
                    'header' => $this->commitHeaderRow($row),
                    'units' => $this->commitUnitRow($row),
                    'fees' => $this->commitFeeRow($row),
                };

                $result === 'created' ? $created++ : $updated++;
            }
        });

        ActivityLogger::log('property-listings', "{$type}_csv_imported", new Listing, [
            'created' => $created,
            'updated' => $updated,
        ]);

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => [],
            'batch_errors' => [],
            'warnings' => $validation['warnings'],
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
        $headers = array_map(
            fn ($header) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header) ?? '')),
            $rawHeaders,
        );
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
     * @param  array{row_errors: array<int, array<int, string>>, batch_errors: array<int, string>}  $validation
     * @return array{create: int, update: int, errors: int}
     */
    protected function summarize(string $type, array $rows, array $validation): array
    {
        $rows = $this->importableRows($type, $rows);
        $existing = match ($type) {
            'header' => Listing::query()
                ->whereIn('code', collect($rows)->pluck('code')->filter()->unique()->all())
                ->pluck('code')
                ->all(),
            'units' => $this->existingUnitKeys($rows),
            'fees' => $this->existingFeeKeys($rows),
        };

        return [
            'create' => collect($rows)->reject(fn (array $row) => in_array($this->rowKey($type, $row), $existing, true))->count(),
            'update' => collect($rows)->filter(fn (array $row) => in_array($this->rowKey($type, $row), $existing, true))->count(),
            'errors' => count($validation['row_errors']) + count($validation['batch_errors']),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{row_errors: array<int, array<int, string>>, field_errors: array<int, array<string, array<int, string>>>, batch_errors: array<int, string>, warnings: array<int, string>, field_warnings: array<int, array<string, array<int, string>>>}
     */
    protected function validateParsed(string $type, array $headers, array $rows): array
    {
        $rowErrors = [];
        $fieldErrors = [];
        $fieldWarnings = [];
        $batchErrors = $this->validateColumns($type, $headers);
        $dropdownWarnings = [];
        $missingUnitListingCodes = [];
        $duplicateRows = $this->duplicateIgnoredRows($type, $rows);
        $existingCodes = Listing::query()
            ->whereIn('code', collect($rows)->pluck('code')->filter()->unique()->all())
            ->pluck('code')
            ->all();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $errors = [];

            foreach ($this->requiredFields($type) as $field) {
                if (trim((string) ($row[$field] ?? '')) === '') {
                    $this->addFieldError(
                        $errors,
                        $fieldErrors,
                        $rowNumber,
                        $field,
                        ucfirst(str_replace('_', ' ', $field)).' is required.',
                    );
                }
            }

            if ($type === 'header') {
                $this->validateHeaderLocation($row, $rowNumber, $errors, $fieldErrors);
            }

            if (in_array($type, ['units', 'fees'], true)) {
                $code = trim((string) ($row['code'] ?? ''));
                if ($code !== '' && ! in_array($code, $existingCodes, true)) {
                    if ($type === 'units') {
                        $this->addFieldWarning(
                            $fieldWarnings,
                            $rowNumber,
                            'code',
                            "Listing code \"{$code}\" was not found; this unit row will be ignored.",
                        );
                        $missingUnitListingCodes[$code] = $code;

                        continue;
                    }

                    $this->addFieldError(
                        $errors,
                        $fieldErrors,
                        $rowNumber,
                        'code',
                        "Listing code \"{$code}\" was not found.",
                    );
                }
            }

            foreach ($this->lookupFields($type) as $field => $group) {
                $value = trim((string) ($row[$field] ?? ''));

                if ($value === '') {
                    continue;
                }

                if (! $this->registry->hasValue($group, $value)) {
                    $this->addFieldWarning(
                        $fieldWarnings,
                        $rowNumber,
                        $field,
                        "Unknown {$field} code \"{$value}\" will be imported as blank.",
                    );
                    $dropdownWarnings[$group][$field][$value] = $value;
                }
            }

            foreach ($this->numericFields($type) as $field) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '' && ! is_numeric($value)) {
                    $this->addFieldWarning(
                        $fieldWarnings,
                        $rowNumber,
                        $field,
                        ucfirst(str_replace('_', ' ', $field)).' could not be converted to a number and will be imported as blank.',
                    );
                }
            }

            foreach ($this->integerFields($type) as $field) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addFieldWarning(
                        $fieldWarnings,
                        $rowNumber,
                        $field,
                        ucfirst(str_replace('_', ' ', $field)).' could not be converted to an integer and will be imported as blank.',
                    );
                }
            }

            foreach ($this->booleanFields($type) as $field) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '' && $this->nullableBool($value) === null) {
                    $this->addFieldWarning(
                        $fieldWarnings,
                        $rowNumber,
                        $field,
                        ucfirst(str_replace('_', ' ', $field)).' could not be converted to a boolean and will be imported as blank.',
                    );
                }
            }

            foreach ($this->stringMaxFields($type) as $field => $max) {
                $value = trim((string) ($row[$field] ?? ''));
                if ($value !== '' && mb_strlen($value) > $max) {
                    $this->addFieldError(
                        $errors,
                        $fieldErrors,
                        $rowNumber,
                        $field,
                        ucfirst(str_replace('_', ' ', $field))." must not exceed {$max} characters.",
                    );
                }
            }

            if (isset($duplicateRows[$rowNumber])) {
                $duplicate = $duplicateRows[$rowNumber];
                $this->addFieldWarning(
                    $fieldWarnings,
                    $rowNumber,
                    $duplicate['field'],
                    $duplicate['message'],
                );
            }

            if ($errors !== []) {
                $rowErrors[$rowNumber] = $errors;
            }
        }

        $warnings = [];
        foreach ($duplicateRows as $rowNumber => $duplicate) {
            $warnings[] = $duplicate['message'];
        }

        foreach ($missingUnitListingCodes as $code) {
            $warnings[] = "Listing code {$code} was not found; matching unit rows will be ignored.";
        }

        foreach ($dropdownWarnings as $group => $fields) {
            foreach ($fields as $field => $values) {
                $warnings[] = sprintf(
                    'Unknown %s codes for %s will be imported as blank: %s.',
                    ListingLookupGroups::label($group),
                    $field,
                    implode(', ', array_values($values)),
                );
            }
        }

        return [
            'row_errors' => $rowErrors,
            'field_errors' => $fieldErrors,
            'batch_errors' => $batchErrors,
            'warnings' => $warnings,
            'field_warnings' => $fieldWarnings,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $errors
     * @param  array<int, array<string, array<int, string>>>  $fieldErrors
     */
    protected function validateHeaderLocation(array $row, int $rowNumber, array &$errors, array &$fieldErrors): void
    {
        $provinceName = trim((string) ($row['province'] ?? ''));
        $cityName = trim((string) ($row['city'] ?? ''));

        if ($provinceName === '' || $cityName === '') {
            return;
        }

        $province = Province::query()
            ->active()
            ->where('name', $provinceName)
            ->first();

        if ($province === null) {
            $this->addFieldError(
                $errors,
                $fieldErrors,
                $rowNumber,
                'province',
                "Province \"{$provinceName}\" was not found in the province master file.",
            );

            return;
        }

        $cityExists = City::query()
            ->active()
            ->where('province_id', $province->id)
            ->where('name', $cityName)
            ->exists();

        if (! $cityExists) {
            $this->addFieldError(
                $errors,
                $fieldErrors,
                $rowNumber,
                'city',
                "City \"{$cityName}\" was not found under province \"{$provinceName}\" in the city master file.",
            );
        }
    }

    /**
     * @param  array<int, string>  $errors
     * @param  array<int, array<string, array<int, string>>>  $fieldErrors
     */
    protected function addFieldError(array &$errors, array &$fieldErrors, int $rowNumber, string $field, string $message): void
    {
        $errors[] = $message;
        $fieldErrors[$rowNumber][$field][] = $message;
    }

    /**
     * @param  array<int, array<string, array<int, string>>>  $fieldWarnings
     */
    protected function addFieldWarning(array &$fieldWarnings, int $rowNumber, string $field, string $message): void
    {
        $fieldWarnings[$rowNumber][$field][] = $message;
    }

    /**
     * @return array<int, string>
     */
    protected function validateColumns(string $type, array $headers): array
    {
        $expected = $this->headers($type);

        if ($headers === $expected) {
            return [];
        }

        $missing = array_values(array_diff($expected, $headers));
        $unexpected = array_values(array_diff($headers, $expected));
        $errors = ['CSV columns must exactly match the '.$type.' template.'];

        if ($missing !== []) {
            $errors[] = 'Missing columns: '.implode(', ', $missing).'.';
        }

        if ($unexpected !== []) {
            $errors[] = 'Unexpected columns: '.implode(', ', $unexpected).'.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    protected function lookupFields(string $type): array
    {
        return match ($type) {
            'header' => [
                'completion_status' => ListingLookupGroups::COMPLETION_STATUS,
                'grade' => ListingLookupGroups::GRADE,
                'peza_accreditation' => ListingLookupGroups::PEZA_ACCREDITATION,
            ],
            'units' => [
                'handover_condition' => ListingLookupGroups::HANDOVER_CONDITION,
                'availability' => ListingLookupGroups::AVAILABILITY,
                'bedrooms' => ListingLookupGroups::BEDROOMS,
                'property_type' => ListingLookupGroups::PROPERTY_TYPE,
            ],
            'fees' => [
                'fee_type' => ListingLookupGroups::FEE_TYPE,
            ],
        };
    }

    /**
     * @return array<int, string>
     */
    protected function requiredFields(string $type): array
    {
        return match ($type) {
            'header' => ['code', 'name'],
            'units' => ['code', 'floor', 'unit'],
            'fees' => ['code'],
        };
    }

    /**
     * @return array<int, string>
     */
    protected function numericFields(string $type): array
    {
        return match ($type) {
            'header' => [
                'office_rental_rate',
                'total_area_size',
                'unit_market_size',
                'retail_market_rate',
                'gross_leasable_area',
                'typical_floor_area',
                'typical_retail_floor_area',
            ],
            'units' => ['area_size', 'rental', 'selling_price'],
            'fees' => ['fee'],
        };
    }

    /**
     * @return array<int, string>
     */
    protected function integerFields(string $type): array
    {
        return match ($type) {
            'header' => ['completion_year'],
            'units', 'fees' => ['sort_order'],
        };
    }

    /**
     * @return array<int, string>
     */
    protected function booleanFields(string $type): array
    {
        return match ($type) {
            'header' => ['published_to_public', 'other_info_visible'],
            'units' => ['for_lease', 'for_sale'],
            'fees' => [],
        };
    }

    /**
     * @return array<string, int>
     */
    protected function stringMaxFields(string $type): array
    {
        return match ($type) {
            'header' => ['density_ratio' => 50],
            'units', 'fees' => [],
        };
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function listingPayloadFromRow(array $row): array
    {
        $city = $this->nullableString($row['city'] ?? null);
        $name = trim((string) $row['name']);
        $code = trim((string) $row['code']);
        $slugInput = $this->nullableString($row['slug'] ?? null);

        if ($slugInput === null || $slugInput === '') {
            $helper = app(ListingSlugHelper::class);
            $base = $helper->generateFromName($name, $code);
            $slugInput = $helper->ensureUnique($base, $city);
        }

        return [
            'code' => $code,
            'name' => $name,
            'summary' => $this->nullableString($row['summary'] ?? null),
            'description' => $this->nullableString($row['description'] ?? null),
            'slug' => $slugInput,
            'province' => $this->nullableString($row['province'] ?? null),
            'city' => $city,
            'brgy' => $this->nullableString($row['brgy'] ?? null),
            'address' => $this->nullableString($row['address'] ?? null),
            'office_rental_rate' => $this->nullableDecimal($row['office_rental_rate'] ?? null),
            'total_area_size' => $this->nullableDecimal($row['total_area_size'] ?? null),
            'unit_market_size' => $this->nullableDecimal($row['unit_market_size'] ?? null),
            'retail_market_rate' => $this->nullableDecimal($row['retail_market_rate'] ?? null),
            'completion_status' => $this->nullableLookupString($row['completion_status'] ?? null, ListingLookupGroups::COMPLETION_STATUS),
            'published_to_public' => $this->nullableBool($row['published_to_public'] ?? null),
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
            'grade' => $this->nullableLookupString($row['grade'] ?? null, ListingLookupGroups::GRADE),
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
            'floor_efficiency' => $this->nullableString($row['floor_efficiency'] ?? null),
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
            'no_of_lifts_passenger' => $this->nullableString($row['no_of_lifts_passenger'] ?? null),
            'no_of_lifts_service' => $this->nullableString($row['no_of_lifts_service'] ?? null),
            'telco' => $this->nullableString($row['telco'] ?? null),
            'backup_power' => $this->nullableString($row['backup_power'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function otherInfoPayloadFromRow(array $row): array
    {
        return [
            'peza_accreditation' => $this->nullableLookupString($row['peza_accreditation'] ?? null, ListingLookupGroups::PEZA_ACCREDITATION),
            'sustainability' => $this->nullableString($row['sustainability'] ?? null),
            'other_info_visible' => $this->nullableBool($row['other_info_visible'] ?? null),
        ];
    }

    protected function commitHeaderRow(array $row): string
    {
        $listing = Listing::query()->where('code', trim((string) $row['code']))->first();
        $payload = $this->listingPayloadFromRow($row);
        $payload['spec'] = $this->specPayloadFromRow($row);
        $payload['building_service'] = $this->buildingPayloadFromRow($row);
        $payload['other_info'] = $this->otherInfoPayloadFromRow($row);

        if ($listing === null) {
            $this->persistence->create($payload);

            return 'created';
        }

        $this->persistence->update($listing, $payload);

        return 'updated';
    }

    protected function commitUnitRow(array $row): string
    {
        $listing = Listing::query()->where('code', trim((string) $row['code']))->firstOrFail();
        $unit = ListingUnit::query()
            ->where('listing_id', $listing->id)
            ->where('floor', trim((string) $row['floor']))
            ->where('unit', trim((string) $row['unit']))
            ->first();

        $payload = [
            'floor' => trim((string) $row['floor']),
            'unit' => trim((string) $row['unit']),
            'area_size' => $this->nullableDecimal($row['area_size'] ?? null),
            'rental' => $this->nullableDecimal($row['rental'] ?? null),
            'handover_condition' => $this->nullableLookupString($row['handover_condition'] ?? null, ListingLookupGroups::HANDOVER_CONDITION),
            'availability' => $this->nullableLookupString($row['availability'] ?? null, ListingLookupGroups::AVAILABILITY),
            'bedrooms' => $this->nullableLookupString($row['bedrooms'] ?? null, ListingLookupGroups::BEDROOMS),
            'selling_price' => $this->nullableDecimal($row['selling_price'] ?? null),
            'property_type' => $this->nullableLookupString($row['property_type'] ?? null, ListingLookupGroups::PROPERTY_TYPE),
            'for_lease' => $this->nullableBool($row['for_lease'] ?? null),
            'for_sale' => $this->nullableBool($row['for_sale'] ?? null),
            'last_remarks' => $this->nullableString($row['last_remarks'] ?? null),
            'sort_order' => $this->nullableInt($row['sort_order'] ?? null),
        ];

        if ($unit === null) {
            $listing->units()->create($payload);

            return 'created';
        }

        $unit->update($payload);

        return 'updated';
    }

    protected function commitFeeRow(array $row): string
    {
        $listing = Listing::query()->where('code', trim((string) $row['code']))->firstOrFail();
        $feeType = $this->nullableLookupString($row['fee_type'] ?? null, ListingLookupGroups::FEE_TYPE);
        $feeQuery = ListingFee::query()->where('listing_id', $listing->id);
        $feeType === null ? $feeQuery->whereNull('fee_type') : $feeQuery->where('fee_type', $feeType);
        $fee = $feeQuery->first();

        $payload = [
            'fee_type' => $feeType,
            'fee' => $this->nullableDecimal($row['fee'] ?? null),
            'sort_order' => $this->nullableInt($row['sort_order'] ?? null),
        ];

        if ($fee === null) {
            $listing->fees()->create($payload);

            return 'created';
        }

        $fee->update($payload);

        return 'updated';
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

    protected function nullableLookupString(mixed $value, string $group): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        return $this->registry->hasValue($group, $value) ? $value : null;
    }

    protected function nullableDecimal(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '' || filter_var($value, FILTER_VALIDATE_INT) === false) {
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

        $value = strtolower($value);

        if (in_array($value, ['1', 'true', 'yes', 'y'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'no', 'n'], true)) {
            return false;
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{field: string, code: string, winner: int, message: string}>
     */
    protected function duplicateIgnoredRows(string $type, array $rows): array
    {
        $lastRowNumbers = [];

        foreach ($rows as $index => $row) {
            $key = $this->rowKey($type, $row);
            if ($key !== '') {
                $lastRowNumbers[$key] = $index + 2;
            }
        }

        $duplicates = [];
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $key = $this->rowKey($type, $row);

            if ($key === '' || ($lastRowNumbers[$key] ?? $rowNumber) === $rowNumber) {
                continue;
            }

            $duplicates[$rowNumber] = [
                'field' => $this->duplicateWarningField($type),
                'code' => trim((string) ($row['code'] ?? '')),
                'winner' => $lastRowNumbers[$key],
                'message' => $this->duplicateWarningMessage($type, $row, $rowNumber, $lastRowNumbers[$key]),
            ];
        }

        return $duplicates;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function lastRowsByKey(string $type, array $rows): array
    {
        $seen = [];
        $lastRows = [];

        for ($index = count($rows) - 1; $index >= 0; $index--) {
            $row = $rows[$index];
            $key = $this->rowKey($type, $row);

            if ($key !== '' && isset($seen[$key])) {
                continue;
            }

            if ($key !== '') {
                $seen[$key] = true;
            }

            $lastRows[] = $row;
        }

        return array_reverse($lastRows);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function importableRows(string $type, array $rows): array
    {
        $rows = $this->lastRowsByKey($type, $rows);

        if ($type !== 'units') {
            return $rows;
        }

        $existingCodes = Listing::query()
            ->whereIn('code', collect($rows)->pluck('code')->filter()->unique()->all())
            ->pluck('code')
            ->all();

        return array_values(array_filter(
            $rows,
            fn (array $row) => in_array(trim((string) ($row['code'] ?? '')), $existingCodes, true),
        ));
    }

    protected function duplicateWarningField(string $type): string
    {
        return match ($type) {
            'header' => 'code',
            'fees' => 'fee_type',
            'units' => 'unit',
        };
    }

    protected function duplicateWarningMessage(string $type, array $row, int $rowNumber, int $winnerRowNumber): string
    {
        $code = trim((string) ($row['code'] ?? ''));

        if ($type === 'fees') {
            $feeType = trim((string) ($row['fee_type'] ?? ''));
            $label = $feeType === '' ? '(blank)' : $feeType;

            return "Duplicate fee upload key {$code} + {$label} found on row {$rowNumber}; row {$rowNumber} will be ignored and row {$winnerRowNumber} will be used.";
        }

        return "Duplicate property code {$code} found on row {$rowNumber}; row {$rowNumber} will be ignored and row {$winnerRowNumber} will be used.";
    }

    protected function rowKey(string $type, array $row): string
    {
        return match ($type) {
            'header' => trim((string) ($row['code'] ?? '')),
            'units' => implode('|', [
                trim((string) ($row['code'] ?? '')),
                trim((string) ($row['floor'] ?? '')),
                trim((string) ($row['unit'] ?? '')),
            ]),
            'fees' => implode('|', [
                trim((string) ($row['code'] ?? '')),
                trim((string) ($row['fee_type'] ?? '')),
            ]),
        };
    }

    protected function feeTypeKey(mixed $value): string
    {
        return $this->nullableLookupString($value, ListingLookupGroups::FEE_TYPE) ?? '__NULL__';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, string>
     */
    protected function existingUnitKeys(array $rows): array
    {
        $codes = collect($rows)->pluck('code')->filter()->unique()->all();

        return ListingUnit::query()
            ->join('listings', 'listings.id', '=', 'listing_units.listing_id')
            ->whereIn('listings.code', $codes)
            ->get(['listings.code', 'listing_units.floor', 'listing_units.unit'])
            ->map(fn ($unit) => implode('|', [
                (string) $unit->code,
                (string) $unit->floor,
                (string) $unit->unit,
            ]))
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, string>
     */
    protected function existingFeeKeys(array $rows): array
    {
        $codes = collect($rows)->pluck('code')->filter()->unique()->all();

        return ListingFee::query()
            ->join('listings', 'listings.id', '=', 'listing_fees.listing_id')
            ->whereIn('listings.code', $codes)
            ->get(['listings.code', 'listing_fees.fee_type'])
            ->map(fn ($fee) => implode('|', [
                (string) $fee->code,
                $fee->fee_type === null ? '__NULL__' : (string) $fee->fee_type,
            ]))
            ->all();
    }

    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        if ($type === 'properties' || $type === 'listings') {
            return 'header';
        }

        if (! in_array($type, ['header', 'units', 'fees'], true)) {
            throw new InvalidArgumentException("Unsupported CSV type [{$type}].");
        }

        return $type;
    }

    protected function ensureImportMemoryLimit(): void
    {
        $current = $this->phpSizeToBytes(ini_get('memory_limit') ?: '');
        $required = $this->phpSizeToBytes(self::IMPORT_MEMORY_LIMIT);

        if ($current === null || $required === null || $current < 0 || $current >= $required) {
            return;
        }

        ini_set('memory_limit', self::IMPORT_MEMORY_LIMIT);
    }

    protected function phpSizeToBytes(string $value): ?int
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
