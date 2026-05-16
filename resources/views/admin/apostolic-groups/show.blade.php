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
    $ruleLabel = match($group->membership_rule_type) {
        'gender' => db_trans('gender_rule'),
        'family_role' => db_trans('family_role_rule'),
        default => db_trans('manual_rule'),
    };

    $meetingDayLabel = filled($group->meeting_day)
        ? db_trans(\Illuminate\Support\Str::lower($group->meeting_day))
        : null;

    $meetingTimeLabel = $group->meeting_time
        ? \Carbon\Carbon::parse($group->meeting_time)->format('H:i')
        : null;

    $meetingLabel = trim(collect([$meetingDayLabel, $meetingTimeLabel])->filter()->implode(' · '));

    $summaryCards = [
        ['label' => db_trans('total_members'), 'value' => $stats['total_members'], 'icon' => 'fa-users'],
        ['label' => db_trans('active_members'), 'value' => $stats['active_members'], 'icon' => 'fa-user-check'],
        ['label' => db_trans('familias_represented'), 'value' => $stats['familias_represented'], 'icon' => 'fa-house-user'],
        ['label' => db_trans('jumuiyas_represented'), 'value' => $stats['jumuiyas_represented'], 'icon' => 'fa-church'],
    ];
@endphp
<div class="apg-dashboard-page">
    <section class="dashboard-hero apg-hero mb-4 apg-show-hero">
        <div class="row g-4 align-items-center">
            <div class="col-xl-8">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="dashboard-hero-badge">{{ db_trans('apostolic_group_profile') }}</span>
                    <span class="apg-hero-pill {{ $group->is_active ? 'success' : 'muted' }}">{{ $group->is_active ? db_trans('active') : db_trans('inactive') }}</span>
                    <span class="apg-hero-pill">{{ $ruleLabel }}</span>
                </div>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="apg-show-avatar">{{ strtoupper(substr($group->name, 0, 2)) }}</div>
                    <div>
                        <h1 class="dashboard-title mb-2">{{ $group->name }}</h1>
                        <p class="dashboard-subtitle mb-0">{{ $group->code }} · {{ optional($group->founded_on)->format('d M Y') ?: '—' }}</p>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    @foreach($summaryCards as $card)
                        <div class="col-sm-6 col-xl-3">
                            <div class="apg-mini-stat">
                                <i class="fas {{ $card['icon'] }}"></i>
                                <div>
                                    <small>{{ $card['label'] }}</small>
                                    <strong>{{ number_format($card['value']) }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-xl-4">
                <div class="apg-action-grid two-col">
                    @can('apostolic-groups.members.create')
                        <button class="apg-quick-action" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                            <span class="action-icon"><i class="fas fa-user-plus"></i></span>
                            <span><strong>{{ db_trans('add_members') }}</strong></span>
                        </button>
                    @endcan

                    @can('apostolic-groups.update')
                        <button class="apg-quick-action" data-bs-toggle="modal" data-bs-target="#editGroupModal">
                            <span class="action-icon"><i class="fas fa-pen"></i></span>
                            <span><strong>{{ db_trans('edit_group') }}</strong></span>
                        </button>
                    @endcan

                    @if(in_array($group->membership_rule_type, ['gender','family_role']))
                        <form method="POST" action="{{ route('apostolic-groups.sync-rule-members', $group) }}" class="apg-sync-form">
                            @csrf
                            <button type="submit" class="apg-quick-action w-100 border-0 text-start">
                                <span class="action-icon"><i class="fas fa-arrows-rotate"></i></span>
                                <span><strong>{{ db_trans('sync_rule_members') }}</strong></span>
                            </button>
                        </form>
                    @endif

                    @can('apostolic-groups.view')
                        <a href="{{ route('pdf.apostolic-groups.members.pdf', $group) }}" class="apg-quick-action">
                            <span class="action-icon"><i class="fas fa-file-pdf"></i></span>
                            <span><strong>{{ db_trans('export_pdf') }}</strong></span>
                        </a>

                        <a href="{{ route('apostolic-groups.members.excel', $group) }}" class="apg-quick-action">
                            <span class="action-icon"><i class="fas fa-file-excel"></i></span>
                            <span><strong>{{ db_trans('export_excel') }}</strong></span>
                        </a>
                    @endcan

                    <a href="{{ route('apostolic-groups.index') }}" class="apg-quick-action">
                        <span class="action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span><strong>{{ db_trans('back') }}</strong></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="apg-section-title mb-3">
                        <h5 class="mb-1">{{ db_trans('leadership_and_configuration') }}</h5>
                    </div>
                    <div class="apg-detail-list">
                        <div class="apg-detail-row"><span>{{ db_trans('group_leader') }}</span><strong>{{ $group->leader?->full_name ?? db_trans('no_leader_assigned') }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('assistant_leader') }}</span><strong>{{ $group->assistantLeader?->full_name ?? '—' }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('group_patron') }}</span><strong>{{ $group->patron?->full_name ?? '—' }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('membership_rule') }}</span><strong>{{ $ruleLabel }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('rule_value') }}</span><strong>{{ $group->membership_rule_value ?: '—' }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('meeting_day') }}</span><strong>{{ $meetingDayLabel ?: '—' }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('meeting_time') }}</span><strong>{{ $group->meeting_time ? \Carbon\Carbon::parse($group->meeting_time)->format('H:i') : '—' }}</strong></div>
                        <div class="apg-detail-row"><span>{{ db_trans('meeting_location') }}</span><strong>{{ $group->meeting_location ?: '—' }}</strong></div>
                    </div>
                    <div class="apg-progress-card mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">{{ db_trans('group_profile_completion') }}</span>
                            <span class="text-primary fw-bold">{{ $stats['profile_readiness'] }}%</span>
                        </div>
                        <div class="progress apg-progress"><div class="progress-bar" style="width: {{ $stats['profile_readiness'] }}%"></div></div>
                    </div>
                    @if($group->notes)
                        <div class="apg-note mt-4">{{ $group->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="row g-4 h-100">
                <div class="col-md-6">
                    <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0"><h5 class="mb-1">{{ db_trans('membership_status_breakdown') }}</h5></div>
                        <div class="card-body p-4"><canvas id="apgStatusChart" height="180"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0"><h5 class="mb-1">{{ db_trans('gender_distribution') }}</h5></div>
                        <div class="card-body p-4"><canvas id="apgGenderChart" height="180"></canvas></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card dashboard-panel apg-panel border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-0 p-4 pb-0"><h5 class="mb-1">{{ db_trans('role_distribution') }}</h5></div>
                        <div class="card-body p-4"><canvas id="apgRoleChartShow" height="120"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card dashboard-panel apg-panel border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 p-4 pb-0 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div>
                <h5 class="mb-1">{{ db_trans('group_members') }}</h5>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @can('apostolic-groups.members.create')
                    <button class="btn admin-main-btn btn-sm" data-bs-toggle="modal" data-bs-target="#addMembersModal">
                        <i class="fas fa-user-plus me-2"></i>{{ db_trans('add_members') }}
                    </button>
                @endcan

                @can('apostolic-groups.view')
                    <a href="{{ route('pdf.apostolic-groups.members.pdf', $group) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-2"></i>{{ db_trans('export_pdf') }}
                    </a>

                    <a href="{{ route('apostolic-groups.members.excel', $group) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-2"></i>{{ db_trans('export_excel') }}
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body p-4 pt-3">
            <div class="table-responsive">
                <table class="table align-middle apg-data-table nowrap" id="groupMembersTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('familia') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('role') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('joined') }}</th>
                            <th class="text-end">{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($memberships as $membership)
                            <tr>
                                <td>
                                    <div class="apg-group-cell">
                                        <div class="apg-group-avatar small">{{ strtoupper(substr($membership->member?->full_name ?? 'M', 0, 2)) }}</div>
                                        <div>
                                            <div class="fw-semibold">{{ $membership->member?->full_name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $membership->member?->phone ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $membership->member?->familia?->name ?? '—' }}</td>
                                <td>{{ $membership->member?->familia?->jumuiya?->name ?? '—' }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ db_trans($membership->role) }}</span></td>
                                <td><span class="badge {{ $membership->status === 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $membership->status === 'active' ? db_trans('active') : db_trans('inactive') }}</span></td>
                                <td>{{ optional($membership->joined_at)->format('d M Y') ?: '—' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('apostolic-groups.members.update')
                                        <button class="btn btn-outline-warning membership-edit-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editMembershipModal"
                                                data-action="{{ route('apostolic-groups.members.update', [$group, $membership]) }}"
                                                data-role="{{ $membership->role }}"
                                                data-status="{{ $membership->status }}"
                                                data-joined_at="{{ optional($membership->joined_at)->format('Y-m-d') }}"
                                                data-left_at="{{ optional($membership->left_at)->format('Y-m-d') }}"
                                                data-notes="{{ $membership->notes }}"><i class="fas fa-pen"></i></button>
                                        @endcan
                                        @can('apostolic-groups.members.delete')
                                        <form method="POST" action="{{ route('apostolic-groups.members.destroy', [$group, $membership]) }}" class="d-inline apg-remove-member-form">
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

<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content apg-modal">
            <form method="POST" action="{{ route('apostolic-groups.update', $group) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit_group">
                <div class="modal-header border-0 pb-0"><h5 class="modal-title">{{ db_trans('edit_apostolic_group') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">@include('admin.apostolic-groups.partials.form-fields', ['members' => $availableMembers, 'group' => $group, 'prefix' => 'edit_', 'oldPrefix' => null])</div>
                <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="submit" class="btn admin-main-btn">{{ db_trans('update') }}</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addMembersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content apg-modal">
            <form method="POST" action="{{ route('apostolic-groups.members.store', $group) }}">
                @csrf
                <input type="hidden" name="form_context" value="add_members">
                <div class="modal-header border-0 pb-0"><h5 class="modal-title">{{ db_trans('add_members') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label">{{ db_trans('select_members') }}</label>
                        <select class="form-select" name="member_ids[]" multiple size="10">
                            @foreach($availableMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->full_name }} — {{ $member->familia?->jumuiya?->name ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('role') }}</label>
                            <select class="form-select" name="role">
                                @foreach(['member','leader','assistant_leader','secretary','treasurer','patron'] as $role)
                                    <option value="{{ $role }}">{{ db_trans($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select class="form-select" name="status">
                                <option value="active">{{ db_trans('active') }}</option>
                                <option value="inactive">{{ db_trans('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('joined_at') }}</label>
                            <input type="date" class="form-control" name="joined_at" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="submit" class="btn admin-main-btn">{{ db_trans('save') }}</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editMembershipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content apg-modal">
            <form method="POST" action="#" id="editMembershipForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="edit_membership">
                <div class="modal-header border-0 pb-0"><h5 class="modal-title">{{ db_trans('edit_membership') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body pt-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('role') }}</label>
                            <select class="form-select" name="role" id="membership_role">
                                @foreach(['member','leader','assistant_leader','secretary','treasurer','patron'] as $role)
                                    <option value="{{ $role }}">{{ db_trans($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select class="form-select" name="status" id="membership_status">
                                <option value="active">{{ db_trans('active') }}</option>
                                <option value="inactive">{{ db_trans('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('joined_at') }}</label><input type="date" class="form-control" name="joined_at" id="membership_joined_at"></div>
                        <div class="col-md-6"><label class="form-label">{{ db_trans('left_at') }}</label><input type="date" class="form-control" name="left_at" id="membership_left_at"></div>
                        <div class="col-12"><label class="form-label">{{ db_trans('notes') }}</label><textarea class="form-control" name="notes" rows="3" id="membership_notes"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button><button type="submit" class="btn admin-main-btn">{{ db_trans('update') }}</button></div>
            </form>
        </div>
    </div>
</div>
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
    const statusLabels = @json($statusChart['labels'] ?? []);
    const statusData = @json($statusChart['data'] ?? []);
    const genderLabels = @json($memberChart['labels'] ?? []);
    const genderData = @json($memberChart['data'] ?? []);
    const roleLabels = @json($roleChart['labels'] ?? []);
    const roleData = @json($roleChart['data'] ?? []);
    const hasErrors = @json($errors->any());
    const oldContext = @json(old('form_context'));

    const makeChart = (id, type, labels, data) => {
        const el = document.getElementById(id);
        if (!el) return;

        const palette = [
            'rgba(124,58,237,0.88)',
            'rgba(59,130,246,0.88)',
            'rgba(16,185,129,0.88)',
            'rgba(245,158,11,0.88)',
            'rgba(244,63,94,0.88)',
            'rgba(14,165,233,0.88)',
            'rgba(139,92,246,0.88)',
            'rgba(34,197,94,0.88)',
        ];

        const borderPalette = [
            'rgba(124,58,237,1)',
            'rgba(59,130,246,1)',
            'rgba(16,185,129,1)',
            'rgba(245,158,11,1)',
            'rgba(244,63,94,1)',
            'rgba(14,165,233,1)',
            'rgba(139,92,246,1)',
            'rgba(34,197,94,1)',
        ];

        new Chart(el, {
            type,
            data: {
                labels,
                datasets: [{
                    data,
                    label: '{{ db_trans('members') }}',
                    backgroundColor: type === 'bar' ? 'rgba(124,58,237,0.82)' : palette,
                    borderColor: type === 'bar' ? 'rgba(109,40,217,1)' : borderPalette,
                    borderWidth: 2,
                    borderRadius: type === 'bar' ? 10 : 0,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: type !== 'bar',
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            color: '#334155'
                        }
                    }
                },
                scales: type === 'bar' ? {
                    x: {
                        ticks: { color: '#64748b' },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#64748b' },
                        grid: { color: 'rgba(148,163,184,0.15)' }
                    }
                } : {}
            }
        });
    };

    makeChart('apgStatusChart', 'doughnut', statusLabels, statusData);
    makeChart('apgGenderChart', 'pie', genderLabels, genderData);
    makeChart('apgRoleChartShow', 'bar', roleLabels, roleData);

    $('#groupMembersTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[0, 'asc']],
        language: { search: '', searchPlaceholder: '{{ db_trans('search') }}...' }
    });

    document.querySelectorAll('.membership-edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = document.getElementById('editMembershipForm');
            form.action = this.dataset.action;
            document.getElementById('membership_role').value = this.dataset.role || 'member';
            document.getElementById('membership_status').value = this.dataset.status || 'active';
            document.getElementById('membership_joined_at').value = this.dataset.joined_at || '';
            document.getElementById('membership_left_at').value = this.dataset.left_at || '';
            document.getElementById('membership_notes').value = this.dataset.notes || '';
        });
    });

    document.querySelectorAll('.apg-remove-member-form, .apg-sync-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const isSync = form.classList.contains('apg-sync-form');
            Swal.fire({
                icon: isSync ? 'question' : 'warning',
                title: isSync ? @json(db_trans('sync_rule_members_confirmation')) : @json(db_trans('remove_member_confirmation')),
                text: isSync ? @json(db_trans('this_will_add_any_matching_members_not_already_in_the_group')) : @json(db_trans('this_action_cannot_be_undone')),
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: isSync ? @json(db_trans('continue')) : @json(db_trans('remove')),
                cancelButtonText: @json(db_trans('cancel'))
            }).then(result => { if (result.isConfirmed) form.submit(); });
        });
    });

    if (hasErrors) {
        const map = {
            edit_group: 'editGroupModal',
            add_members: 'addMembersModal',
            edit_membership: 'editMembershipModal'
        };
        const target = document.getElementById(map[oldContext] || 'addMembersModal');
        if (target) bootstrap.Modal.getOrCreateInstance(target).show();
    }
})();
</script>
@endpush