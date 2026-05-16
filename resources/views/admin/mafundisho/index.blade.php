@extends('layouts.admin')

@section('title', db_trans('teaching_students'))

@section('content')
@php
    $statusFilter = request('status', $status);
    $monthFilter = request('month', $month ?? null);
    $currentTeachingType = $teachingType ?? $teachingTypes->firstWhere('slug', $type);

    $activeCount = $stats['continuing'] ?? $stats['active'] ?? 0;
    $completedCount = $stats['completed'] ?? 0;
    $failedCount = $stats['failed'] ?? 0;
    $repeatedCount = $stats['repeated'] ?? 0;
    $withdrawnCount = $stats['withdrawn'] ?? 0;

    $exportQuery = collect([
        'month' => $monthFilter ?: null,
        'status' => $statusFilter ?: null,
    ])->filter()->all();

    $pdfExportUrl = \Illuminate\Support\Facades\Route::has('pdf.mafundisho.type')
        ? route('pdf.mafundisho.type', [$type, $year]) . ($exportQuery ? '?' . http_build_query($exportQuery) : '')
        : null;

    $excelExportUrl = \Illuminate\Support\Facades\Route::has('excel.mafundisho.type')
        ? route('excel.mafundisho.type', [$type, $year]) . ($exportQuery ? '?' . http_build_query($exportQuery) : '')
        : null;
@endphp

<div class="teaching-page">

    {{-- HERO --}}
    <div class="dashboard-hero teaching-hero mb-4">
        <div class="row align-items-center g-4">
            <div class="col-xl-7">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="dashboard-hero-badge">{{ db_trans('teaching_student_management') }}</span>
                    <span class="teaching-chip">{{ $typeLabel }}</span>
                    <span class="teaching-chip soft">{{ $year }}</span>

                    @if(!empty($monthFilter))
                        <span class="teaching-chip soft">
                            {{ \Carbon\Carbon::create(null, (int) $monthFilter, 1)->translatedFormat('F') }}
                        </span>
                    @endif
                </div>

                <h1 class="dashboard-title mb-0">{{ $typeLabel }} · {{ $year }}</h1>
            </div>

            <div class="col-xl-5">
                <div class="teaching-quick-grid single-column">
                    <div class="teaching-action-card">
                        <div class="w-100">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">{{ db_trans('teaching_type') }}</label>
                                    <select id="teachingTypeRedirect" class="form-select form-select-sm">
                                        @foreach($teachingTypes as $item)
                                            <option value="{{ $item->slug }}" @selected($type === $item->slug)>
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">{{ db_trans('year') }}</label>
                                    <select id="teachingYearRedirect" class="form-select form-select-sm">
                                        @foreach($availableYears as $availableYear)
                                            <option value="{{ $availableYear }}" @selected((int) $year === (int) $availableYear)>
                                                {{ $availableYear }}
                                            </option>
                                        @endforeach

                                        @if(! $availableYears->contains((int) now()->year))
                                            <option value="{{ now()->year }}" @selected((int) $year === (int) now()->year)>
                                                {{ now()->year }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small mb-1">{{ db_trans('month') }}</label>
                                    <select id="teachingMonthRedirect" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_months') }}</option>
                                        @foreach(range(1, 12) as $monthNumber)
                                            <option value="{{ $monthNumber }}" @selected((string)($monthFilter ?? '') === (string)$monthNumber)>
                                                {{ \Carbon\Carbon::create(null, $monthNumber, 1)->translatedFormat('M') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small mb-1">{{ db_trans('status') }}</label>
                                    <select id="teachingStatusRedirect" class="form-select form-select-sm">
                                        <option value="">{{ db_trans('all_statuses') }}</option>
                                        @foreach($statusLabels as $statusKey => $statusLabel)
                                            @continue($statusKey === \App\Models\MafundishoEnrollment::STATUS_ACTIVE)
                                            <option value="{{ $statusKey }}" @selected($statusFilter === $statusKey)>
                                                {{ $statusLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="teachingApplyFilters" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter me-1"></i>{{ db_trans('apply_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @can('mafundisho.create')
                        <button type="button"
                                class="teaching-action-card border-0"
                                data-bs-toggle="modal"
                                data-bs-target="#createEnrollmentModal">
                            <span class="teaching-action-icon"><i class="fas fa-user-plus"></i></span>
                            <span>
                                <strong>{{ db_trans('add_student') }}</strong>
                            </span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small fw-bold">{{ db_trans('total_students') }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small fw-bold">{{ db_trans('continuing_students') }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($activeCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small fw-bold">{{ db_trans('graduated_students') }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($completedCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="text-uppercase text-muted small fw-bold">{{ db_trans('eligible_members') }}</div>
                    <div class="fs-2 fw-bold">{{ number_format($stats['eligible'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- STATUS CARDS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="fw-semibold">{{ db_trans('continuing_students') }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($activeCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="fw-semibold">{{ db_trans('graduated_students') }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($completedCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="fw-semibold">{{ db_trans('failed_students') }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($failedCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="fw-semibold">{{ db_trans('repeating_students') }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($repeatedCount) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body">
                    <div class="fw-semibold">{{ db_trans('withdrawn_students') }}</div>
                    <div class="fs-3 fw-bold">{{ number_format($withdrawnCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body p-4">
                    <h5 class="mb-3">{{ db_trans('enrollment_trend') }}</h5>
                    <div style="height: 280px;">
                        <canvas id="teachingMonthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card dashboard-panel border-0 h-100">
                <div class="card-body p-4">
                    <h5 class="mb-3">{{ db_trans('registration_status_breakdown') }}</h5>
                    <div style="height: 280px;">
                        <canvas id="teachingStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card dashboard-panel">
        <div class="card-header bg-transparent border-0 p-4 pb-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="mb-0 fw-bold">{{ db_trans('students') }}</h5>

                <div class="d-flex flex-wrap gap-2">
                    @if($pdfExportUrl)
                        <a href="{{ $pdfExportUrl }}" class="btn btn-sm btn-danger rounded-pill px-3">
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if($excelExportUrl)
                        <a href="{{ $excelExportUrl }}" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="mafundishoEnrollmentsTable">
                    <thead>
                        <tr>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th>{{ db_trans('started_on') }}</th>
                            <th class="text-end">{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $enrollment)
                            @php
                                $normalizedEnrollmentStatus = $enrollment->status === \App\Models\MafundishoEnrollment::STATUS_ACTIVE
                                    ? \App\Models\MafundishoEnrollment::STATUS_CONTINUING
                                    : $enrollment->status;
                            @endphp

                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $enrollment->member?->full_name ?? '—' }}</div>
                                    <div class="small text-muted">
                                        {{ $enrollment->member?->familia?->name ?? '—' }}
                                        @if($enrollment->member?->familia?->jumuiya)
                                            · {{ $enrollment->member->familia->jumuiya->name }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $statusLabels[$normalizedEnrollmentStatus] ?? $enrollment->status }}</td>
                                <td>{{ optional($enrollment->started_at)->format('M d, Y') ?: '—' }}</td>
                                <td class="text-end">
                                    @if($enrollment->member_id)
                                        <a href="{{ route('members.show', $enrollment->member_id) }}"
                                           class="btn btn-sm btn-primary">
                                            {{ db_trans('view') }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    {{ db_trans('no_data_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@can('mafundisho.create')
    <div class="modal fade" id="createEnrollmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4">
                <form method="POST" action="{{ url('/mafundisho') }}">
                        @csrf

                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold">{{ db_trans('add_student') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                        </div>

                        <div class="modal-body pt-3">
                            @include('admin.mafundisho.partials.form-fields', [
                                'prefix' => 'create_',
                                'defaultType' => $type,
                                'defaultYear' => $year,
                                'defaultTeachingTypeId' => $currentTeachingType?->id,
                                'teachingTypes' => $teachingTypes,
                                'members' => $members,
                                'statusLabels' => $statusLabels,
                            ])
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                                {{ db_trans('cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-1"></i>{{ db_trans('save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeRedirect = document.getElementById('teachingTypeRedirect');
    const yearRedirect = document.getElementById('teachingYearRedirect');
    const monthRedirect = document.getElementById('teachingMonthRedirect');
    const statusRedirect = document.getElementById('teachingStatusRedirect');
    const applyButton = document.getElementById('teachingApplyFilters');

    function buildUrl() {
        const type = typeRedirect?.value || @json($type);
        const year = yearRedirect?.value || @json($year);

        let url = `/mafundisho/${type}/${year}`;
        const params = new URLSearchParams();

        if (monthRedirect?.value) {
            params.set('month', monthRedirect.value);
        }

        if (statusRedirect?.value) {
            params.set('status', statusRedirect.value);
        }

        const query = params.toString();

        if (query) {
            url += `?${query}`;
        }

        return url;
    }

    applyButton?.addEventListener('click', function () {
        window.location.href = buildUrl();
    });

    typeRedirect?.addEventListener('change', function () {
        window.location.href = buildUrl();
    });

    yearRedirect?.addEventListener('change', function () {
        window.location.href = buildUrl();
    });

    function refreshTeachingForm(form) {
        if (!form) return;

        const teachingTypeSelect = form.querySelector('.teaching-type-select');
        const statusSelect = form.querySelector('.teaching-status-select');

        const selectedType = teachingTypeSelect?.options[teachingTypeSelect.selectedIndex];
        const requiresPartnerInfo = selectedType?.dataset.requiresPartnerInfo === '1';
        const selectedStatus = statusSelect?.value;

        form.querySelectorAll('.marriage-only, [class*="marriage-only"]').forEach(function (section) {
            section.style.display = requiresPartnerInfo ? '' : 'none';
        });

        form.querySelectorAll('.teaching-completed-only, [class*="completed-only"]').forEach(function (section) {
            section.style.display = selectedStatus === 'completed' ? '' : 'none';
        });

        const hiddenType = form.querySelector('input[name="type"]');
        if (hiddenType && selectedType) {
            hiddenType.value = selectedType.dataset.slug || '';
        }
    }

    document.querySelectorAll('#createEnrollmentModal form').forEach(function (form) {
        refreshTeachingForm(form);

        form.addEventListener('change', function (event) {
            if (
                event.target.classList.contains('teaching-type-select') ||
                event.target.classList.contains('teaching-status-select')
            ) {
                refreshTeachingForm(form);
            }
        });
    });

    const previousContext = @json(old('form_context'));

    if (previousContext === 'create_enrollment') {
        const modalEl = document.getElementById('createEnrollmentModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    const monthlyChartEl = document.getElementById('teachingMonthlyChart');
    if (monthlyChartEl && window.Chart) {
        new Chart(monthlyChartEl, {
            type: 'bar',
            data: {
                labels: @json($monthlyChart['labels'] ?? []),
                datasets: [{
                    label: @json(db_trans('students')),
                    data: @json($monthlyChart['data'] ?? []),
                    borderRadius: 10,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    const statusChartEl = document.getElementById('teachingStatusChart');
    if (statusChartEl && window.Chart) {
        new Chart(statusChartEl, {
            type: 'doughnut',
            data: {
                labels: @json($statusChart['labels'] ?? []),
                datasets: [{
                    data: @json($statusChart['data'] ?? []),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '68%'
            }
        });
    }
});
</script>
@endpush