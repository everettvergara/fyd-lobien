@extends('admin.layouts.app')

@section('title', 'Dashboard')

@php
    $breadcrumbs = [
        ['label' => 'Dashboard'],
    ];
@endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Dashboard</h1>
    </div>
@endsection
