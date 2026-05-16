@extends('pdf.layouts.report')

@section('content')
    <div class="section-header">
        <h3>{{ $reportTitle }}</h3>
        <div class="accent-line"></div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center nowrap">#</th>
                    <th>{{ db_trans('member') }}</th>
                    <th>{{ db_trans('member_code') }}</th>
                    <th>{{ db_trans('gender') }}</th>
                    <th>{{ db_trans('phone') }}</th>
                    <th>{{ db_trans('familia') }}</th>
                    <th>{{ db_trans('family_role') }}</th>
                    <th>{{ db_trans('joined') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center tabular-nums">{{ $row->sn }}</td>
                        <td>{{ $row->full_name }}</td>
                        <td>{{ $row->member_code }}</td>
                        <td>{{ $row->gender }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->familia_name }}</td>
                        <td>{{ $row->family_role }}</td>
                        <td class="tabular-nums">{{ $row->joined_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ db_trans('no_members_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection