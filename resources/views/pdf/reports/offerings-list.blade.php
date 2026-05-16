@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('offerings') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ db_trans('offering_type') }}</th>
                    <th>{{ db_trans('mass_type') }}</th>
                    <th>{{ db_trans('collection_scope') }}</th>
                    <th>{{ db_trans('location') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                    <th>{{ db_trans('payment_method') }}</th>
                    <th>{{ db_trans('collection_date') }}</th>
                    <th>{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->sn }}</td>
                        <td>{{ $row->offering_type ?? '—' }}</td>
                        <td>{{ $row->mass_type ?? '—' }}</td>
                        <td>{{ $row->collection_scope ?? '—' }}</td>
                        <td>{{ $row->location ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((float) ($row->amount ?? 0), 2) }}</td>
                        <td>{{ $row->payment_method ?? '—' }}</td>
                        <td>{{ $row->collection_date ?? '—' }}</td>
                        <td>{{ $row->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection