@extends('layouts.admin')

@section('title', db_trans('members'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-members-v3.css') }}">
@endpush

@section('content')
    @php
        $statCards = [
            ['label' => db_trans('total_members'), 'value' => $stats['total_members'], 'meta' => db_trans('member_directory'), 'icon' => 'fas fa-users', 'tone' => 'primary'],
            ['label' => db_trans('male_members'), 'value' => $stats['male_members'], 'meta' => db_trans('father'), 'icon' => 'fas fa-mars', 'tone' => 'info'],
            ['label' => db_trans('female_members'), 'value' => $stats['female_members'], 'meta' => db_trans('mother'), 'icon' => 'fas fa-venus', 'tone' => 'success'],
            ['label' => db_trans('fathers'), 'value' => $stats['fathers'], 'meta' => db_trans('family_role'), 'icon' => 'fas fa-user-tie', 'tone' => 'warning'],
            ['label' => db_trans('mothers'), 'value' => $stats['mothers'], 'meta' => db_trans('family_role'), 'icon' => 'fas fa-female', 'tone' => 'secondary'],
            ['label' => db_trans('children'), 'value' => $stats['children'], 'meta' => db_trans('family_role'), 'icon' => 'fas fa-child', 'tone' => 'dark'],
            ['label' => db_trans('active_members'), 'value' => $stats['active_members'], 'meta' => db_trans('active'), 'icon' => 'fas fa-user-check', 'tone' => 'success'],
            ['label' => db_trans('new_members_this_month'), 'value' => $stats['new_members_this_month'], 'meta' => db_trans('overview'), 'icon' => 'fas fa-user-plus', 'tone' => 'primary'],
        ];
    @endphp

    <div class="dashboard-hero members-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="dashboard-hero-badge">{{ db_trans('member_directory') }}</span>
                <h2 class="dashboard-title mb-2">{{ db_trans('members') }}</h2>
                <div class="members-hero-pills">
                    <span class="hero-pill"><i class="fas fa-users me-2"></i>{{ number_format($stats['total_members']) }} {{ db_trans('total_members') }}</span>
                    <span class="hero-pill"><i class="fas fa-phone-slash me-2"></i>{{ number_format($stats['members_without_phone']) }} {{ db_trans('members_without_phone') }}</span>
                    <span class="hero-pill"><i class="fas fa-church me-2"></i>{{ number_format($stats['members_missing_sacrament_data']) }} {{ db_trans('members_missing_sacrament_data') }}</span>
                </div>
            </div>

            <div class="col-lg-5">
                <form method="GET" action="{{ route('members.index') }}" class="hero-filter-card">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">{{ db_trans('year') }}</label>
                            <input type="number" name="year" class="form-control" value="{{ $filters['year'] ?? now()->year }}" min="2000" max="2100">
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ db_trans('month') }}</label>
                            <select name="month" class="form-select">
                                <option value="">{{ db_trans('all_months') }}</option>
                                @foreach(range(1, 12) as $monthNumber)
                                    <option value="{{ $monthNumber }}" @selected((string)($filters['month'] ?? '') === (string)$monthNumber)>
                                        {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ db_trans('kanda') }}</label>
                            <select name="kanda_id" class="form-select">
                                <option value="">{{ db_trans('all_kandas') }}</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" @selected((string)($filters['kanda_id'] ?? '') === (string)$kanda->id)>{{ $kanda->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ db_trans('jumuiya') }}</label>
                            <select name="jumuiya_id" class="form-select">
                                <option value="">{{ db_trans('all_jumuiyas') }}</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" @selected((string)($filters['jumuiya_id'] ?? '') === (string)$jumuiya->id)>{{ $jumuiya->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-light flex-fill"><i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}</button>
                            <a href="{{ route('members.index') }}" class="btn btn-outline-light">{{ db_trans('reset') }}</a>
                        </div>
                    </div>
                </form>

                <div class="members-hero-actions mt-3">
                    @can('members.create')
                        <button type="button" class="hero-action-card" data-bs-toggle="modal" data-bs-target="#memberCreateModal">
                            <span class="hero-action-icon"><i class="fas fa-user-plus"></i></span>
                            <span><strong>{{ db_trans('add_member') }}</strong></span>
                        </button>
                    @endcan
                    @can('kandas.create')
                        <button type="button" class="hero-action-card" data-bs-toggle="modal" data-bs-target="#kandaCreateModal">
                            <span class="hero-action-icon"><i class="fas fa-layer-group"></i></span>
                            <span><strong>{{ db_trans('add_kanda') }}</strong></span>
                        </button>
                    @endcan
                </div>
            </div>        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach($statCards as $card)
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card tone-{{ $card['tone'] }} h-100">
                    <div class="stat-card__icon"><i class="{{ $card['icon'] }}"></i></div>
                    <div class="stat-card__content">
                        <h3>{{ $card['label'] }}</h3>
                        <div class="stat-card__value">{{ number_format($card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card dashboard-panel h-100 members-panel">
                <div class="card-header bg-transparent border-0 p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">{{ db_trans('member_growth') }}</h5>
                    </div>
                    <span class="panel-chip">{{ db_trans('last_6_months') }}</span>
                </div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="members-chart-wrap">
                        <canvas id="memberGrowthChart" height="118"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <a href="{{ route('sacraments.index') }}" class="text-decoration-none d-block h-100">
                <div class="card dashboard-panel h-100 members-panel">
                    <div class="card-header bg-transparent border-0 p-4 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold text-dark mb-0">{{ db_trans('sacrament_overview') }}</h5>
                        <span class="panel-chip">{{ db_trans('open') }}</span>
                    </div>
                    <div class="card-body pt-0 px-4 pb-4">
                        <div class="members-chart-wrap">
                            <canvas id="sacramentOverviewChart" height="118"></canvas>
                        </div>
                    </div>
                </div>
            </a>
        </div>    </div>

    <div class="card dashboard-panel members-panel">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="row g-3 align-items-center">
                <div class="col-lg-6">
                    <h5 class="fw-bold text-dark mb-0">{{ db_trans('members') }}</h5>
                </div>

                <div class="col-lg-6 text-lg-end">
                    @can('members.view')
                        <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                            <a href="{{ route('pdf.members.export', request()->query()) }}" class="btn btn-sm btn-danger rounded-pill">
                                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                            </a>

                            <a href="{{ route('members.export.excel', request()->query()) }}" class="btn btn-sm btn-success rounded-pill">
                                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card-body pt-0 px-4 pb-4">
            <form method="GET" action="{{ route('members.index') }}" class="members-filter-bar mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">{{ db_trans('search') }}</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="{{ db_trans('member_directory') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ db_trans('gender') }}</label>
                        <select name="gender" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender }}" @selected(($filters['gender'] ?? '') === $gender)>{{ db_trans(strtolower($gender)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ db_trans('family_role') }}</label>
                        <select name="family_role" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            @foreach($familyRoles as $role)
                                <option value="{{ $role }}" @selected(($filters['family_role'] ?? '') === $role)>{{ db_trans(strtolower($role)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ db_trans('status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ db_trans('all') }}</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ db_trans('active') }}</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ db_trans('inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>{{ db_trans('filter') }}</button>
                        <a href="{{ route('members.index') }}" class="btn btn-light w-100">{{ db_trans('reset') }}</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive members-table-shell">
                <table class="table align-middle table-hover mb-0" id="membersTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('member_code') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('familia') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('kanda') }}</th>
                            <th>{{ db_trans('sacraments') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="member-avatar-sm">
                                            {{ strtoupper(substr($member->first_name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $member->full_name }}</div>
                                           @php
    $genderKey = match(strtolower((string) $member->gender)) {
        'male', 'mwanaume' => 'male',
        'female', 'mwanamke' => 'female',
        default => 'other',
    };

    $roleKey = match(strtolower((string) $member->family_role)) {
        'father', 'baba' => 'father',
        'mother', 'mama' => 'mother',
        'child', 'mtoto' => 'child',
        'other', 'nyingine' => 'other',
        default => null,
    };
@endphp

<div class="small text-muted">
    {{ db_trans($genderKey) }} · {{ $roleKey ? db_trans($roleKey) : '—' }}
</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->member_code ?: '—' }}</td>
                                <td>{{ $member->phone ?: '—' }}</td>
                                <td>{{ $member->familia?->name ?: '—' }}</td>
                                <td>{{ $member->familia?->jumuiya?->name ?: '—' }}</td>
                                <td>{{ $member->familia?->jumuiya?->kanda?->name ?: '—' }}</td>
                                <td>
                                    <div class="member-sacrament-badges">
                                        @if($member->is_baptized)<span class="badge badge-soft-primary">{{ db_trans('baptized') }}</span>@endif
                                        @if($member->has_communion)<span class="badge badge-soft-success">{{ db_trans('communion') }}</span>@endif
                                        @if($member->has_confirmation)<span class="badge badge-soft-warning">{{ db_trans('confirmation') }}</span>@endif
                                        @if($member->receives_eucharist)<span class="badge badge-soft-info">{{ db_trans('eucharist') }}</span>@endif
                                        @if($member->is_married)<span class="badge badge-soft-secondary">{{ db_trans('married') }}</span>@endif
                                        @if(! $member->is_baptized && ! $member->has_communion && ! $member->has_confirmation && ! $member->receives_eucharist && ! $member->is_married)
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($member->is_active)
                                        <span class="badge rounded-pill text-bg-success">{{ db_trans('active') }}</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary">{{ db_trans('inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @can('members.view')
                                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('view') }}</a>
                                        @endcan
                                        @can('members.update')
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#memberEditModal{{ $member->id }}">{{ db_trans('edit') }}</button>
                                        @endcan
                                        @can('members.delete')
                                            <form action="{{ route('members.destroy', $member) }}" method="POST" class="js-member-delete-form d-inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">{{ db_trans('no_members_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('members.create')
        @include('admin.members.partials.member-form-modal', [
            'mode' => 'create',
            'modalId' => 'memberCreateModal',
            'member' => null,
            'familias' => $familias,
        ])
    @endcan

    @can('members.update')
        @foreach($members as $member)
            @include('admin.members.partials.member-form-modal', [
                'mode' => 'edit',
                'modalId' => 'memberEditModal'.$member->id,
                'member' => $member,
                'familias' => $familias,
            ])
        @endforeach
    @endcan
    @can('kandas.create')
        <div class="modal fade" id="kandaCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4">
                    <form action="{{ route('kandas.store') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">{{ db_trans('add_kanda') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ db_trans('kanda') }}</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ db_trans('code') }}</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">{{ db_trans('comment') }}</label>
                                    <textarea name="comment" class="form-control" rows="4">{{ old('comment') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="kanda_is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kanda_is_active">{{ db_trans('active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>{{ db_trans('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Chart) {
                const ctx = document.getElementById('memberGrowthChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($memberGrowthChart['labels']),
                            datasets: [{
                                label: @json(db_trans('members')),
                                data: @json($memberGrowthChart['data']),
                                borderColor: '#7c3aed',
                                backgroundColor: 'rgba(124,58,237,0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 4,
                                pointHoverRadius: 5
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                const sacramentCtx = document.getElementById('sacramentOverviewChart');
                if (sacramentCtx) {
                    new Chart(sacramentCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($sacramentOverviewChart['labels']),
                            datasets: [{
                                label: @json(db_trans('sacraments')),
                                data: @json($sacramentOverviewChart['data']),
                                backgroundColor: '#7c3aed',
                                borderRadius: 12,
                                maxBarThickness: 42
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }

            if (window.jQuery && jQuery.fn.DataTable) {
                jQuery('#membersTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    stateSave: true,
                    deferRender: true,
                    order: [[0, 'asc']],
                    language: {
                        search: '',
                        searchPlaceholder: @json(db_trans('search')),
                        lengthMenu: '_MENU_',
                    },
                    columnDefs: [
                        { orderable: false, targets: [6, 8] }
                    ]
                });
            }

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('.js-familia-select').each(function () {
                    const select = jQuery(this);
                    const modal = select.closest('.modal');

                    select.select2({
                        dropdownParent: modal.length ? modal : jQuery(document.body),
                        width: '100%',
                        placeholder: @json(db_trans('select_familia')),
                        allowClear: true
                    });
                });
            }

            document.querySelectorAll('.member-form-modal').forEach(function (modalEl) {
                const toggleMap = {
                    is_baptized: modalEl.querySelector('[data-sacrament-section="baptism"]'),
                    is_married: modalEl.querySelector('[data-sacrament-section="marriage"]')
                };

                Object.keys(toggleMap).forEach(function (key) {
                    const checkbox = modalEl.querySelector('[data-sacrament-toggle="' + key + '"]');
                    const section = toggleMap[key];
                    if (!checkbox || !section) return;

                    const refresh = function () {
                        section.classList.toggle('d-none', !checkbox.checked);
                    };

                    checkbox.addEventListener('change', refresh);
                    refresh();
                });
            });

            document.querySelectorAll('.js-member-delete-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('confirm_delete_member')),
                        text: @json(db_trans('delete_member_confirmation_text')),
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: @json(db_trans('delete')),
                        cancelButtonText: @json(db_trans('cancel'))
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const hasErrors = @json($errors->any());
            const open = @json(request('open'));
            const openMemberId = @json(request('member'));
            const oldMode = @json(old('member_form_mode'));
            const oldMemberId = @json(old('modal_member_id'));

            let targetModal = null;

            if (hasErrors && !oldMode && document.getElementById('kandaCreateModal')) {
                targetModal = document.getElementById('kandaCreateModal');
            } else if (hasErrors) {
                if (oldMode === 'edit' && oldMemberId) {
                    targetModal = document.getElementById('memberEditModal' + oldMemberId);
                } else {
                    targetModal = document.getElementById('memberCreateModal');
                }
            } else if (open === 'create') {
                targetModal = document.getElementById('memberCreateModal');
            } else if (open === 'edit' && openMemberId) {
                targetModal = document.getElementById('memberEditModal' + openMemberId);
            }

            if (targetModal && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(targetModal).show();
            }
        });
    </script>
@endpush