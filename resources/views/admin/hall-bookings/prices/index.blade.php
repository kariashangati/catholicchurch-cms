@extends('layouts.admin')
@section('title', db_trans('hall_prices'))
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/hall-booking-v1.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush
@section('content')
<div class="hall-booking-module">
    <section class="hb-hero hb-hero-compact mb-4"><div><h2 class="hb-hero-title">{{ db_trans('hall_prices') }}</h2><p class="hb-hero-subtitle">{{ db_trans('manage_hall_prices_by_day_or_special_date') }}</p></div>@can('hall-bookings.prices.manage')<button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createPriceRuleModal"><i class="fas fa-plus me-1"></i>{{ db_trans('add_price_rule') }}</button>@endcan</section>
    <div class="card hb-panel border-0"><div class="card-body"><div class="table-responsive"><table class="table hb-table align-middle" id="hbPriceRulesTable"><thead><tr><th>{{ db_trans('hall') }}</th><th>{{ db_trans('type') }}</th><th>{{ db_trans('date_or_day') }}</th><th>{{ db_trans('price') }}</th><th>{{ db_trans('effective_period') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('actions') }}</th></tr></thead><tbody>
    @foreach($rules as $rule)
        <tr><td>{{ $rule->hall?->name }}</td><td>{{ $rule->specific_date ? db_trans('special_date') : db_trans('weekly') }}</td><td>{{ $rule->specific_date?->format('d M Y') ?: ($weekDays[$rule->day_of_week] ?? '-') }}</td><td>{{ number_format((float)$rule->price,2) }}</td><td>{{ $rule->effective_from?->format('d M Y') ?: '-' }} - {{ $rule->effective_to?->format('d M Y') ?: '-' }}</td><td><span class="hb-status {{ $rule->is_active ? 'hb-status-imeidhinishwa' : 'hb-status-imefutwa' }}">{{ $rule->is_active ? db_trans('active') : db_trans('inactive') }}</span></td><td><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPriceRuleModal{{ $rule->id }}">{{ db_trans('edit') }}</button><form method="POST" action="{{ route('hall-bookings.prices.destroy',$rule) }}" data-confirm-delete>@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ db_trans('delete') }}</button></form></div></td></tr>
    @endforeach
    </tbody></table></div></div></div>
    @include('admin.hall-bookings.prices.partials.form-modal', ['modalId'=>'createPriceRuleModal','title'=>db_trans('add_price_rule'),'action'=>route('hall-bookings.prices.store'),'rule'=>null])
    @foreach($rules as $rule)
        @include('admin.hall-bookings.prices.partials.form-modal', ['modalId'=>'editPriceRuleModal'.$rule->id,'title'=>db_trans('edit_price_rule'),'action'=>route('hall-bookings.prices.update',$rule),'rule'=>$rule])
    @endforeach
</div>
@endsection
@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script><script src="{{ asset('admin/js/hall-booking-v1.js') }}"></script>
@endpush
