@extends('layouts.admin')

@section('title', $pageTitle)

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-lg-8">
                <span class="ui-page-badge">{{ db_trans('zaka') }}</span>
                <h1 class="ui-page-title">{{ $recorder->name }}</h1>
            </div>

            <div class="col-lg-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('finance.tithes.activity-date', ['date' => $activityDate->format('Y-m-d')]) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon"><i class="fas fa-arrow-left"></i></span>
                        <span class="ui-hero-action-text">{{ db_trans('back') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div class="flex-grow-1">
                    @include('admin.finance.tithes.partials.activity-header', [
                        'parishName' => $parishName,
                        'title' => db_trans('tithes_recorded_by') . ' ' . $recorder->name,
                        'subtitle' => $activityDate->format('Y-m-d'),
                    ])
                </div>

                <div class="d-flex flex-wrap gap-2">
                    @if(Route::has('pdf.finance.tithes.activity-recorder.export'))
                        <a
                            href="{{ route('pdf.finance.tithes.activity-recorder.export', array_merge(['date' => $activityDate->format('Y-m-d'), 'recorder' => $recorder->id], request()->query())) }}"
                            target="_blank"
                            class="btn btn-sm btn-danger rounded-pill px-3"
                        >
                            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                        </a>
                    @endif

                    @if(Route::has('finance.tithes.activity-recorder.export.excel'))
                        <a
                            href="{{ route('finance.tithes.activity-recorder.export.excel', array_merge(['date' => $activityDate->format('Y-m-d'), 'recorder' => $recorder->id], request()->query())) }}"
                            class="btn btn-sm btn-success rounded-pill px-3"
                        >
                            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="ui-stat-card ui-tone-primary p-4">
                        <div class="ui-stat-label">{{ db_trans('records') }}</div>
                        <div class="ui-stat-value">{{ number_format($recordsCount) }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="ui-stat-card ui-tone-success p-4">
                        <div class="ui-stat-label">{{ db_trans('grand_total') }}</div>
                        <div class="ui-stat-value">{{ number_format($total, 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle" id="titheActivityRecorderDetailTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('member') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('kanda') }}</th>
                            <th>{{ db_trans('jumuiya') }}</th>
                            <th>{{ db_trans('payment_method') }}</th>
                            <th>{{ db_trans('status') }}</th>
                            <th class="text-end">{{ db_trans('amount') }}</th>
                            <th class="text-end">{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->member?->full_name ?? '—' }}</td>
                                <td>{{ $item->member?->phone ?: '—' }}</td>
                                <td>{{ $item->member?->familia?->jumuiya?->kanda?->name ?? $item->jumuiya?->kanda?->name ?? '—' }}</td>
                                <td>{{ $item->jumuiya?->name ?? $item->member?->familia?->jumuiya?->name ?? '—' }}</td>
                                <td>{{ $item->payment_method_label }}</td>
                                <td>{{ $item->status_label }}</td>
                                <td class="text-end fw-bold">{{ number_format((float) $item->amount, 2) }}</td>
                                <td class="text-end">
                                    @include('admin.finance.tithes.partials.activity-actions', ['item' => $item])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-end">{{ db_trans('grand_total') }}</th>
                            <th class="text-end">{{ number_format($total, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($items as $item)
    @canany(['finance.update', 'finance.tithes.update'])
        <div class="modal fade" id="editTitheModal-{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content ui-modal-card admin-ui-v4">
                    <form method="POST" action="{{ route('finance.tithes.update', $item) }}">
                        @csrf
                        @method('PUT')

                        <div class="modal-header ui-modal-header">
                            <h5 class="ui-modal-title">{{ db_trans('edit_tithe') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            @include('admin.finance.tithes.partials.single-form', [
                                'tithe' => $item,
                                'members' => $members,
                                'jumuiyas' => $jumuiyas,
                                'kandas' => $kandas,
                                'statuses' => $statuses,
                                'paymentMethods' => $paymentMethods,
                            ])
                        </div>

                        <div class="modal-footer ui-modal-footer">
                            <button type="button" class="btn ui-btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                            <button type="submit" class="btn ui-btn-primary">{{ db_trans('save_changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcanany
@endforeach
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && document.getElementById('titheActivityRecorderDetailTable')) {
        $('#titheActivityRecorderDetailTable').DataTable({
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            responsive: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
            columnDefs: [
                { orderable: false, searchable: false, targets: -1 }
            ],
            language: {
                search: '',
                searchPlaceholder: @json(db_trans('search')) + '...',
                lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('rows')),
                info: @json(db_trans('showing')) + ' _START_ - _END_ / _TOTAL_',
                infoEmpty: @json(db_trans('no_records_available')),
                zeroRecords: @json(db_trans('no_matching_records')),
                paginate: {
                    previous: @json(db_trans('previous')),
                    next: @json(db_trans('next'))
                }
            }
        });
    }

    document.querySelectorAll('.delete-tithe-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: @json(db_trans('are_you_sure')),
                text: @json(db_trans('tithe_delete_confirmation')),
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: @json(db_trans('delete')),
                cancelButtonText: @json(db_trans('cancel')),
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush