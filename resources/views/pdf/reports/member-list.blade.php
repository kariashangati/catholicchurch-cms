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
                    <th class="text-center" style="width: 5%;">#</th>
                    <th style="width: 19%;">{{ db_trans('member') }}</th>
                    <th style="width: 13%;">{{ db_trans('member_code') }}</th>
                    <th style="width: 12%;">{{ db_trans('phone') }}</th>
                    <th style="width: 14%;">{{ db_trans('familia') }}</th>
                    <th style="width: 13%;">{{ db_trans('jumuiya') }}</th>
                    <th style="width: 11%;">{{ db_trans('kanda') }}</th>
                    <th style="width: 8%;">{{ db_trans('sacraments') }}</th>
                    <th class="text-center" style="width: 5%;">{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->sn }}</td>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->member_code }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->familia_name }}</td>
                        <td>{{ $row->jumuiya_name }}</td>
                        <td>{{ $row->kanda_name }}</td>
                        <td>{{ $row->sacraments }}</td>
                        <td class="text-center">{{ $row->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ db_trans('no_members_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection