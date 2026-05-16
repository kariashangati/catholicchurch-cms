@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('group_members') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('familia') }}</th>
                    <th>{{ db_trans('jumuiya') }}</th>
                    <th>{{ db_trans('role') }}</th>
                    <th>{{ db_trans('status') }}</th>
                    <th>{{ db_trans('joined') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->member_name ?? '—' }}</td>
                        <td>{{ $row->familia_name ?? '—' }}</td>
                        <td>{{ $row->jumuiya_name ?? '—' }}</td>
                        <td>{{ $row->role_label ?? '—' }}</td>
                        <td>{{ $row->status_label ?? '—' }}</td>
                        <td>{{ $row->joined_at_label ?? '—' }}</td>
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