@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('familias') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('familia') }}</th>
                    <th>{{ db_trans('phone') }}</th>
                    <th>{{ db_trans('members') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->familia ?? '—' }}</td>
                        <td>{{ $row->phone ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($row->members ?? 0)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection