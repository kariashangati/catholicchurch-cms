@extends('layouts.admin')

@section('title', db_trans('age_groups'))
@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $ageGroups = collect($ageGroups ?? []);
    $genderScopes = $genderScopes ?? \App\Models\AgeGroup::genderScopes();
    $activeCount = $ageGroups->where('is_active', true)->count();
    $inactiveCount = $ageGroups->where('is_active', false)->count();
@endphp

<div class="admin-ui-v4 contributions-page-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-users"></i>
                    {{ db_trans('age_groups') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('age_groups') }}</h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-layer-group"></i>
                        {{ number_format($ageGroups->count()) }} {{ db_trans('records') }}
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-circle-check"></i>
                        {{ number_format($activeCount) }} {{ db_trans('active') }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-calendar-xmark"></i>
                        {{ number_format((int) ($membersWithoutDobCount ?? 0)) }} {{ db_trans('members_without_dob') }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    @can('membership.age-groups.create')
                        <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createAgeGroupModal">
                            <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('new_age_group') }}</span>
                        </button>
                    @endcan

                    <a href="{{ Route::has('members.index') ? route('members.index') : '#' }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-user-friends"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('members') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-tone-primary p-4 h-100">
                <div class="ui-stat-top">
                    <span class="ui-stat-icon"><i class="fas fa-layer-group"></i></span>
                    <span class="ui-chip">{{ db_trans('total') }}</span>
                </div>
                <div class="ui-stat-label">{{ db_trans('age_groups') }}</div>
                <div class="ui-stat-value">{{ number_format($ageGroups->count()) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-circle-check"></i></div>
                <div class="ui-stat-label">{{ db_trans('active') }}</div>
                <div class="ui-stat-value">{{ number_format($activeCount) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-danger"><i class="fas fa-ban"></i></div>
                <div class="ui-stat-label">{{ db_trans('inactive') }}</div>
                <div class="ui-stat-value">{{ number_format($inactiveCount) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-mini-card p-4 h-100">
                <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-calendar-xmark"></i></div>
                <div class="ui-stat-label">{{ db_trans('members_without_dob') }}</div>
                <div class="ui-stat-value">{{ number_format((int) ($membersWithoutDobCount ?? 0)) }}</div>
            </div>
        </div>
    </div>

    <div class="ui-table-card p-4">
        <div class="ui-section-heading">
            <div>
                <h5 class="mb-1">{{ db_trans('age_groups') }}</h5>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('pdf.membership.age-groups.export') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                </a>

                <a href="{{ route('membership.age-groups.export.excel') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                </a>

                <span class="ui-section-badge">{{ number_format($ageGroups->count()) }} {{ db_trans('records') }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="ageGroupsTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('sn') }}</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('age_range') }}</th>
                        <th>{{ db_trans('gender_scope') }}</th>
                        <th>{{ db_trans('members') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('description') }}</th>
                        <th class="text-end">{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ageGroups as $ageGroup)
                        @php
                            $normalizedScope = \App\Models\AgeGroup::normalizeGenderScope($ageGroup->gender_scope ?? null);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $ageGroup->name }}</td>
                            <td>{{ $ageGroup->min_age }} - {{ $ageGroup->max_age }}</td>
                            <td>{{ $genderScopes[$normalizedScope] ?? ($ageGroup->gender_scope_label ?? '—') }}</td>
                            <td>{{ number_format((int) $ageGroup->members_count) }}</td>
                            <td>
                                <span class="ui-status-pill {{ $ageGroup->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">
                                    {{ $ageGroup->is_active ? db_trans('active') : db_trans('inactive') }}
                                </span>
                            </td>
                            <td>{{ $ageGroup->description ?: '—' }}</td>
                            <td class="text-end text-nowrap">
                                @can('membership.age-groups.update')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary js-edit-age-group"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editAgeGroupModal"
                                        data-id="{{ $ageGroup->id }}"
                                        data-name="{{ $ageGroup->name }}"
                                        data-min-age="{{ $ageGroup->min_age }}"
                                        data-max-age="{{ $ageGroup->max_age }}"
                                        data-gender-scope="{{ $normalizedScope }}"
                                        data-description="{{ $ageGroup->description }}"
                                        data-is-active="{{ $ageGroup->is_active ? 1 : 0 }}">
                                        <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                    </button>
                                @endcan

                                @can('membership.age-groups.delete')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger js-delete-age-group"
                                        data-url="{{ route('membership.age-groups.destroy', $ageGroup) }}"
                                        data-name="{{ $ageGroup->name }}">
                                        <i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@can('membership.age-groups.create')
    @include('admin.membership.age-groups.partials.create-modal', ['genderScopes' => $genderScopes])
@endcan

@can('membership.age-groups.update')
    @include('admin.membership.age-groups.partials.edit-modal', ['genderScopes' => $genderScopes])
@endcan
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    @include('admin.membership.age-groups.partials.scripts')
@endpush
