@extends('layouts.admin')
@section('title', db_trans('main_offerings'))
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="dashboard-title mb-1">{{ db_trans('manage_main_offerings') }}</h2><p class="text-muted mb-0">{{ db_trans('main_offerings') }}</p></div>
    @can('finance.create')<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMainOfferingModal"><i class="fas fa-plus me-2"></i>{{ db_trans('add_main_offering') }}</button>@endcan
</div>

<form method="GET" class="card dashboard-panel mb-4"><div class="card-body p-4"><div class="row g-3 align-items-end">
    <div class="col-md-2"><label class="form-label">{{ db_trans('year') }}</label><select name="year" class="form-select">@for($yr = now()->year; $yr >= 2020; $yr--)<option value="{{ $yr }}" @selected(($filters['year'] ?? now()->year) == $yr)>{{ $yr }}</option>@endfor</select></div>
    <div class="col-md-2"><label class="form-label">{{ db_trans('month') }}</label><select name="month" class="form-select"><option value="">{{ db_trans('all_months') }}</option>@foreach(range(1, 12) as $monthNumber)<option value="{{ $monthNumber }}" @selected(($filters['month'] ?? '') == $monthNumber)>{{ \Carbon\Carbon::create()->month($monthNumber)->format('F') }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">{{ db_trans('mass_type') }}</label><select name="mass_type_id" class="form-select"><option value="">{{ db_trans('all_mass_types') }}</option>@foreach($massTypes as $massType)<option value="{{ $massType->id }}" @selected(($filters['mass_type_id'] ?? '') == $massType->id)>{{ $massType->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">{{ db_trans('status') }}</label><select name="status" class="form-select"><option value="">{{ db_trans('all_statuses') }}</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>{{ db_trans('filter_records') }}</button></div>
</div></div></form>

<div class="card dashboard-panel"><div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">{{ db_trans('main_offerings') }}</h5><div class="text-muted">{{ number_format($total, 2) }}</div></div>
    <div class="table-responsive"><table class="table align-middle table-hover mb-0">
        <thead><tr><th>#</th><th>{{ db_trans('mass_type') }}</th><th>Mass Name</th><th>{{ db_trans('amount') }}</th><th>{{ db_trans('offering_date') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('actions') }}</th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $items->firstItem() + $loop->index }}</td>
                <td>{{ $item->massType?->name ?? '—' }}</td>
                <td>{{ $item->mass_name ?: '—' }}</td>
                <td>{{ number_format($item->amount, 2) }}</td>
                <td>{{ optional($item->offering_date)->format('M d, Y') }}</td>
                <td><span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'pending' ? 'warning text-dark' : 'danger') }}">{{ ucfirst($item->status) }}</span></td>
                <td><div class="d-flex gap-2">@can('finance.update')<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMainOfferingModal{{ $item->id }}"><i class="fas fa-edit"></i></button>@endcan @can('finance.delete')<form method="POST" action="{{ route('finance.main-offerings.destroy', $item) }}" onsubmit="return confirm('Delete this record?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endcan</div></td>
            </tr>
            @include('admin.finance.partials.main-offering-modal', ['modalId' => 'editMainOfferingModal'.$item->id, 'title' => db_trans('update'), 'action' => route('finance.main-offerings.update', $item), 'method' => 'PUT', 'item' => $item])
        @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <div class="mt-3">{{ $items->links() }}</div>
</div></div>
@can('finance.create')@include('admin.finance.partials.main-offering-modal', ['modalId' => 'createMainOfferingModal', 'title' => db_trans('add_main_offering'), 'action' => route('finance.main-offerings.store'), 'item' => null])@endcan
@endsection
