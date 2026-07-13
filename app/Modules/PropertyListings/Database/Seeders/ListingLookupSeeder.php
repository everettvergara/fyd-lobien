<?php

namespace App\Modules\PropertyListings\Database\Seeders;

use App\Modules\PropertyListings\Models\ListingLookup;
use App\Modules\PropertyListings\Support\ListingLookupGroups;
use Illuminate\Database\Seeder;

class ListingLookupSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLookups();
    }

    protected function seedLookups(): void
    {
        $rows = [
            ...$this->rows(ListingLookupGroups::IMAGE_TYPE, [
                ['building', 'Building', ['file_kind' => 'image']],
                ['floor-plan', 'Floor Plan', ['file_kind' => 'image']],
                ['map', 'Map', ['file_kind' => 'image']],
                ['interior', 'Interior', ['file_kind' => 'image']],
                ['flyers', 'flyers', ['file_kind' => 'pdf']],
            ]),
            ...$this->rows(ListingLookupGroups::PROPERTY_TYPE, [
                ['commercial-office', 'Commercial - Office Use (Commercial)'],
                ['commercial-retail', 'Commercial - Retail Use (Commercial)'],
                ['commercial-others', 'Commercial - Others (Commercial)'],
                ['residential-condo', 'Residential - Condo (Residential)'],
                ['residential-house-lot', 'Residential - House and Lot (Residential)'],
                ['residential-others', 'Residential - Others (Residential)'],
                ['industrial-warehouse', 'Industrial - Warehouse (Industrial)'],
                ['industrial-others', 'Industrial - Others (Industrial)'],
                ['lot', 'Lot (Lot)'],
                ['others', 'Others (Others)'],
            ]),
            ...$this->rows(ListingLookupGroups::COMPLETION_STATUS, [
                ['existing', 'Existing'],
                ['pipeline', 'Pipeline'],
            ]),
            ...$this->rows(ListingLookupGroups::PROPERTY_USE, [
                ['commercial', 'Commercial'],
                ['residential', 'Residential'],
                ['industrial', 'Industrial'],
                ['others', 'Others'],
            ]),
            ...$this->rows(ListingLookupGroups::HANDOVER_CONDITION, [
                ['bare-shell', 'Bare Shell'],
                ['warm-shell', 'Warm Shell'],
                ['partially-fitted', 'Partially Fitted'],
                ['fully-fitted', 'Fully Fitted'],
                ['as-is-where-is', 'As-is-where-is'],
            ]),
            ...$this->rows(ListingLookupGroups::AVAILABILITY, [
                ['vacant', 'Vacant'],
                ['leased', 'Leased'],
            ]),
            ...$this->rows(ListingLookupGroups::BEDROOMS, [
                ['studio', 'Studio'],
                ['1br', '1BR'],
                ['2br', '2BR'],
                ['3br', '3BR'],
                ['others', 'Others'],
            ]),
            ...$this->rows(ListingLookupGroups::GRADE, [
                ['a-plus', 'A+ (Prime)'],
                ['a', 'A'],
                ['b', 'B'],
                ['c', 'C'],
            ]),
            ...$this->rows(ListingLookupGroups::FEE_TYPE, [
                ['rental-rate', 'Rental Rate'],
                ['dues-cusa', 'Dues/CUSA'],
                ['parking-fee', 'Parking Fee'],
            ]),
            ...$this->rows(ListingLookupGroups::PEZA_ACCREDITATION, [
                ['yes', 'Yes'],
                ['no', 'No'],
                ['processing', 'Processing'],
            ]),
        ];

        foreach ($rows as $index => $row) {
            ListingLookup::updateOrCreate(
                ['group' => $row['group'], 'value' => $row['value']],
                [
                    'label' => $row['label'],
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                    'meta' => $row['meta'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2?: array<string, mixed>}>  $items
     * @return array<int, array{group: string, value: string, label: string, meta?: array<string, mixed>}>
     */
    protected function rows(string $group, array $items): array
    {
        return collect($items)->map(function (array $item) use ($group) {
            return [
                'group' => $group,
                'value' => $item[0],
                'label' => $item[1],
                'meta' => $item[2] ?? null,
            ];
        })->all();
    }
}
