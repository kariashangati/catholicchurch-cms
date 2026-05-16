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
                    <th style="width: 22%;">{{ db_trans('member') }}</th>
                    <th style="width: 14%;">{{ db_trans('member_code') }}</th>
                    <th style="width: 18%;">{{ db_trans('familia') }}</th>
                    <th style="width: 16%;">{{ db_trans('jumuiya') }}</th>
                    <th style="width: 12%;">{{ db_trans('status') }}</th>
                    <th class="text-center" style="width: 13%;">{{ db_trans('started_on') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->sn }}</td>
                        <td>{{ $row->member }}</td>
                        <td>{{ $row->member_code }}</td>
                        <td>{{ $row->familia }}</td>
                        <td>{{ $row->jumuiya }}</td>
                        <td>{{ $row->status }}</td>
                        <td class="text-center">{{ $row->started_on }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection