<?php

namespace App\Modules\Address\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Address\Models\Province;
use App\Modules\Address\Requests\StoreProvinceRequest;
use App\Modules\Address\Requests\UpdateProvinceRequest;
use App\Modules\Address\Services\ProvinceAdminListService;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProvinceController extends Controller
{
    public function __construct(
        protected ProvinceAdminListService $provinceList,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Province::class);

        return view('address::provinces.index', [
            'list' => $this->provinceList->result($request),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Province::class);

        return view('address::provinces.create');
    }

    public function store(StoreProvinceRequest $request): RedirectResponse
    {
        $province = Province::create($request->validated());

        ActivityLogger::log('provinces', 'created', $province);

        return redirect()->route('admin.provinces.index')
            ->with('success', 'Province created successfully.');
    }

    public function show(Province $province): View
    {
        $this->authorize('view', $province);

        $province->loadCount('cities');

        return view('address::provinces.show', compact('province'));
    }

    public function edit(Province $province): View
    {
        $this->authorize('update', $province);

        return view('address::provinces.edit', compact('province'));
    }

    public function update(UpdateProvinceRequest $request, Province $province): RedirectResponse
    {
        $province->update($request->validated());

        ActivityLogger::log('provinces', 'updated', $province);

        return redirect()->route('admin.provinces.index')
            ->with('success', 'Province updated successfully.');
    }

    public function destroy(Province $province): RedirectResponse
    {
        $this->authorize('delete', $province);

        if ($province->cities()->exists()) {
            return back()->with('error', 'Cannot delete a province that has cities.');
        }

        if (User::where('province_id', $province->id)->exists()) {
            return back()->with('error', 'Cannot delete a province that is assigned to users.');
        }

        ActivityLogger::log('provinces', 'deleted', $province);
        $province->delete();

        return redirect()->route('admin.provinces.index')
            ->with('success', 'Province deleted successfully.');
    }
}
