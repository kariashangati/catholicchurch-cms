@extends('layouts.admin')

@section('title', db_trans('family_details'))
@section('disable_default_alerts', true)

@section('content')
    @php
        use Illuminate\Support\Str;

        $mapLocalizedValue = function (?string $value, array $translations = []) {
            $locale = app()->getLocale();

            if (blank($value)) {
                return '—';
            }

            $normalized = strtolower(trim($value));

            if (isset($translations[$normalized])) {
                return $translations[$normalized][$locale] ?? $translations[$normalized]['en'] ?? ucfirst($value);
            }

            return ucfirst($value);
        };

        $memberInitial = fn ($member) => Str::upper(mb_substr($member->first_name ?: $member->last_name ?: 'M', 0, 1));

        $roleChartData = collect($chartData['roleData'] ?? [])->map(fn ($value) => (int) $value)->values();
        $sacramentChartData = collect($chartData['sacramentData'] ?? [])->map(fn ($value) => (int) $value)->values();
    @endphp

    <div class="familia-show-page">
        <div class="dashboard-hero familia-dashboard-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="dashboard-hero-badge">{{ db_trans('family_profile') }}</span>
                        <span class="familia-hero-pill {{ $familia->is_active ? 'is-active' : 'is-inactive' }}">
                            <i class="fas {{ $familia->is_active ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                            {{ $familia->is_active ? db_trans('active_family') : db_trans('inactive_family') }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="familia-profile-avatar">{{ strtoupper(mb_substr($familia->name, 0, 1)) }}</div>
                        <div>
                            <h1 class="dashboard-title mb-2">{{ $familia->name }}</h1>
                            <p class="dashboard-subtitle mb-0">
                                {{ $familia->jumuiya?->name ?? '—' }} · {{ $familia->jumuiya?->kanda?->name ?? '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="familia-hero-pills">
                        <span class="familia-hero-pill">
                            <i class="fas fa-users"></i>{{ number_format($stats['members_count']) }} {{ db_trans('members') }}
                        </span>
                        <span class="familia-hero-pill">
                            <i class="fas fa-envelope"></i>{{ $familia->envelope_no ?: '—' }}
                        </span>
                        <span class="familia-hero-pill">
                            <i class="fas fa-phone"></i>{{ $familia->phone ?: '—' }}
                        </span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="familia-quick-grid">
                        @can('familias.members.create')
                            <button type="button" class="familia-quick-card" data-bs-toggle="modal" data-bs-target="#memberCreateModal">
                                <span class="familia-quick-icon"><i class="fas fa-user-plus"></i></span>
                                <span><strong>{{ db_trans('add_family_member') }}</strong></span>
                            </button>
                        @endcan

                        @can('familias.update')
                            <button type="button" class="familia-quick-card" data-bs-toggle="modal" data-bs-target="#familiaQuickEditModal">
                                <span class="familia-quick-icon"><i class="fas fa-pen"></i></span>
                                <span><strong>{{ db_trans('edit_family') }}</strong></span>
                            </button>
                        @endcan

                        <a href="{{ route('familias.index') }}" class="familia-quick-card">
                            <span class="familia-quick-icon"><i class="fas fa-table"></i></span>
                            <span><strong>{{ db_trans('family_directory') }}</strong></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach([
                ['label' => db_trans('members_count'), 'value' => number_format($stats['members_count']), 'icon' => 'fa-users', 'tone' => 'primary'],
                ['label' => db_trans('male_members'), 'value' => number_format($stats['male_members_count']), 'icon' => 'fa-person', 'tone' => 'info'],
                ['label' => db_trans('female_members'), 'value' => number_format($stats['female_members_count']), 'icon' => 'fa-person-dress', 'tone' => 'secondary'],
                ['label' => db_trans('baptized'), 'value' => number_format($stats['baptized_count']), 'icon' => 'fa-droplet', 'tone' => 'success'],
                ['label' => db_trans('communion'), 'value' => number_format($stats['communion_count']), 'icon' => 'fa-bread-slice', 'tone' => 'warning'],
                ['label' => db_trans('married'), 'value' => number_format($stats['married_count']), 'icon' => 'fa-ring', 'tone' => 'danger'],
            ] as $card)
                <div class="col-xxl-2 col-xl-4 col-md-6">
                    <div class="familia-kpi-card familia-tone-{{ $card['tone'] }}">
                        <div class="familia-kpi-icon"><i class="fas {{ $card['icon'] }}"></i></div>
                        <div class="familia-kpi-label">{{ $card['label'] }}</div>
                        <div class="familia-kpi-value">{{ $card['value'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card dashboard-panel familia-panel border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-3">
                            <div>
                                <h5 class="mb-1">{{ db_trans('family_roles_breakdown') }}</h5>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="familiaRoleChart"></canvas>
                        </div>

                        @if($roleChartData->sum() <= 0)
                            <div class="text-center text-muted small mt-3">
                                {{ db_trans('no_data_found') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card dashboard-panel familia-panel border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-3">
                            <div>
                                <h5 class="mb-1">{{ db_trans('family_sacrament_overview') }}</h5>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="familiaSacramentChart"></canvas>
                        </div>

                        @if($sacramentChartData->sum() <= 0)
                            <div class="text-center text-muted small mt-3">
                                {{ db_trans('no_data_found') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card dashboard-panel familia-panel border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-4">
                            <div>
                                <h5 class="mb-1">{{ db_trans('family_financial_overview') }}</h5>
                                <p class="text-muted mb-0">{{ db_trans('family_financial_overview_description') }}</p>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table familia-finance-table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ db_trans('contribution_type') }}</th>
                                        <th>{{ $finance['current_year'] }}</th>
                                        <th>{{ $finance['previous_year'] }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ db_trans('tithes') }}</td>
                                        <td>{{ number_format($finance['current']['zaka'], 2) }}</td>
                                        <td>{{ number_format($finance['previous']['zaka'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ db_trans('harvest') }}</td>
                                        <td>{{ number_format($finance['current']['mavuno'], 2) }}</td>
                                        <td>{{ number_format($finance['previous']['mavuno'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ db_trans('other_contributions') }}</td>
                                        <td>{{ number_format($finance['current']['other'], 2) }}</td>
                                        <td>{{ number_format($finance['previous']['other'], 2) }}</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td>{{ db_trans('total') }}</td>
                                        <td>{{ number_format($finance['current']['total'], 2) }}</td>
                                        <td>{{ number_format($finance['previous']['total'], 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dashboard-panel familia-panel border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-4">
                            <div>
                                <h5 class="mb-1">{{ db_trans('family_members_roster') }}</h5>
                                <p class="text-muted mb-0">{{ db_trans('household_roles_and_status_overview') }}</p>
                            </div>
                        </div>

                        <div class="familia-mini-kpis">
                            <div class="mini-stat-box">
                                <div class="mini-stat-label">{{ db_trans('father') }}</div>
                                <div class="mini-stat-value">{{ number_format($stats['fathers_count']) }}</div>
                            </div>
                            <div class="mini-stat-box">
                                <div class="mini-stat-label">{{ db_trans('mother') }}</div>
                                <div class="mini-stat-value">{{ number_format($stats['mothers_count']) }}</div>
                            </div>
                            <div class="mini-stat-box">
                                <div class="mini-stat-label">{{ db_trans('child') }}</div>
                                <div class="mini-stat-value">{{ number_format($stats['children_count']) }}</div>
                            </div>
                        </div>

                        <div class="familia-progress-panel mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-semibold">{{ db_trans('profile_readiness') }}</span>
                                <span class="familia-progress-value">{{ $stats['profile_readiness'] }}%</span>
                            </div>
                            <div class="progress familia-progress">
                                <div class="progress-bar" role="progressbar" style="width: {{ $stats['profile_readiness'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card familia-directory-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 p-4 pb-0">
                <div class="familia-section-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">{{ db_trans('household_members') }}</h5>
                        <p class="text-muted mb-0">{{ db_trans('manage_family_members_records') }}</p>
                    </div>

                    @can('familias.view')
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('pdf.familias.members.export', $familia) }}" class="btn btn-sm btn-danger rounded-pill">
                                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                            </a>

                            <a href="{{ route('familias.members.export.excel', $familia) }}" class="btn btn-sm btn-success rounded-pill">
                                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>

            <div class="card-body p-4 pt-3">
                <div class="table-responsive">
                    <table class="table align-middle familia-data-table w-100" id="familiaMembersTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('member') }}</th>
                                <th>{{ db_trans('gender') }}</th>
                                <th>{{ db_trans('family_role') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('sacraments') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                @php
                                    $genderLabel = $mapLocalizedValue($member->gender, [
                                        'male' => ['en' => 'Male', 'sw' => 'Mwanaume'],
                                        'female' => ['en' => 'Female', 'sw' => 'Mwanamke'],
                                        'mwanaume' => ['en' => 'Male', 'sw' => 'Mwanaume'],
                                        'mwanamke' => ['en' => 'Female', 'sw' => 'Mwanamke'],
                                    ]);

                                    $roleLabel = $mapLocalizedValue($member->family_role, [
                                        'father' => ['en' => 'Father', 'sw' => 'Baba'],
                                        'mother' => ['en' => 'Mother', 'sw' => 'Mama'],
                                        'child' => ['en' => 'Child', 'sw' => 'Mtoto'],
                                        'other' => ['en' => 'Other', 'sw' => 'Nyingine'],
                                        'baba' => ['en' => 'Father', 'sw' => 'Baba'],
                                        'mama' => ['en' => 'Mother', 'sw' => 'Mama'],
                                        'mtoto' => ['en' => 'Child', 'sw' => 'Mtoto'],
                                        'nyingine' => ['en' => 'Other', 'sw' => 'Nyingine'],
                                    ]);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="familia-row-identity">
                                            <div class="familia-row-avatar">{{ $memberInitial($member) }}</div>
                                            <div>
                                                <div class="familia-row-title">{{ $member->full_name }}</div>
                                                <div class="familia-row-subtitle">{{ $member->member_code ?: '—' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="familia-tag-chip">{{ $genderLabel }}</span>
                                    </td>

                                    <td>
                                        <span class="familia-tag-chip secondary">{{ $roleLabel }}</span>
                                    </td>

                                    <td>{{ $member->phone ?: '—' }}</td>

                                    <td>
                                        <div class="familia-sacrament-badges">
                                            @foreach([
                                                'is_baptized' => db_trans('baptized_short'),
                                                'has_communion' => db_trans('communion_short'),
                                                'has_confirmation' => db_trans('confirmation_short'),
                                                'is_married' => db_trans('married_short'),
                                            ] as $field => $label)
                                                @if($member->{$field})
                                                    <span class="familia-inline-badge">{{ $label }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>

                                    <td>
                                        <span class="familia-status-badge {{ $member->is_active ? 'active' : 'inactive' }}">
                                            {{ $member->is_active ? db_trans('active') : db_trans('inactive') }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="familia-action-row">
                                            @can('familias.members.update')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm familia-btn-soft familia-member-edit-trigger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#memberEditModal"
                                                    data-action="{{ route('familias.members.update', [$familia, $member]) }}"
                                                    data-id="{{ $member->id }}"
                                                    data-first_name="{{ $member->first_name }}"
                                                    data-middle_name="{{ $member->middle_name }}"
                                                    data-last_name="{{ $member->last_name }}"
                                                    data-phone="{{ $member->phone }}"
                                                    data-gender="{{ $member->gender }}"
                                                    data-date_of_birth="{{ optional($member->date_of_birth)->format('Y-m-d') }}"
                                                    data-occupation="{{ $member->occupation }}"
                                                    data-family_role="{{ $member->family_role }}"
                                                    data-member_code="{{ $member->member_code }}"
                                                    data-bahasha="{{ $member->bahasha }}"
                                                    data-notes="{{ $member->notes }}"
                                                    data-is_active="{{ $member->is_active ? 1 : 0 }}"
                                                    data-is_baptized="{{ $member->is_baptized ? 1 : 0 }}"
                                                    data-has_communion="{{ $member->has_communion ? 1 : 0 }}"
                                                    data-has_confirmation="{{ $member->has_confirmation ? 1 : 0 }}"
                                                    data-receives_eucharist="{{ $member->receives_eucharist ? 1 : 0 }}"
                                                    data-is_married="{{ $member->is_married ? 1 : 0 }}"
                                                    data-marriage_type="{{ $member->marriage_type }}"
                                                    data-baptism_certificate_number="{{ $member->baptism_certificate_number }}"
                                                    data-marriage_certificate_number="{{ $member->marriage_certificate_number }}"
                                                    data-baptism_parish="{{ $member->baptism_parish }}"
                                                    data-baptism_diocese="{{ $member->baptism_diocese }}"
                                                >
                                                    <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                                </button>
                                            @endcan

                                            @can('familias.members.delete')
                                                <form method="POST" action="{{ route('familias.members.destroy', [$familia, $member]) }}" class="familia-member-delete-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm familia-btn-danger">
                                                        <i class="fas fa-trash-alt me-1"></i>{{ db_trans('remove') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @can('familias.update')
        <div class="modal fade" id="familiaQuickEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content familia-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">{{ db_trans('edit_family') }}</h5>
                            <p class="text-muted small mb-0">{{ db_trans('update_family_record_and_contact_information') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="{{ route('familias.update', $familia) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" value="familia_edit_{{ $familia->id }}">

                        <div class="modal-body pt-3">
                            @include('admin.familias.partials.familia-form-fields', [
                                'prefix' => 'quick_',
                                'kandas' => $kandas,
                                'jumuiyas' => $jumuiyas,
                                'model' => $familia,
                            ])
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ db_trans('update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('familias.members.create')
        <div class="modal fade" id="memberCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content familia-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">{{ db_trans('add_family_member') }}</h5>
                            <p class="text-muted small mb-0">{{ db_trans('register_new_household_member') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="{{ route('familias.members.store', $familia) }}">
                        @csrf
                        <input type="hidden" name="form_context" value="member_create">

                        <div class="modal-body pt-3">
                            @include('admin.familias.partials.member-form-fields', [
                                'prefix' => 'create_member_',
                                'roles' => $availableFamiliaRoles,
                            ])
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ db_trans('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('familias.members.update')
        <div class="modal fade" id="memberEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content familia-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">{{ db_trans('edit_family_member') }}</h5>
                            <p class="text-muted small mb-0">{{ db_trans('update_household_member_information') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form method="POST" action="#" id="memberEditForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" id="memberEditFormContext" value="member_edit">

                        <div class="modal-body pt-3">
                            @include('admin.familias.partials.member-form-fields', [
                                'prefix' => 'edit_member_',
                                'roles' => $availableFamiliaRoles,
                            ])
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ db_trans('update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const makeChart = (id, type, labels, data, colors) => {
                const el = document.getElementById(id);
                if (!el || typeof Chart === 'undefined') return;

                const values = Array.isArray(data) ? data.map(value => Number(value || 0)) : [];

                new Chart(el, {
                    type,
                    data: {
                        labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
                            borderWidth: 0,
                            borderRadius: type === 'bar' ? 10 : 0,
                            maxBarThickness: 34
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: type === 'bar' ? 'top' : 'bottom'
                            }
                        },
                        scales: type === 'bar'
                            ? {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                            : {}
                    }
                });
            };

            makeChart(
                'familiaRoleChart',
                'pie',
                @json($chartData['roleLabels'] ?? []),
                @json($chartData['roleData'] ?? []),
                ['#2563eb', '#8b5cf6', '#f59e0b']
            );

            makeChart(
                'familiaSacramentChart',
                'bar',
                @json($chartData['sacramentLabels'] ?? []),
                @json($chartData['sacramentData'] ?? []),
                ['#16a34a', '#0ea5e9', '#7c3aed', '#ef4444']
            );

            if (window.jQuery && $('#familiaMembersTable').length && !$.fn.DataTable.isDataTable('#familiaMembersTable')) {
                $('#familiaMembersTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    order: [[0, 'asc']],
                    language: {
                        search: @json(db_trans('search')),
                        lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                        info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                        infoEmpty: @json(db_trans('showing')) + ' 0 ' + @json(db_trans('to')) + ' 0 ' + @json(db_trans('of')) + ' 0 ' + @json(db_trans('entries')),
                        zeroRecords: @json(db_trans('no_data_found')),
                        paginate: {
                            previous: @json(db_trans('previous')),
                            next: @json(db_trans('next'))
                        }
                    }
                });
            }

            const setToggleTargetState = (checkbox) => {
                const selector = checkbox.dataset.target;
                if (!selector) return;

                const target = document.querySelector(selector);
                if (!target) return;

                target.classList.toggle('d-none', !checkbox.checked);
            };

            document.querySelectorAll('.familia-sacrament-toggle').forEach((checkbox) => {
                setToggleTargetState(checkbox);
                checkbox.addEventListener('change', () => setToggleTargetState(checkbox));
            });

            document.querySelectorAll('.familia-member-edit-trigger').forEach((button) => {
                button.addEventListener('click', function () {
                    const form = document.getElementById('memberEditForm');
                    if (!form) return;

                    form.action = this.dataset.action || '#';

                    const context = document.getElementById('memberEditFormContext');
                    if (context) {
                        context.value = 'member_edit_' + this.dataset.id;
                    }

                    const set = (idSuffix, value) => {
                        const input = document.getElementById('edit_member_' + idSuffix);
                        if (input) {
                            input.value = value || '';
                        }
                    };

                    const check = (name, value) => {
                        const input = document.querySelector('#memberEditModal input[name="' + name + '"]');
                        if (input) {
                            input.checked = value === '1';
                            input.dispatchEvent(new Event('change'));
                        }
                    };

                    set('first_name', this.dataset.first_name);
                    set('middle_name', this.dataset.middle_name);
                    set('last_name', this.dataset.last_name);
                    set('phone', this.dataset.phone);
                    set('gender', this.dataset.gender);
                    set('date_of_birth', this.dataset.date_of_birth);
                    set('occupation', this.dataset.occupation);
                    set('family_role', this.dataset.family_role);
                    set('member_code', this.dataset.member_code);
                    set('bahasha', this.dataset.bahasha);
                    set('notes', this.dataset.notes);
                    set('marriage_type', this.dataset.marriage_type);
                    set('baptism_certificate_number', this.dataset.baptism_certificate_number);
                    set('marriage_certificate_number', this.dataset.marriage_certificate_number);
                    set('baptism_parish', this.dataset.baptism_parish);
                    set('baptism_diocese', this.dataset.baptism_diocese);

                    check('is_active', this.dataset.is_active);
                    check('is_baptized', this.dataset.is_baptized);
                    check('has_communion', this.dataset.has_communion);
                    check('has_confirmation', this.dataset.has_confirmation);
                    check('receives_eucharist', this.dataset.receives_eucharist);
                    check('is_married', this.dataset.is_married);
                });
            });

            document.querySelectorAll('.familia-member-delete-form').forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('confirm_delete_family_member')),
                        text: @json(db_trans('this_action_cannot_be_undone')),
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#7c3aed',
                        confirmButtonText: @json(db_trans('remove')),
                        cancelButtonText: @json(db_trans('cancel')),
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const previousFormContext = @json(old('form_context'));

            if (previousFormContext === 'member_create') {
                const modal = document.getElementById('memberCreateModal');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
            }

            if (previousFormContext && previousFormContext.startsWith('member_edit_')) {
                const modal = document.getElementById('memberEditModal');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
            }

            if (previousFormContext && previousFormContext.startsWith('familia_edit_')) {
                const modal = document.getElementById('familiaQuickEditModal');
                if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        });
    </script>
@endpush