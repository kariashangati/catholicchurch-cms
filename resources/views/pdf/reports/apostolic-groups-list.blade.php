@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ db_trans('apostolic_groups') }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ db_trans('group') }}</th>
                    <th>{{ db_trans('leader') }}</th>
                    <th>{{ db_trans('membership_rule') }}</th>
                    <th>{{ db_trans('meeting') }}</th>
                    <th>{{ db_trans('members') }}</th>
                    <th>{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->name ?? '—' }}</td>
                        <td>{{ $row->leader_name ?? '—' }}</td>
                        <td>{{ $row->membership_rule_label ?? '—' }}</td>
                        <td>{{ $row->meeting_label ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format((int) ($row->members_count ?? 0)) }}</td>
                        <td>{{ $row->status_label ?? '—' }}</td>
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