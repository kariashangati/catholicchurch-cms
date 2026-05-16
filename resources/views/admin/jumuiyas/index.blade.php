@extends('layouts.admin')

@section('title', db_trans('jumuiyas'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/jumuiya-module-v3.css') }}">
@endpush

@section('content')
    <div class="admin-ui-v3 jumuiya-module-v3">
    <div class="ui-page-hero jumuiya-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="ui-page-hero-badge">{{ db_trans('overview') }}</span>
                <h1 class="ui-page-hero-title">{{ db_trans('jumuiyas') }}</h1>

                <div class="ui-page-hero-pills mt-3">
                    <span class="ui-hero-pill">
                        <i class="fas fa-layer-group"></i>
                        {{ number_format($stats['total_jumuiyas']) }} {{ db_trans('jumuiyas') }}
                    </span>
                    <span class="ui-hero-pill">
                        <i class="fas fa-users"></i>
                        {{ number_format($stats['total_members']) }} {{ db_trans('members') }}
                    </span>
                    <span class="ui-hero-pill">
                        <i class="fas fa-home"></i>
                        {{ number_format($stats['total_familias']) }} {{ db_trans('familias') }}
                    </span>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="hero-actions-grid justify-content-lg-end">
                    @can('jumuiyas.create')
                        <button type="button" class="btn btn-light btn-lg ui-hero-action-btn jumuiya-hero-action" data-bs-toggle="modal" data-bs-target="#createJumuiyaModal">
                            <i class="fas fa-plus-circle me-2"></i>{{ db_trans('create_jumuiya') }}
                        </button>
                    @endcan

                    @can('jumuiyas.view')
                        <a href="{{ route('pdf.jumuiyas.export') }}" class="btn btn-danger btn-lg ui-hero-action-btn">
                            <i class="fas fa-file-pdf me-2"></i>{{ db_trans('export_pdf') }}
                        </a>

                        <a href="{{ route('jumuiyas.export.excel') }}" class="btn btn-success btn-lg ui-hero-action-btn">
                            <i class="fas fa-file-excel me-2"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endcan

                    @can('kandas.view')
                        <a href="{{ route('kandas.index') }}" class="btn btn-outline-primary btn-lg ui-hero-action-btn">
                            <i class="fas fa-layer-group me-2"></i>{{ db_trans('kandas') }}
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-stat-card--primary h-100">
                <div class="ui-stat-card__icon"><i class="fas fa-layer-group"></i></div>
                <div class="ui-stat-card__body">
                    <div class="ui-stat-card__label">{{ db_trans('total_jumuiyas') }}</div>
                    <div class="ui-stat-card__value">{{ number_format($stats['total_jumuiyas']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-stat-card--success h-100">
                <div class="ui-stat-card__icon"><i class="fas fa-check-circle"></i></div>
                <div class="ui-stat-card__body">
                    <div class="ui-stat-card__label">{{ db_trans('active_jumuiyas') }}</div>
                    <div class="ui-stat-card__value">{{ number_format($stats['active_jumuiyas']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-stat-card--info h-100">
                <div class="ui-stat-card__icon"><i class="fas fa-home"></i></div>
                <div class="ui-stat-card__body">
                    <div class="ui-stat-card__label">{{ db_trans('total_familias') }}</div>
                    <div class="ui-stat-card__value">{{ number_format($stats['total_familias']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="ui-stat-card ui-stat-card--warning h-100">
                <div class="ui-stat-card__icon"><i class="fas fa-users"></i></div>
                <div class="ui-stat-card__body">
                    <div class="ui-stat-card__label">{{ db_trans('total_members') }}</div>
                    <div class="ui-stat-card__value">{{ number_format($stats['total_members']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card dashboard-panel h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">{{ db_trans('members_distribution') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="chart-shell">
                        <canvas id="jumuiyaMembersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card dashboard-panel h-100">
                <div class="card-header bg-transparent border-0 p-4 pb-0">
                    <h5 class="fw-bold text-dark mb-1">{{ db_trans('families_count') }}</h5>
                </div>
                <div class="card-body p-4">
                    <div class="chart-shell">
                        <canvas id="jumuiyaFamiliesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card dashboard-panel">
        <div class="card-header bg-transparent border-0 p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ db_trans('jumuiyas') }}</h5>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    @can('jumuiyas.view')
                        <a href="{{ route('pdf.jumuiyas.export') }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>

                        <a href="{{ route('jumuiyas.export.excel') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endcan

                    <input type="search" class="form-control form-control-sm admin-table-search" data-table-target="#jumuiyaIndexTable" placeholder="{{ db_trans('search') }}">

                    <select class="form-select form-select-sm admin-table-length" data-table-target="#jumuiyaIndexTable">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0 admin-data-table" id="jumuiyaIndexTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('kanda') }}</th>
                            <th>{{ db_trans('members') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('created_at') }}</th>
                            <th class="text-end">{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jumuiyas as $jumuiya)
                            <tr>
                                <td>
                                    <div class="jumuiya-name-wrap">
                                        @if($jumuiya->image)
                                            <img src="{{ asset('storage/' . $jumuiya->image) }}" alt="{{ $jumuiya->name }}" class="jumuiya-thumb">
                                        @else
                                            <div class="jumuiya-thumb-placeholder">
                                                {{ strtoupper(substr($jumuiya->name, 0, 1)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <div class="fw-bold">{{ $jumuiya->name }}</div>
                                            <div class="small text-muted">{{ $jumuiya->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $jumuiya->kanda?->name ?? '—' }}</td>
                                <td>{{ number_format($jumuiya->members_count) }}</td>
                                <td>
                                    @if($jumuiya->is_active)
                                        <span class="kanda-status-badge active">
                                            <i class="fas fa-check-circle"></i>{{ db_trans('active') }}
                                        </span>
                                    @else
                                        <span class="kanda-status-badge inactive">
                                            <i class="fas fa-minus-circle"></i>{{ db_trans('inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ optional($jumuiya->created_at)->format('M d, Y') }}</td>
                                <td class="text-end">
                                    <div class="kanda-action-group justify-content-end">
                                        <a href="{{ route('jumuiyas.show', $jumuiya) }}" class="btn btn-sm btn-outline-primary">
                                            {{ db_trans('view') }}
                                        </a>

                                        @can('jumuiyas.members.view')
                                            <a href="{{ route('jumuiyas.members.index', $jumuiya) }}" class="btn btn-sm btn-outline-info">
                                                {{ db_trans('members') }}
                                            </a>
                                        @endcan

                                        @can('jumuiya-reports.view')
                                            <a href="{{ route('jumuiya-reports.show', $jumuiya) }}" class="btn btn-sm btn-outline-success">
                                                {{ db_trans('view_report') }}
                                            </a>
                                        @endcan

                                        @can('jumuiyas.update')
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-warning js-edit-jumuiya"
                                                data-url="{{ route('jumuiyas.edit', $jumuiya) }}"
                                                data-update-url="{{ route('jumuiyas.update', $jumuiya) }}"
                                            >
                                                {{ db_trans('edit') }}
                                            </button>
                                        @endcan

                                        @can('jumuiyas.delete')
                                            <form action="{{ route('jumuiyas.destroy', $jumuiya) }}" method="POST" class="js-confirm-delete d-inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    {{ db_trans('delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    {{ db_trans('create_new_jumuiya_to_get_started') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-transparent border-0 p-4">
            <div class="admin-table-pagination" data-table-target="#jumuiyaIndexTable"></div>
        </div>
    </div>

    @can('jumuiyas.create')
        <div class="modal fade" id="createJumuiyaModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header border-0 p-4 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">{{ db_trans('create_jumuiya') }}</h5>
                            <div class="text-muted small">{{ db_trans('add_new_jumuiya_under_selected_kanda') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ route('jumuiyas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body px-4 pb-2">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('jumuiya') }}</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('kanda') }}</label>
                                    <select name="kanda_id" class="form-select" required>
                                        <option value="">{{ db_trans('select_kanda') }}</option>
                                        @foreach($kandas as $kanda)
                                            <option value="{{ $kanda->id }}" @selected(old('kanda_id') == $kanda->id)>{{ $kanda->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ db_trans('comment') }}</label>
                                    <textarea name="comment" rows="4" class="form-control">{{ old('comment') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('upload_image') }}</label>
                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_is_active" checked>
                                        <label class="form-check-label" for="create_is_active">{{ db_trans('active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 px-4 pb-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('back') }}</button>
                            <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('jumuiyas.update')
        <div class="modal fade" id="editJumuiyaModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header border-0 p-4 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-1">{{ db_trans('edit_jumuiya') }}</h5>
                            <div class="text-muted small">{{ db_trans('update_jumuiya_profile_and_status') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="editJumuiyaForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="modal-body px-4 pb-2">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('jumuiya') }}</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('kanda') }}</label>
                                    <select name="kanda_id" class="form-select" required>
                                        <option value="">{{ db_trans('select_kanda') }}</option>
                                        @foreach($kandas as $kanda)
                                            <option value="{{ $kanda->id }}">{{ $kanda->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ db_trans('comment') }}</label>
                                    <textarea name="comment" rows="4" class="form-control"></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">{{ db_trans('upload_image') }}</label>
                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                </div>

                                <div class="col-md-6">
                                    <div id="editJumuiyaImagePreviewWrap" class="jumuiya-modal-preview-wrap">
                                        <label class="form-label fw-semibold">{{ db_trans('community_image') }}</label>
                                        <div>
                                            <img id="editJumuiyaImagePreview" src="" alt="Preview" class="jumuiya-modal-preview">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1">
                                        <label class="form-check-label">{{ db_trans('active') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 px-4 pb-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('back') }}</button>
                            <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sharedTicks = { color: '#6b7280', font: { size: 11 } };
    const sharedGrid = { color: 'rgba(148, 163, 184, 0.14)' };

    const membersCtx = document.getElementById('jumuiyaMembersChart');
    if (membersCtx) {
        new Chart(membersCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: '{{ db_trans('members') }}',
                        data: @json($chartData['members']),
                        backgroundColor: 'rgba(124, 58, 237, 0.82)',
                        borderColor: 'rgba(109, 40, 217, 1)',
                        borderWidth: 1.5,
                        borderRadius: 10,
                        maxBarThickness: 34
                    },
                    {
                        label: '{{ db_trans('familias') }}',
                        data: @json($chartData['familias']),
                        backgroundColor: 'rgba(16, 185, 129, 0.72)',
                        borderColor: 'rgba(5, 150, 105, 1)',
                        borderWidth: 1.5,
                        borderRadius: 10,
                        maxBarThickness: 34
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 10, color: '#334155' }
                    }
                },
                scales: {
                    x: { ticks: sharedTicks, grid: { display: false } },
                    y: { beginAtZero: true, ticks: sharedTicks, grid: sharedGrid }
                }
            }
        });
    }

    const familiesCtx = document.getElementById('jumuiyaFamiliesChart');
    if (familiesCtx) {
        new Chart(familiesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    data: @json($chartData['familias']),
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.90)',
                        'rgba(59, 130, 246, 0.90)',
                        'rgba(16, 185, 129, 0.90)',
                        'rgba(245, 158, 11, 0.90)',
                        'rgba(236, 72, 153, 0.90)',
                        'rgba(14, 165, 233, 0.90)',
                        'rgba(99, 102, 241, 0.90)',
                        'rgba(239, 68, 68, 0.90)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 10, color: '#334155' }
                    }
                },
                cutout: '62%'
            }
        });
    }
});
</script>
@endpush