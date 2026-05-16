@extends('layouts.admin')

@section('title', db_trans('monthly_tithe_breakdown'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tithes-module.css') }}">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">{{ db_trans('monthly_tithe_breakdown') }}</h3>
            <div class="text-muted">{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</div>
        </div>
        <div class="tithe-mini-chip">{{ db_trans('amount') }}: {{ number_format($total, 2) }}</div>
    </div>

    <div class="card tithe-panel">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tithe-table align-middle mb-0">
                    <thead><tr><th>#</th><th>{{ db_trans('jumuiya') }}</th><th>{{ db_trans('kanda') }}</th><th>{{ db_trans('amount') }}</th><th>{{ db_trans('actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->jumuiya_name }}</td>
                            <td>{{ $item->kanda_name }}</td>
                            <td>{{ number_format($item->total_amount, 2) }}</td>
                            <td>
                                @if($item->jumuiya_id)
                                    <a href="{{ route('finance.tithes.jumuiya.show', ['jumuiya' => $item->jumuiya_id, 'year' => $year]) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('view') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">{{ db_trans('no_records_found') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
