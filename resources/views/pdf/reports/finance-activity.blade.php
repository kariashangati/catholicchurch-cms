@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('unified_finance_activity') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('type') }}</th>
                    <th>{{ db_trans('description') }}</th>
                    <th>{{ db_trans('location') }}</th>
                    <th>{{ db_trans('date') }}</th>
                    <th>{{ db_trans('status') }}</th>
                    <th class="text-right">{{ db_trans('amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->type ?? '—' }}</td>
                        <td>{{ $row->description ?? '—' }}</td>
                        <td>{{ $row->location ?? '—' }}</td>
                        <td>{{ $row->date ?? '—' }}</td>
                        <td>{{ $row->status ?? '—' }}</td>
                        <td class="text-right tabular-nums">
                            {{ number_format((float) ($row->amount ?? 0), 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection