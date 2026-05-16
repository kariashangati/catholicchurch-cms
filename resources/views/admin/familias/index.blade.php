@extends('layouts.admin')

@section('title', db_trans('familias'))
@section('disable_default_alerts', true)

@section('content')
    <div class="familia-dashboard-page">
        <div class="dashboard-hero familia-dashboard-hero mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <span class="dashboard-hero-badge">{{ db_trans('family_management') }}</span>
                    <h1 class="dashboard-title mb-2">{{ db_trans('familias') }}</h1>

                    <div class="familia-hero-pills mt-3">
                        <span class="familia-hero-pill"><i class="fas fa-home"></i>{{ number_format($stats['total_familias']) }} {{ db_trans('familias') }}</span>
                        <span class="familia-hero-pill"><i class="fas fa-users"></i>{{ number_format($stats['total_members']) }} {{ db_trans('members') }}</span>
                        <span class="familia-hero-pill"><i class="fas fa-phone"></i>{{ number_format($stats['familias_with_phone']) }} {{ db_trans('familias_with_phone') }}</span>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="familia-quick-grid">
                        @can('familias.create')
                            <button type="button" class="familia-quick-card" data-bs-toggle="modal" data-bs-target="#familiaCreateModal">
                                <span class="familia-quick-icon"><i class="fas fa-plus-circle"></i></span>
                                <span><strong>{{ db_trans('create_family') }}</strong><small>{{ db_trans('open_quick_create_modal') }}</small></span>
                            </button>
                        @endcan

                        @can('members.view')
                            <a href="{{ route('members.index', ['open' => 'create']) }}" class="familia-quick-card">
                                <span class="familia-quick-icon"><i class="fas fa-user-friends"></i></span>
                                <span><strong>{{ db_trans('add_member') }}</strong><small>{{ db_trans('view_members_directory') }}</small></span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach([
                ['label' => db_trans('total_familias'), 'value' => number_format($stats['total_familias']), 'icon' => 'fa-home', 'tone' => 'primary'],
                ['label' => db_trans('active_familias'), 'value' => number_format($stats['active_familias']), 'icon' => 'fa-circle-check', 'tone' => 'success'],
                ['label' => db_trans('inactive_familias'), 'value' => number_format($stats['inactive_familias']), 'icon' => 'fa-circle-pause', 'tone' => 'warning'],
                ['label' => db_trans('total_members'), 'value' => number_format($stats['total_members']), 'icon' => 'fa-users', 'tone' => 'info'],
                ['label' => db_trans('familias_without_phone'), 'value' => number_format($stats['familias_without_phone']), 'icon' => 'fa-phone-slash', 'tone' => 'danger'],
                ['label' => db_trans('familias_without_address'), 'value' => number_format($stats['familias_without_address']), 'icon' => 'fa-location-dot', 'tone' => 'secondary'],
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
            <div class="col-xl-8">
                <div class="card dashboard-panel familia-panel h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-4">
                            <div>
                                <h5 class="mb-1">{{ db_trans('family_size_overview') }}</h5>
                                <p class="text-muted mb-0">{{ db_trans('family_size_overview_description') }}</p>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 330px;"><canvas id="familiaSizeChart"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card dashboard-panel familia-panel border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="familia-section-header mb-3">
                            <div>
                                <h5 class="mb-1">{{ db_trans('phone_coverage') }}</h5>
                                <p class="text-muted mb-0">{{ db_trans('families_with_and_without_phone_numbers') }}</p>
                            </div>
                        </div>
                        <div class="chart-container" style="height: 260px;"><canvas id="familiaPhoneChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card familia-directory-card border-0 shadow-sm" id="familiaDirectoryCard">
            <div class="card-header bg-transparent border-0 p-4 pb-0">
                <div class="familia-section-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">{{ db_trans('family_directory') }}</h5>
                        <p class="text-muted mb-0">{{ db_trans('manage_family_records_here') }}</p>
                    </div>

                    @can('familias.view')
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('pdf.familias.export') }}" class="btn btn-sm btn-danger rounded-pill">
                                <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                            </a>

                            <a href="{{ route('familias.export.excel') }}" class="btn btn-sm btn-success rounded-pill">
                                <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>
            <div class="card-body p-4 pt-3">
                <div class="table-responsive">
                    <table class="table align-middle familia-data-table w-100" id="familiaDirectoryTable">
                        <thead>
                            <tr>
                                <th>{{ db_trans('family') }}</th>
                                <th>{{ db_trans('jumuiya') }}</th>
                                <th>{{ db_trans('phone') }}</th>
                                <th>{{ db_trans('envelope_no') }}</th>
                                <th>{{ db_trans('members_count') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($familias as $familia)
                                <tr>
                                    <td>
                                        <div class="familia-row-identity">
                                            <div class="familia-row-avatar">{{ strtoupper(mb_substr($familia->name, 0, 1)) }}</div>
                                            <div>
                                                <div class="familia-row-title">{{ $familia->name }}</div>
                                                <div class="familia-row-subtitle">{{ $familia->address ?: db_trans('no_address_available') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="familia-row-title">{{ $familia->jumuiya?->name ?? '—' }}</div>
                                        <div class="familia-row-subtitle">{{ $familia->jumuiya?->kanda?->name ?? '—' }}</div>
                                    </td>
                                    <td>{{ $familia->phone ?: '—' }}</td>
                                    <td>{{ $familia->envelope_no ?: '—' }}</td>
                                    <td><span class="familia-count-badge">{{ number_format($familia->members_count) }}</span></td>
                                    <td>
                                        <span class="familia-status-badge {{ $familia->is_active ? 'active' : 'inactive' }}">{{ $familia->is_active ? db_trans('active') : db_trans('inactive') }}</span>
                                    </td>
                                    <td>
                                        <div class="familia-action-row">
                                            <a href="{{ route('familias.show', $familia) }}" class="btn btn-sm familia-btn-soft"><i class="fas fa-eye me-1"></i>{{ db_trans('view') }}</a>
                                            @can('familias.update')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm familia-btn-soft"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#familiaEditModal-{{ $familia->id }}">
                                                    <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                                </button>
                                            @endcan
                                            @can('familias.delete')
                                                <form method="POST" action="{{ route('familias.destroy', $familia) }}" class="familia-delete-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm familia-btn-danger"><i class="fas fa-trash-alt me-1"></i>{{ db_trans('delete') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                @can('familias.update')
                                    <div class="modal fade" id="familiaEditModal-{{ $familia->id }}" tabindex="-1" aria-hidden="true">
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
                                                            'kandas' => $kandas,
                                                            'jumuiyas' => $jumuiyas,
                                                            'prefix' => 'edit_'.$familia->id.'_',
                                                            'mode' => 'edit',
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @can('familias.create')
        <div class="modal fade" id="familiaCreateModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content familia-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title">{{ db_trans('create_family') }}</h5>
                            <p class="text-muted small mb-0">{{ db_trans('quickly_register_a_new_family') }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('familias.store') }}">
                        @csrf
                        <input type="hidden" name="form_context" value="familia_create">
                        <div class="modal-body pt-3">
                            @include('admin.familias.partials.familia-form-fields', [
                                'kandas' => $kandas,
                                'jumuiyas' => $jumuiyas,
                                'prefix' => 'create_',
                                'mode' => 'create',
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
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $('#familiaDirectoryTable').length) {
                $('#familiaDirectoryTable').DataTable({
                    responsive: true,
                    pageLength: 10,
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

            const chartDefaults = {
                borderRadius: 10,
                maxBarThickness: 34,
            };

            const sizeCtx = document.getElementById('familiaSizeChart');
            if (sizeCtx) {
                new Chart(sizeCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['size_labels']),
                        datasets: [{
                            label: @json(db_trans('members_count')),
                            data: @json($chartData['size_members']),
                            backgroundColor: ['#2563eb', '#16a34a', '#7c3aed', '#f59e0b', '#0ea5e9', '#22c55e', '#8b5cf6', '#fb7185'],
                            ...chartDefaults
                        }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            const phoneCtx = document.getElementById('familiaPhoneChart');
            if (phoneCtx) {
                new Chart(phoneCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($chartData['phone_labels']),
                        datasets: [{ data: @json($chartData['phone_data']), backgroundColor: ['#16a34a', '#ef4444'], borderWidth: 0 }]
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, cutout: '68%' }
                });
            }

            document.querySelectorAll('.familia-delete-form').forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('confirm_delete_family')),
                        text: @json(db_trans('this_action_cannot_be_undone')),
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#7c3aed',
                        confirmButtonText: @json(db_trans('delete')),
                        cancelButtonText: @json(db_trans('cancel')),
                    }).then((result) => { if (result.isConfirmed) form.submit(); });
                });
            });

            const previousFormContext = @json(old('form_context'));
            if (previousFormContext === 'familia_create') {
                const modal = new bootstrap.Modal(document.getElementById('familiaCreateModal'));
                modal.show();
            }

            const setupFamiliaKandaJumuiyaFilters = () => {
                document.querySelectorAll('form').forEach(function (form) {
                    const kandaSelect = form.querySelector('.js-familia-kanda-filter');
                    const jumuiyaWrap = form.querySelector('.js-familia-jumuiya-wrap');
                    const jumuiyaSelect = form.querySelector('.js-familia-jumuiya-select');

                    if (!kandaSelect || !jumuiyaWrap || !jumuiyaSelect) return;

                    const syncJumuiyaOptions = function () {
                        const selectedKanda = kandaSelect.value;

                        if (!selectedKanda) {
                            jumuiyaWrap.classList.add('d-none');
                            jumuiyaSelect.value = '';

                            Array.from(jumuiyaSelect.options).forEach(function (option) {
                                if (!option.value) return;
                                option.disabled = true;
                                option.hidden = true;
                            });

                            if (window.jQuery && jQuery.fn.select2 && jQuery(jumuiyaSelect).data('select2')) {
                                jQuery(jumuiyaSelect).val(null).trigger('change.select2');
                            }

                            return;
                        }

                        jumuiyaWrap.classList.remove('d-none');

                        Array.from(jumuiyaSelect.options).forEach(function (option) {
                            if (!option.value) {
                                option.disabled = false;
                                option.hidden = false;
                                return;
                            }

                            const matchesKanda = option.dataset.kandaId === selectedKanda;
                            option.disabled = !matchesKanda;
                            option.hidden = !matchesKanda;
                        });

                        if (jumuiyaSelect.selectedOptions[0]?.disabled) {
                            jumuiyaSelect.value = '';
                        }

                        if (window.jQuery && jQuery.fn.select2 && jQuery(jumuiyaSelect).data('select2')) {
                            jQuery(jumuiyaSelect).trigger('change.select2');
                        }
                    };

                    kandaSelect.addEventListener('change', syncJumuiyaOptions);
                    syncJumuiyaOptions();
                });
            };

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('.js-familia-jumuiya-select').each(function () {
                    const select = jQuery(this);
                    const modal = select.closest('.modal');

                    select.select2({
                        dropdownParent: modal.length ? modal : jQuery(document.body),
                        width: '100%',
                        placeholder: @json(db_trans('select_jumuiya')),
                        allowClear: true
                    });
                });
            }

            setupFamiliaKandaJumuiyaFilters();
        });
    </script>
@endpush