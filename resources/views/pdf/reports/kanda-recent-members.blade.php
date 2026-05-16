@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('recent_members') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('jumuiya') }}</th>
                    <th>{{ db_trans('gender') }}</th>
                    <th>{{ db_trans('phone') }}</th>
                    <th>{{ db_trans('joined') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->member ?? '—' }}</td>
                        <td>{{ $row->jumuiya ?? '—' }}</td>
                        <td>{{ $row->gender ?? '—' }}</td>
                        <td>{{ $row->phone ?? '—' }}</td>
                        <td>{{ $row->joined ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">{{ db_trans('no_records_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection