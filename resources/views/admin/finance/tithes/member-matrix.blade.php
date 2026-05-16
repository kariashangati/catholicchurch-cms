@extends('layouts.admin')

@section('title', db_trans('member_monthly_tithe_matrix'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tithes-module.css') }}">
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-1">{{ db_trans('member_monthly_tithe_matrix') }}</h3>
            <div class="text-muted">{{ $jumuiya->name }} · {{ $jumuiya->kanda?->name }} · {{ $year }}</div>
        </div>
        <a href="{{ route('finance.tithes.jumuiya.show', ['jumuiya' => $jumuiya->id, 'year' => $year]) }}" class="btn btn-light">{{ db_trans('back') }}</a>
    </div>

    <div class="card tithe-panel">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table tithe-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ db_trans('member') }}</th>
                            @foreach(range(1, 12) as $month)
                                <th>{{ \Carbon\Carbon::create()->month($month)->format('M') }}</th>
                            @endforeach
                            <th>{{ db_trans('year_total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['member']->full_name }}</td>
                                @foreach(range(1, 12) as $month)
                                    <td>{{ number_format($row['months'][$month], 2) }}</td>
                                @endforeach
                                <td class="fw-bold">{{ number_format($row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="15" class="text-center py-5 text-muted">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
