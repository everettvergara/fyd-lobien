<?php

namespace App\Modules\Address\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Address\Models\City;
use App\Modules\Address\Models\Province;
use App\Modules\Address\Requests\StoreCityRequest;
use App\Modules\Address\Requests\UpdateCityRequest;
use App\Modules\Address\Services\CityAdminListService;
use App\Services\ActivityLogger;
use App\Services\Media\MediaUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(
        protected CityAdminListService $cityList,
        protected MediaUsageService $usage,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', City::class);

        return view('address::cities.index', [
            'list' => $this->cityList->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', City::class);

        $provinces = Province::query()->orderBy('name')->get();

        return view('address::cities.create', compact('provinces'));
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $city = City::create($request->validated());
        $this->syncMediaUsage($city);

        ActivityLogger::log('cities', 'created', $city);

        return redirect()->route('admin.cities.index')
            ->with('success', 'City created successfully.');
    }

    public function show(City $city): View
    {
        $this->authorize('view', $city);

        $city->load(['province', 'image']);

        return view('address::cities.show', compact('city'));
    }

    public function edit(City $city): View
    {
        $this->authorize('update', $city);

        $provinces = Province::query()->orderBy('name')->get();
        $city->load('image');

        return view('address::cities.edit', compact('city', 'provinces'));
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $city->update($request->validated());
        $this->syncMediaUsage($city->refresh());

        ActivityLogger::log('cities', 'updated', $city);

        return redirect()->route('admin.cities.index')
            ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->authorize('delete', $city);

        if (User::where('city_id', $city->id)->exists()) {
            return back()->with('error', 'Cannot delete a city that is assigned to users.');
        }

        ActivityLogger::log('cities', 'deleted', $city);
        $this->usage->removeModel($city);
        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', 'City deleted successfully.');
    }

    public function byProvince(Province $province): JsonResponse
    {
        $cities = City::query()
            ->where('province_id', $province->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['cities' => $cities]);
    }

    protected function syncMediaUsage(City $city): void
    {
        $this->usage->syncModel($city, 'address', [
            'image_id' => 'City Image',
        ]);
    }
}
