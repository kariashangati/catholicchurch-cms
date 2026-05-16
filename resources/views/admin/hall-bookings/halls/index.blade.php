@extends('layouts.admin')

@section('title', db_trans('halls'))

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/hall-booking-v1.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="hall-booking-module">
    <section class="hb-hero hb-hero-compact mb-4">
        <div>
            <h2 class="hb-hero-title">{{ db_trans('halls') }}</h2>
            <p class="hb-hero-subtitle">{{ db_trans('manage_halls_pictures_capacity_conditions_and_payment_details') }}</p>
        </div>
        @can('hall-bookings.halls.create')
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createHallModal"><i class="fas fa-plus me-1"></i>{{ db_trans('add_hall') }}</button>
        @endcan
    </section>

    <div class="card hb-panel border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table hb-table align-middle" id="hbHallsTable">
                    <thead><tr><th>{{ db_trans('hall') }}</th><th>{{ db_trans('capacity') }}</th><th>{{ db_trans('default_price') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('images') }}</th><th>{{ db_trans('actions') }}</th></tr></thead>
                    <tbody>
                    @foreach($halls as $hall)
                        <tr>
                            <td><strong>{{ $hall->name }}</strong><div class="small text-muted">{{ $hall->location ?: '-' }}</div></td>
                            <td>{{ $hall->capacity ?: '-' }}</td>
                            <td>{{ number_format((float)$hall->default_price, 2) }}</td>
                            <td><span class="hb-status {{ $hall->is_active ? 'hb-status-imeidhinishwa' : 'hb-status-imefutwa' }}">{{ $hall->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                            <td>{{ $hall->images_count ?? $hall->images->count() }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    @can('hall-bookings.halls.update')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editHallModal{{ $hall->id }}">{{ db_trans('edit') }}</button>
                                    @endcan
                                    @can('hall-bookings.halls.delete')
                                        <form method="POST" action="{{ route('hall-bookings.halls.destroy', $hall) }}" data-confirm-delete>
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button>
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

    @can('hall-bookings.halls.create')
        @include('admin.hall-bookings.halls.partials.form-modal', ['modalId' => 'createHallModal', 'title' => db_trans('add_hall'), 'action' => route('hall-bookings.halls.store'), 'hall' => null])
    @endcan
    @foreach($halls as $hall)
        @can('hall-bookings.halls.update')
            @include('admin.hall-bookings.halls.partials.form-modal', ['modalId' => 'editHallModal'.$hall->id, 'title' => db_trans('edit_hall'), 'action' => route('hall-bookings.halls.update', $hall), 'hall' => $hall])
        @endcan
    @endforeach
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('admin/js/hall-booking-v1.js') }}"></script>
@endpush
