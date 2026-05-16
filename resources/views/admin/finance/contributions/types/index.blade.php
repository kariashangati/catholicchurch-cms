@extends('layouts.admin')

@section('title', db_trans('contribution_types'))

@section('disable_default_alerts')@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
    @php
        $types = collect($types ?? []);
        $activeCount = $types->where('is_active', true)->count();
        $inactiveCount = $types->where('is_active', false)->count();
        $installmentCount = $types->where('has_installments', true)->count();
        $currency = fn ($amount) => number_format((float) $amount, 2);
    @endphp

    <div class="admin-ui-v4 contributions-page-v4">
        <div class="ui-page-hero mb-4">
            <div class="ui-hero-pattern"></div>

            <div class="row g-4 align-items-center position-relative">
                <div class="col-xl-8">
                    <span class="ui-page-badge">
                        <i class="fas fa-tags"></i>
                        {{ db_trans('contribution_types') }}
                    </span>

                    <h1 class="ui-page-title mt-3 mb-2">{{ db_trans('contribution_types') }}</h1>

                    <div class="ui-meta-wrap mt-3">
                        <span class="ui-meta-pill">
                            <i class="fas fa-list"></i>
                            {{ number_format($types->count()) }} {{ db_trans('contribution_types') }}
                        </span>

                        <span class="ui-meta-pill">
                            <i class="fas fa-circle-check"></i>
                            {{ number_format($activeCount) }} {{ db_trans('active') }}
                        </span>

                        <span class="ui-meta-pill ui-meta-pill-warning">
                            <i class="fas fa-calendar-check"></i>
                            {{ number_format($installmentCount) }} {{ db_trans('has_installments') }}
                        </span>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="ui-actions-grid">
                        @can('finance.contributions.types.create')
                            <button type="button" class="ui-hero-action border-0 text-start" data-bs-toggle="modal" data-bs-target="#createTypeModal">
                                <span class="ui-hero-action-icon"><i class="fas fa-plus"></i></span>
                                <span class="ui-hero-action-text">{{ db_trans('new_contribution_type') }}</span>
                            </button>
                        @endcan

                        <a href="{{ Route::has('finance.contributions.dashboard') ? route('finance.contributions.dashboard') : '#' }}" class="ui-hero-action">
                            <span class="ui-hero-action-icon"><i class="fas fa-chart-line"></i></span>
                            <span class="ui-hero-action-text">{{ db_trans('dashboard') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="ui-stat-card ui-tone-primary p-4 h-100">
                    <div class="ui-stat-top">
                        <span class="ui-stat-icon"><i class="fas fa-tags"></i></span>
                        <span class="ui-chip">{{ db_trans('setup') }}</span>
                    </div>
                    <div class="ui-stat-label">{{ db_trans('contribution_types') }}</div>
                    <div class="ui-stat-value">{{ number_format($types->count()) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-success"><i class="fas fa-bolt"></i></div>
                    <div class="ui-stat-label">{{ db_trans('active') }}</div>
                    <div class="ui-stat-value">{{ number_format($activeCount) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-warning"><i class="fas fa-calendar-days"></i></div>
                    <div class="ui-stat-label">{{ db_trans('has_installments') }}</div>
                    <div class="ui-stat-value">{{ number_format($installmentCount) }}</div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="ui-mini-card p-4 h-100">
                    <div class="ui-mini-icon ui-mini-tone-danger"><i class="fas fa-power-off"></i></div>
                    <div class="ui-stat-label">{{ db_trans('inactive') }}</div>
                    <div class="ui-stat-value">{{ number_format($inactiveCount) }}</div>
                </div>
            </div>
        </div>

        <div class="ui-table-card p-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="mb-1">{{ db_trans('contribution_types') }}</h5>
                </div>

                <span class="ui-section-badge">
                    {{ number_format($types->count()) }} {{ db_trans('records') }}
                </span>
            </div>

            @if($types->isNotEmpty())
                <div class="table-responsive">
                    <table class="table align-middle" id="contributionTypesTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ db_trans('name') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('has_installments') }}</th>
                                <th>{{ db_trans('target_amount') }}</th>
                                <th>{{ db_trans('due_date') }}</th>
                                <th>{{ db_trans('description') }}</th>
                                <th>{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($types as $type)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="ui-type-block">
                                            <span class="fw-semibold">{{ $type->name }}</span>
                                            <span class="ui-type-meta">{{ db_trans('slug') }}: {{ $type->slug ?? '—' }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="ui-status-pill {{ $type->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">
                                            {{ $type->is_active ? db_trans('active') : db_trans('inactive') }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="ui-status-pill {{ $type->has_installments ? 'ui-status-approved' : 'ui-status-pending' }}">
                                            {{ $type->has_installments ? db_trans('yes') : db_trans('no') }}
                                        </span>
                                    </td>

                                    <td class="ui-amount">
                                        {{ $type->has_installments ? $currency($type->plan->target_amount ?? 0) : '—' }}
                                    </td>

                                    <td data-order="{{ optional($type->plan?->due_date)->format('Y-m-d') }}">
                                        {{ $type->plan?->due_date ? $type->plan->due_date->format('d M Y') : '—' }}
                                    </td>

                                    <td>{{ \Illuminate\Support\Str::limit($type->description, 70) }}</td>

                                    <td class="text-nowrap">
                                        @can('finance.contributions.types.update')
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $type->id }}">
                                                <i class="fas fa-pen me-1"></i>{{ db_trans('edit') }}
                                            </button>
                                        @endcan

                                        @can('finance.contributions.types.delete')
                                            <form method="POST" action="{{ route('finance.contributions.types.destroy', $type) }}" class="d-inline js-swal-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash me-1"></i>{{ db_trans('delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ui-empty-state">
                    <div class="ui-empty-state-icon"><i class="fas fa-tags"></i></div>
                    <h6 class="mb-1">{{ db_trans('no_contribution_types_found') }}</h6>
                </div>
            @endif
        </div>

        @foreach($types as $type)
            @include('admin.finance.contributions.partials.type-modal', [
                'modalId' => 'editTypeModal' . $type->id,
                'title' => db_trans('edit') . ' ' . $type->name,
                'action' => route('finance.contributions.types.update', $type),
                'method' => 'PUT',
                'type' => $type,
            ])
        @endforeach
    </div>

    @include('admin.finance.contributions.partials.type-modal', [
        'modalId' => 'createTypeModal',
        'title' => db_trans('new_contribution_type'),
        'action' => route('finance.contributions.types.store'),
        'method' => 'POST',
        'type' => null,
    ])
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && $.fn.DataTable && $('#contributionTypesTable').length) {
                $('#contributionTypesTable').DataTable({
                    paging: true,
                    info: true,
                    searching: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, @json(db_trans('all'))]],
                    order: [],
                    autoWidth: false,
                    language: {
                        search: @json(db_trans('search')) + ':',
                        lengthMenu: @json(db_trans('show')) + ' _MENU_ ' + @json(db_trans('entries')),
                        info: @json(db_trans('showing')) + ' _START_ ' + @json(db_trans('to')) + ' _END_ ' + @json(db_trans('of')) + ' _TOTAL_ ' + @json(db_trans('entries')),
                        infoEmpty: @json(db_trans('no_records_found')),
                        zeroRecords: @json(db_trans('no_records_found')),
                        paginate: {
                            previous: '‹',
                            next: '›'
                        }
                    }
                });
            }

            document.querySelectorAll('.finance-modal').forEach(function (modal) {
                const toggle = modal.querySelector('.installment-toggle');
                const fields = modal.querySelectorAll('.installment-fields');

                const refresh = function () {
                    fields.forEach(function (field) {
                        field.style.display = toggle && toggle.checked ? '' : 'none';
                    });
                };

                if (toggle) {
                    toggle.addEventListener('change', refresh);
                    refresh();
                }
            });

            document.querySelectorAll('.js-swal-delete').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: @json(db_trans('are_you_sure')),
                        text: @json(db_trans('this_action_cannot_be_undone')),
                        showCancelButton: true,
                        confirmButtonColor: '#7c3aed',
                        confirmButtonText: @json(db_trans('delete')),
                        cancelButtonText: @json(db_trans('cancel'))
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