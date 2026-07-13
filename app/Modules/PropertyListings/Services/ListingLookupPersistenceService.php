<?php

namespace App\Modules\PropertyListings\Services;

use App\Modules\PropertyListings\Models\Listing;
use App\Modules\PropertyListings\Models\ListingAsset;
use App\Modules\PropertyListings\Models\ListingFee;
use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Models\ListingOtherInfo;
use App\Modules\PropertyListings\Models\ListingSpec;
use App\Modules\PropertyListings\Models\ListingUnit;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use App\Modules\PropertyListings\Support\ListingLookupRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ListingLookupPersistenceService
{
    public function __construct(
        protected ListingLookupRegistry $registry,
    ) {}

    public function create(string $group, array $data): ListingLookup
    {
        $lookup = DB::transaction(function () use ($group, $data) {
            return ListingLookup::create([
                'group' => $group,
                'value' => (string) $data['value'],
                'label' => (string) $data['label'],
                'summary' => $this->profileValue($group, $data, 'summary'),
                'description' => $this->profileValue($group, $data, 'description'),
                'image_id' => $this->profileValue($group, $data, 'image_id'),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'meta' => $this->normalizeMeta($group, $data['meta'] ?? null),
            ]);
        });

        $this->registry->forgetCache();

        return $lookup;
    }

    public function update(ListingLookup $lookup, array $data): ListingLookup
    {
        DB::transaction(function () use ($lookup, $data) {
            $payload = [
                'label' => (string) ($data['label'] ?? $lookup->label),
                'sort_order' => (int) ($data['sort_order'] ?? $lookup->sort_order),
                'is_active' => (bool) ($data['is_active'] ?? $lookup->is_active),
                'meta' => array_key_exists('meta', $data)
                    ? $this->normalizeMeta($lookup->group, $data['meta'])
                    : $lookup->meta,
            ];

            if (ListingLookupGroups::usesPropertyTypeProfile($lookup->group)) {
                $payload['summary'] = $this->nullableString($data['summary'] ?? null);
                $payload['description'] = $this->nullableString($data['description'] ?? null);
                $payload['image_id'] = filled($data['image_id'] ?? null) ? (int) $data['image_id'] : null;
            }

            $lookup->update($payload);
        });

        $this->registry->forgetCache();

        return $lookup->refresh();
    }

    public function delete(ListingLookup $lookup): void
    {
        $usageCount = $this->usageCount($lookup);

        if ($usageCount > 0) {
            throw ValidationException::withMessages([
                'value' => "Cannot delete this value because it is used on {$usageCount} record(s). Deactivate it instead.",
            ]);
        }

        DB::transaction(fn () => $lookup->delete());

        $this->registry->forgetCache();
    }

    public function usageCount(ListingLookup $lookup): int
    {
        return $this->usageCountFor($lookup->group, $lookup->value);
    }

    public function usageCountFor(string $group, string $value): int
    {
        return match ($group) {
            ListingLookupGroups::IMAGE_TYPE => ListingAsset::query()->where('asset_type', $value)->count(),
            ListingLookupGroups::PROPERTY_TYPE => ListingUnit::query()->where('property_type', $value)->count(),
            ListingLookupGroups::COMPLETION_STATUS => Listing::query()->where('completion_status', $value)->count(),
            ListingLookupGroups::HANDOVER_CONDITION => ListingUnit::query()->where('handover_condition', $value)->count(),
            ListingLookupGroups::AVAILABILITY => ListingUnit::query()->where('availability', $value)->count(),
            ListingLookupGroups::BEDROOMS => ListingUnit::query()->where('bedrooms', $value)->count(),
            ListingLookupGroups::GRADE => ListingSpec::query()->where('grade', $value)->count(),
            ListingLookupGroups::FEE_TYPE => ListingFee::query()->where('fee_type', $value)->count(),
            ListingLookupGroups::PEZA_ACCREDITATION => ListingOtherInfo::query()->where('peza_accreditation', $value)->count(),
            ListingLookupGroups::PROPERTY_USE => 0,
            default => 0,
        };
    }

    public function canDelete(ListingLookup $lookup): bool
    {
        return $this->usageCount($lookup) === 0;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>|null
     */
    protected function normalizeMeta(string $group, ?array $meta): ?array
    {
        if (! ListingLookupGroups::usesFileKind($group)) {
            return $meta ?: null;
        }

        $fileKind = Arr::get($meta, 'file_kind', 'image');

        return [
            'file_kind' => in_array($fileKind, ['image', 'pdf'], true) ? $fileKind : 'image',
        ];
    }

    protected function profileValue(string $group, array $data, string $key): mixed
    {
        if (! ListingLookupGroups::usesPropertyTypeProfile($group)) {
            return null;
        }

        if ($key === 'image_id') {
            return filled($data['image_id'] ?? null) ? (int) $data['image_id'] : null;
        }

        return $this->nullableString($data[$key] ?? null);
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
