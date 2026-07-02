@extends('admin.layouts.app')

@section('title', 'Edit User')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => $user->name],
        ['label' => 'Edit'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Edit User</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-4 admin-form-columns">
                    <div class="col-12 admin-form-main">
                        @include('users::users._form', ['user' => $user])
                    </div>
                    <div class="col-12 admin-form-sidebar">
                        @include('users::users._profile_fields', ['user' => $user])
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const provinceSelect = document.getElementById('province_id');
    const citySelect = document.getElementById('city_id');

    if (!provinceSelect || !citySelect) {
        return;
    }

    const selectedCityId = @json(old('city_id', $user->city_id));

    async function loadCities(provinceId, preserveCityId = null) {
        citySelect.innerHTML = '<option value="">Select city...</option>';

        if (!provinceId) {
            return;
        }

        citySelect.disabled = true;

        try {
            const response = await fetch(`{{ url('/admin/cities/by-province') }}/${provinceId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            data.cities.forEach((city) => {
                const option = document.createElement('option');
                option.value = city.id;
                option.textContent = city.name;
                if (preserveCityId && Number(preserveCityId) === Number(city.id)) {
                    option.selected = true;
                }
                citySelect.appendChild(option);
            });
        } finally {
            citySelect.disabled = false;
        }
    }

    provinceSelect.addEventListener('change', () => {
        loadCities(provinceSelect.value);
    });

    if (provinceSelect.value && citySelect.options.length <= 1) {
        loadCities(provinceSelect.value, selectedCityId);
    }
});
</script>
@endpush
