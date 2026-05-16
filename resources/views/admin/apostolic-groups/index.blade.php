@extends('layouts.admin')

@section('title', $pageTitle)
@section('disable_default_alerts', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-apostolic-groups-v3.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
@php
    $statCards = [
        ['label' => db_trans('apostolic_groups'), 'value' => $stats['total_groups'] ?? 0, 'icon' => 'fa-layer-group', 'accent' => 'violet'],
        ['label' => db_trans('active_apostolic_groups'), 'value' => $stats['active_groups'] ?? 0, 'icon' => 'fa-circle-check', 'accent' => 'green'],
        ['label' => db_trans('inactive_apostolic_groups'), 'value' => $stats['inactive_groups'] ?? 0, 'icon' => 'fa-circle-pause', 'accent' => 'orange'],
        ['label' => db_trans('active_memberships'), 'value' => $stats['active_memberships'] ?? 0, 'icon' => 'fa-users', 'accent' => 'blue'],
        ['label' => db_trans('groups_without_leaders'), 'value' => $stats['groups_without_leaders'] ?? 0, 'icon' => 'fa-user-slash', 'accent' => 'rose'],
        ['label' => db_trans('groups_with_meetings'), 'value' => $stats['groups_with_meetings'] ?? 0, 'icon' => 'fa-calendar-check', 'accent' => 'teal'],
    ];
@endphp

<div class="apg-dashboard-page">
    <section class="dashboard-hero apg-hero mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-xl-7">
              
                <h1 class="dashboard-title mb-2">{{ db_trans('apostolic_group_directory') }}</h1>

                <div class="apg-hero-pills">
                    <span class="apg-hero-pill"><i class="fas fa-layer-group me-2"></i>{{ ($stats['total_groups'] ?? 0) }} {{ db_trans('groups') ?: 'Groups' }}</span>
                    <span class="apg-hero-pill"><i class="fas fa-users me-2"></i>{{ ($stats['active_memberships'] ?? 0) }} {{ db_trans('active_memberships') }}</span>
                    <span class="apg-hero-pill"><i class="fas fa-bolt me-2"></i>{{ ($stats['auto_rule_groups'] ?? 0) }} {{ db_trans('automatic_rules') }}</span>
                </div>
            </div>

    <div class="col-xl-5">
    <div class="d-flex flex-column gap-2">
        @can('apostolic-groups.create')
            <div class="text-xl-end">
                <button type="button"
                        class="btn btn-light btn-sm px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#createGroupModal">
                    <i class="fas fa-plus me-1"></i>{{ db_trans('add_apostolic_group') }}
                </button>
            </div>
        @endcan

        <form method="GET"
              action="{{ route('apostolic-groups.index') }}"
              class="apg-hero-filter-card p-2">
            <div class="row g-2 align-items-end">
                <div class="col-5">
                    <label class="form-label small mb-1">{{ db_trans('year') }}</label>
                    <select name="year" class="form-select form-select-sm">
                        @foreach($filterOptions['years'] as $year)
                            <option value="{{ $year }}" @selected((int) $filters['year'] === (int) $year)>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-5">
                    <label class="form-label small mb-1">{{ db_trans('month') }}</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">{{ db_trans('all_months') }}</option>
                        @foreach($filterOptions['months'] as $month)
                            <option value="{{ $month['value'] }}" @selected((string) $filters['month'] === (string) $month['value'])>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2">
                    <button type="submit" class="btn btn-light btn-sm w-100" title="{{ db_trans('apply_filters') }}">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        @foreach($statCards as $card)
            <div class="col-md-6 col-xl-4">
                <div class="apg-kpi-card accent-{{ $card['accent'] }} h-100">
                    <div class="apg-kpi-icon"><i class="fas {{ $card['icon'] }}"></i></div>
                    <div class="apg-kpi-body">
                        <div class="apg-kpi-label">{{ $card['label'] }}</div>
                        <div class="apg-kpi-value">{{ number_format($card['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4" id="apgAnalytics">
        <div class="col-12">
            <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ db_trans('group_growth_trend') }}</h5>
                    </div>
                    <span class="apg-inline-badge">{{ $chart['period_label'] ?? db_trans('overview') }}</span>
                </div>
                <div class="card-body p-4">
                    <canvas id="apgGrowthChart" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="mb-1">{{ db_trans('membership_roles_breakdown') }}</h5>
                </div>
                <div class="card-body p-4"><canvas id="apgRoleChart" height="140"></canvas></div>
            </div>
        </div>
    </div>

    <div class="card dashboard-panel apg-panel border-0 shadow-sm" id="groupsTableCard">
        <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ db_trans('apostolic_groups') }}</h5>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @can('apostolic-groups.view')
                    <a href="{{ route('pdf.apostolic-groups.export') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                    </a>

                    <a href="{{ route('apostolic-groups.export.excel') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                    </a>
                @endcan

                <div class="apg-filter-row">
                    <select class="form-select form-select-sm apg-table-filter" data-target="status" style="width: 170px;">
                        <option value="">{{ db_trans('all_statuses') }}</option>
                        <option value="{{ db_trans('active') }}">{{ db_trans('active') }}</option>
                        <option value="{{ db_trans('inactive') }}">{{ db_trans('inactive') }}</option>
                    </select>

                    <select class="form-select form-select-sm apg-table-filter" data-target="rule" style="width: 180px;">
                        <option value="">{{ db_trans('all_rules') }}</option>
                        <option value="{{ db_trans('manual_rule') }}">{{ db_trans('manual_rule') }}</option>
                        <option value="{{ db_trans('gender_rule') }}">{{ db_trans('gender_rule') }}</option>
                        <option value="{{ db_trans('family_role_rule') }}">{{ db_trans('family_role_rule') }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-4 pt-3">
            <div class="table-responsive">
                <table class="table align-middle apg-data-table nowrap" id="apostolicGroupsTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ db_trans('group') }}</th>
                            <th>{{ db_trans('leader') }}</th>
                            <th>{{ db_trans('membership_rule') }}</th>
                            <th>{{ db_trans('meeting') }}</th>
                            <th>{{ db_trans('members') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th class="text-end">{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            @php
                                $ruleLabel = match($group->membership_rule_type) {
                                    'gender' => db_trans('gender_rule'),
                                    'family_role' => db_trans('family_role_rule'),
                                    default => db_trans('manual_rule'),
                                };

                                $meetingDayLabel = match($group->meeting_day) {
                                    'Monday' => db_trans('monday'),
                                    'Tuesday' => db_trans('tuesday'),
                                    'Wednesday' => db_trans('wednesday'),
                                    'Thursday' => db_trans('thursday'),
                                    'Friday' => db_trans('friday'),
                                    'Saturday' => db_trans('saturday'),
                                    'Sunday' => db_trans('sunday'),
                                    default => filled($group->meeting_day) ? $group->meeting_day : null,
                                };

                                $meetingTimeLabel = $group->meeting_time
                                    ? \Carbon\Carbon::parse($group->meeting_time)->format('H:i')
                                    : null;

                                $meetingLabel = trim(
                                    collect([$meetingDayLabel, $meetingTimeLabel])
                                        ->filter()
                                        ->implode(' · ')
                                );
                            @endphp
                            <tr>
                                <td>
                                    <div class="apg-group-cell">
                                        <div class="apg-group-avatar">{{ strtoupper(substr($group->name, 0, 2)) }}</div>
                                        <div>
                                            <a href="{{ route('apostolic-groups.show', $group) }}" class="fw-semibold text-decoration-none">{{ $group->name }}</a>
                                            <div class="small text-muted">{{ $group->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $group->leader?->full_name ?? db_trans('no_leader_assigned') }}</td>
                                <td data-filter="rule"><span class="badge bg-primary-subtle text-primary">{{ $ruleLabel }}</span></td>
                                <td>{{ $meetingLabel !== '' ? $meetingLabel : db_trans('not_set') }}</td>
                                <td>
                                    <div class="small fw-semibold">{{ $group->active_members_count }} {{ db_trans('active') }}</div>
                                    <div class="small text-muted">{{ $group->total_members_count }} {{ db_trans('total') }}</div>
                                </td>
                                <td data-filter="status"><span class="badge {{ $group->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $group->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('apostolic-groups.show', $group) }}" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        @can('apostolic-groups.update')
                                            <button class="btn btn-outline-warning apg-edit-group-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editGroupModal"
                                                    data-action="{{ route('apostolic-groups.update', $group) }}"
                                                    data-name="{{ $group->name }}"
                                                    data-code="{{ $group->code }}"
                                                    data-notes="{{ $group->notes }}"
                                                    data-leader_member_id="{{ $group->leader_member_id }}"
                                                    data-assistant_leader_member_id="{{ $group->assistant_leader_member_id }}"
                                                    data-patron_member_id="{{ $group->patron_member_id }}"
                                                    data-membership_rule_type="{{ $group->membership_rule_type }}"
                                                    data-membership_rule_value="{{ $group->membership_rule_value }}"
                                                    data-founded_on="{{ optional($group->founded_on)->format('Y-m-d') }}"
                                                    data-meeting_day="{{ $group->meeting_day }}"
                                                    data-meeting_time="{{ $group->meeting_time ? \Carbon\Carbon::parse($group->meeting_time)->format('H:i') : '' }}"
                                                    data-meeting_location="{{ $group->meeting_location }}"
                                                    data-is_active="{{ $group->is_active ? 1 : 0 }}"><i class="fas fa-pen"></i></button>
                                        @endcan
                                        @can('apostolic-groups.delete')
                                            <form method="POST" action="{{ route('apostolic-groups.destroy', $group) }}" class="d-inline apg-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
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

@can('apostolic-groups.create')
<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content apg-modal">
            <form method="POST" action="{{ route('apostolic-groups.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_context" value="create_group">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title">{{ db_trans('add_apostolic_group') }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    @include('admin.apostolic-groups.partials.form-fields', [
                        'members' => $availableMembers,
                        'kandas' => $availableKandas,
                        'jumuiyas' => $availableJumuiyas,
                        'familias' => $availableFamilias,
                        'prefix' => 'create_',
                        'oldPrefix' => null
                    ])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn admin-main-btn">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('apostolic-groups.update')
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content apg-modal">
            <form method="POST" action="#" id="editGroupForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit_group">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title">{{ db_trans('edit_apostolic_group') }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    @include('admin.apostolic-groups.partials.form-fields', [
                        'members' => $availableMembers,
                        'kandas' => $availableKandas,
                        'jumuiyas' => $availableJumuiyas,
                        'familias' => $availableFamilias,
                        'prefix' => 'edit_',
                        'group' => null,
                        'oldPrefix' => null
                    ])
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn admin-main-btn">{{ db_trans('update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
(function () {
    const growthLabels = @json($chart['labels'] ?? []);
    const growthData = @json($chart['data'] ?? []);
    const roleLabels = @json($roleChart['labels'] ?? []);
    const roleData = @json($roleChart['data'] ?? []);
    const hasErrors = @json($errors->any());
    const oldContext = @json(old('form_context'));

    const palette = [
        'rgba(124, 58, 237, 0.88)',
        'rgba(59, 130, 246, 0.88)',
        'rgba(16, 185, 129, 0.88)',
        'rgba(245, 158, 11, 0.88)',
        'rgba(244, 63, 94, 0.88)',
        'rgba(20, 184, 166, 0.88)',
        'rgba(139, 92, 246, 0.88)',
        'rgba(234, 88, 12, 0.88)',
    ];

    const borderPalette = [
        'rgba(124, 58, 237, 1)',
        'rgba(59, 130, 246, 1)',
        'rgba(16, 185, 129, 1)',
        'rgba(245, 158, 11, 1)',
        'rgba(244, 63, 94, 1)',
        'rgba(20, 184, 166, 1)',
        'rgba(139, 92, 246, 1)',
        'rgba(234, 88, 12, 1)',
    ];

    const makeChart = (id, type, labels, data) => {
        const el = document.getElementById(id);
        if (!el) return;

        const dataset = {
            data: data,
            borderWidth: 2,
            borderRadius: type === 'bar' ? 10 : 0,
            tension: 0.35
        };

        if (type === 'line') {
            dataset.label = '{{ db_trans('groups') }}';
            dataset.fill = true;
            dataset.backgroundColor = 'rgba(124, 58, 237, 0.16)';
            dataset.borderColor = 'rgba(124, 58, 237, 1)';
            dataset.pointBackgroundColor = 'rgba(124, 58, 237, 1)';
            dataset.pointBorderColor = '#ffffff';
            dataset.pointRadius = 4;
        } else if (type === 'bar') {
            dataset.backgroundColor = palette;
            dataset.borderColor = borderPalette;
        } else {
            dataset.backgroundColor = palette;
            dataset.borderColor = '#ffffff';
        }

        new Chart(el, {
            type,
            data: {
                labels,
                datasets: [dataset]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: type !== 'line',
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            color: '#334155'
                        }
                    }
                },
                scales: type === 'line' || type === 'bar'
                    ? {
                        x: {
                            ticks: { color: '#64748b' },
                            grid: { display: false }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, color: '#64748b' },
                            grid: { color: 'rgba(148, 163, 184, 0.14)' }
                        }
                    }
                    : {}
            }
        });
    };

    makeChart('apgGrowthChart', 'line', growthLabels, growthData);
    makeChart('apgRoleChart', 'doughnut', roleLabels, roleData);

    const table = $('#apostolicGroupsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: '',
            searchPlaceholder: '{{ db_trans("search") }}...'
        }
    });

    document.querySelectorAll('.apg-table-filter').forEach(select => {
        select.addEventListener('change', function () {
            const colIndex = this.dataset.target === 'rule' ? 2 : 5;
            table.column(colIndex).search(this.value).draw();
        });
    });

    document.querySelectorAll('.apg-rule-type').forEach(select => {
        const syncRuleWrap = () => {
            const wrap = document.getElementById(select.dataset.prefix + 'ruleValueWrap');
            if (!wrap) return;
            wrap.style.display = select.value === 'manual' ? 'none' : '';
        };
        select.addEventListener('change', syncRuleWrap);
        syncRuleWrap();
    });

    const setupRoleMemberPickers = () => {
        document.querySelectorAll('.apg-member-picker').forEach((picker) => {
            const select = picker.querySelector('.apg-member-select');
            const panel = picker.querySelector('.apg-member-filter-panel');
            const kandaFilter = picker.querySelector('.apg-role-kanda-filter');
            const jumuiyaFilter = picker.querySelector('.apg-role-jumuiya-filter');
            const familiaFilter = picker.querySelector('.apg-role-familia-filter');
            const searchFilter = picker.querySelector('.apg-role-search-filter');
            const closeBtn = picker.querySelector('.apg-hide-member-filter');

            if (!select || !panel || !kandaFilter || !jumuiyaFilter || !familiaFilter || !searchFilter) return;

            const showPanel = () => {
                document.querySelectorAll('.apg-member-filter-panel').forEach((otherPanel) => {
                    if (otherPanel !== panel) {
                        otherPanel.classList.add('d-none');
                    }
                });

                panel.classList.remove('d-none');
            };

            const syncJumuiyas = () => {
                const selectedKanda = kandaFilter.value;

                Array.from(jumuiyaFilter.options).forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    option.hidden = Boolean(selectedKanda && option.dataset.kandaId !== selectedKanda);
                });

                if (jumuiyaFilter.selectedOptions[0]?.hidden) {
                    jumuiyaFilter.value = '';
                }
            };

            const syncFamilias = () => {
                const selectedKanda = kandaFilter.value;
                const selectedJumuiya = jumuiyaFilter.value;

                Array.from(familiaFilter.options).forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    const matchesKanda = !selectedKanda || option.dataset.kandaId === selectedKanda;
                    const matchesJumuiya = !selectedJumuiya || option.dataset.jumuiyaId === selectedJumuiya;

                    option.hidden = !(matchesKanda && matchesJumuiya);
                });

                if (familiaFilter.selectedOptions[0]?.hidden) {
                    familiaFilter.value = '';
                }
            };

            const syncMembers = () => {
                const selectedKanda = kandaFilter.value;
                const selectedJumuiya = jumuiyaFilter.value;
                const selectedFamilia = familiaFilter.value;
                const search = searchFilter.value.toLowerCase().trim();

                Array.from(select.options).forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    const matchesKanda = !selectedKanda || option.dataset.kandaId === selectedKanda;
                    const matchesJumuiya = !selectedJumuiya || option.dataset.jumuiyaId === selectedJumuiya;
                    const matchesFamilia = !selectedFamilia || option.dataset.familiaId === selectedFamilia;
                    const matchesSearch = !search || (option.dataset.search || '').includes(search);

                    option.hidden = !(matchesKanda && matchesJumuiya && matchesFamilia && matchesSearch);
                });

                if (select.selectedOptions[0]?.hidden) {
                    select.value = '';
                }
            };

            const syncAll = () => {
                syncJumuiyas();
                syncFamilias();
                syncMembers();
            };

            select.addEventListener('focus', showPanel);
            select.addEventListener('click', showPanel);
            select.addEventListener('mousedown', showPanel);

            kandaFilter.addEventListener('change', syncAll);

            jumuiyaFilter.addEventListener('change', () => {
                syncFamilias();
                syncMembers();
            });

            familiaFilter.addEventListener('change', syncMembers);
            searchFilter.addEventListener('input', syncMembers);

            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    panel.classList.add('d-none');
                });
            }

            syncAll();
        });
    };

    setupRoleMemberPickers();

    document.querySelectorAll('.apg-edit-group-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = document.getElementById('editGroupForm');
            form.action = this.dataset.action;

            [
                'name','code','notes','leader_member_id','assistant_leader_member_id',
                'patron_member_id','membership_rule_type','membership_rule_value',
                'founded_on','meeting_day','meeting_time','meeting_location'
            ].forEach(field => {
                const input = document.getElementById('edit_' + field);
                if (input) input.value = this.dataset[field] ?? '';
            });

            const active = document.getElementById('edit_is_active');
            if (active) active.checked = this.dataset.is_active === '1';

            document.getElementById('edit_membership_rule_type')?.dispatchEvent(new Event('change'));
        });
    });

    document.querySelectorAll('.apg-delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: @json(db_trans('are_you_sure')),
                text: @json(db_trans('this_action_cannot_be_undone')),
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#7c3aed',
                confirmButtonText: @json(db_trans('delete')),
                cancelButtonText: @json(db_trans('cancel'))
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    if (hasErrors) {
        const targetId = oldContext === 'edit_group' ? 'editGroupModal' : 'createGroupModal';
        const target = document.getElementById(targetId);
        if (target) bootstrap.Modal.getOrCreateInstance(target).show();
    }
})();
</script>
@endpush