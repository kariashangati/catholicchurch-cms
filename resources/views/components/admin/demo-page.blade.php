@extends('layouts.admin')

@section('title', 'Admin UI v3 Demo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v3.css') }}">
@endpush

@section('content')
    <div class="admin-ui-v3">
        <x-admin.page-hero
            badge="Unified UI"
            title="ChurchCMS Admin UI v3"
            subtitle="Shared hero, stat cards, filter cards, panels and tables that match the dashboard and kanda visual language."
            :meta="[
                ['icon' => 'fas fa-palette', 'label' => 'Dashboard + Kanda inspired'],
                ['icon' => 'fas fa-layer-group', 'label' => 'Reusable blade components'],
                ['icon' => 'fas fa-wand-magic-sparkles', 'label' => 'Ready for module rollout', 'tone' => 'warning'],
            ]"
            :actions="[
                ['href' => '#', 'icon' => 'fas fa-users', 'label' => 'Members'],
                ['href' => '#', 'icon' => 'fas fa-sitemap', 'label' => 'Jumuiya'],
                ['href' => '#', 'icon' => 'fas fa-chart-line', 'label' => 'Reports'],
            ]"
        />

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <x-admin.stat-card title="Active Members" value="1,245" meta="Across all visible communities" icon="fas fa-users" tone="primary" chip="Overview" />
            </div>
            <div class="col-xl-3 col-md-6">
                <x-admin.stat-card title="Families" value="332" meta="Registered in the current year" icon="fas fa-home" tone="success" chip="Structure" />
            </div>
            <div class="col-xl-3 col-md-6">
                <x-admin.stat-card title="Offerings" value="TZS 18.2M" meta="Current month collection" icon="fas fa-hand-holding-heart" tone="info" chip="Finance" />
            </div>
            <div class="col-xl-3 col-md-6">
                <x-admin.stat-card title="Pending Reviews" value="14" meta="Records need follow up" icon="fas fa-bell" tone="warning" chip="Attention" />
            </div>
        </div>

        <x-admin.filter-card
            class="mb-4"
            title="Filter records"
            subtitle="Use consistent filter styling for all index pages."
            action="#"
            reset-url="#"
            submit-label="Apply"
        >
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Year</label>
                <select class="form-select">
                    <option>2026</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Month</label>
                <select class="form-select">
                    <option>March</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" placeholder="Search records">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select">
                    <option>All</option>
                </select>
            </div>
        </x-admin.filter-card>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <x-admin.panel title="Performance panel" subtitle="Use this for charts, analytics and summaries." icon="fas fa-chart-line">
                    <div class="ui-chart-shell ui-chart-shell-lg d-flex align-items-center justify-content-center">
                        <div class="text-center text-muted">Chart area</div>
                    </div>
                </x-admin.panel>
            </div>
            <div class="col-xl-4">
                <x-admin.quick-links
                    title="Quick actions"
                    subtitle="Shared shortcut cards"
                    badge="Shortcuts"
                    :links="[
                        ['href' => '#', 'icon' => 'fas fa-user-plus', 'label' => 'Add member'],
                        ['href' => '#', 'icon' => 'fas fa-money-bill-wave', 'label' => 'Record contribution'],
                        ['href' => '#', 'icon' => 'fas fa-file-lines', 'label' => 'Open report'],
                    ]"
                >
                    <x-admin.note-box class="mt-4" title="Implementation note" text="Use these components across dashboard, reports, finance, members, jumuiya and familia pages for a single visual system." />
                </x-admin.quick-links>
            </div>
        </div>

        <x-admin.table-card
            title="Recent entries"
            subtitle="Consistent table wrapper with responsive layout."
            icon="fas fa-table"
            class="mb-4"
        >
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>St. Joseph Jumuiya</td>
                    <td>Offering</td>
                    <td>TZS 240,000</td>
                    <td>Today</td>
                </tr>
                <tr>
                    <td>Immaculate Familia</td>
                    <td>Tithe</td>
                    <td>TZS 98,000</td>
                    <td>Today</td>
                </tr>
            </tbody>
        </x-admin.table-card>
    </div>
@endsection
