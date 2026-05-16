@extends('layouts.admin')
@section('title', $pageTitle)
@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
<link rel="stylesheet" href="{{ asset('admin/css/tithes-module-v4.css') }}">
@endpush
@section('content')
<div class="admin-ui-v4 tithe-v4">
    <div class="ui-page-hero mb-4"><div class="ui-hero-pattern"></div><div class="position-relative"><span class="ui-page-badge">Security Review</span><h1 class="ui-page-title">{{ $pageTitle }}</h1><p class="ui-page-subtitle">Restricted page for members recorded more than once in the same month.</p></div></div>
    <div class="card ui-filter-card border-0 mb-4"><div class="card-body p-4"><form method="GET" class="row g-3"><div class="col-md-3"><input name="year" class="form-control" value="{{ $filters['year'] ?? now()->year }}" placeholder="Year"></div><div class="col-md-3"><input name="month" class="form-control" value="{{ $filters['month'] ?? '' }}" placeholder="Month"></div><div class="col-md-3"><select name="jumuiya_id" class="form-select"><option value="">{{ db_trans('all_jumuiyas') }}</option>@foreach($jumuiyas as $jumuiya)<option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>@endforeach</select></div><div class="col-md-3"><button class="btn ui-btn-primary w-100">{{ db_trans('apply_filters') }}</button></div></form></div></div>
    <div class="card ui-table-card border-0"><div class="card-body p-4"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>{{ db_trans('member') }}</th><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('month') }}</th><th>Entries</th><th>{{ db_trans('amount') }}</th></tr></thead><tbody>@forelse($groups as $group)<tr><td>{{ $group->member_name }} <div class="small text-muted">{{ $group->member_code }}</div></td><td>{{ $group->jumuiya_name }}</td><td>{{ $group->tithe_month }}/{{ $group->tithe_year }}</td><td><span class="ui-status-pill ui-status-warning">{{ $group->entries_count }}</span></td><td>{{ number_format($group->total_amount,2) }}</td></tr>@empty<tr><td colspan="5"><div class="ui-empty-state">No duplicate month records found.</div></td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $groups->links() }}</div></div></div>
</div>
@endsection
