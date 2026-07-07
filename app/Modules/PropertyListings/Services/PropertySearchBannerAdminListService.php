<?php

namespace App\Modules\PropertyListings\Services;

use App\Framework\Admin\List\AdminListAction;
use App\Framework\Admin\List\AdminListColumn;
use App\Framework\Admin\List\AdminListDefinition;
use App\Framework\Admin\List\AdminListResult;
use App\Framework\Admin\List\AdminListService;
use App\Modules\PropertyListings\Models\PropertySearchBanner;
use Illuminate\Http\Request;

class PropertySearchBannerAdminListService
{
    public function __construct(
        protected AdminListService $lists,
    ) {}

    public function result(Request $request): AdminListResult
    {
        $query = PropertySearchBanner::query()->with('backgroundImage');

        return $this->lists->build($query, $this->definition(), $request);
    }

    public function definition(): AdminListDefinition
    {
        return new AdminListDefinition(
            id: 'property-search-banners',
            title: 'Search Banners',
            modelClass: PropertySearchBanner::class,
            columns: [
                AdminListColumn::make('no', 'No', class: 'text-muted small', headerClass: 'text-muted'),
                AdminListColumn::make('name', 'Name', fn (PropertySearchBanner $banner) => sprintf(
                    '<a href="%s" class="text-decoration-none fw-medium">%s</a>',
                    route('admin.property-search-banners.edit', $banner),
                    e($banner->name),
                ), sortField: 'name', raw: true),
                AdminListColumn::make('key', 'Key', fn (PropertySearchBanner $banner) => e($banner->key), sortField: 'key', class: 'small text-muted font-monospace'),
                AdminListColumn::make('heading', 'Heading', fn (PropertySearchBanner $banner) => e($banner->heading ?: '—'), sortField: 'heading', class: 'small'),
                AdminListColumn::make('is_active', 'Active', fn (PropertySearchBanner $banner) => $banner->is_active
                    ? '<span class="badge bg-success-subtle text-success">Active</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>', sortField: 'is_active', raw: true),
            ],
            rowActions: [
                AdminListAction::make(
                    'edit',
                    'Edit',
                    'bi-pencil',
                    fn (PropertySearchBanner $banner) => route('admin.property-search-banners.edit', $banner),
                    ability: 'update',
                ),
                AdminListAction::make(
                    'delete',
                    'Delete',
                    'bi-trash',
                    fn (PropertySearchBanner $banner) => route('admin.property-search-banners.destroy', $banner),
                    method: 'DELETE',
                    ability: 'delete',
                    confirm: 'Delete this search banner?',
                    danger: true,
                ),
            ],
            searchQuery: fn ($query, string $search) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('heading', 'like', "%{$search}%");
            }),
            searchPlaceholder: 'Search name, key, heading...',
            defaultSort: 'name',
            defaultDirection: 'asc',
            defaultPerPage: 15,
            selectable: false,
        );
    }
}
