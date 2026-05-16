@extends('layouts.admin')

@section('title', db_trans('jumuiya_tithe_detail'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tithes-module.css') }}">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-1">{{ $jumuiya->name }}</h3>
            <div class="text-muted">{{ $jumuiya->kanda?->name }} · {{ db_trans('year_total') }}: {{ number_format($total, 2) }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('finance.tithes.jumuiya.matrix', [$jumuiya, $filters['year'] ?? now()->year]) }}" class="btn btn-outline-primary">{{ db_trans('monthly_matrix') }}</a>
            <a href="{{ route('finance.tithes.index', ['jumuiya_id' => $jumuiya->id, 'year' => $filters['year'] ?? now()->year]) }}" class="btn btn-light">{{ db_trans('view_all') }}</a>
        </div>
    </div>

    @include('admin.finance.tithes.partials.filter-bar', ['showMembers' => false])

    <div class="card tithe-panel">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tithe-table align-middle mb-0">
                    <thead>
                        <tr><th>#</th><th>{{ db_trans('member') }}</th><th>{{ db_trans('family') }}</th><th>{{ db_trans('bahasha') }}</th><th>{{ db_trans('amount') }}</th><th>{{ db_trans('actions') }}</th></tr>
                    </thead>
                    <tbody>
                    @forelse($items as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $row->member_name }}</div>
                                <div class="small text-muted">{{ $row->member_code }}</div>
                            </td>
                            <td>{{ $row->familia_name }}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm bahasha-input" data-member-id="{{ $row->member_id }}" value="{{ $row->bahasha }}" placeholder="{{ db_trans('bahasha') }}">
                            </td>
                            <td>{{ number_format($row->total_amount, 2) }}</td>
                            <td><a href="{{ route('members.show', $row->member_id) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('view') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.bahasha-input').forEach(function (input) {
    input.addEventListener('blur', function () {
        fetch(@json(url('finance/tithes/members')) + '/' + this.dataset.memberId + '/bahasha', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ bahasha: this.value })
        }).then(r => r.json()).then(data => {
            if (data.success && window.Toast) {
                Toast.fire({ icon: 'success', title: data.message });
            }
        });
    });
});
</script>
@endpush
