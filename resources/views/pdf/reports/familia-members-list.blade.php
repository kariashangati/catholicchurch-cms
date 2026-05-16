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
                    <th class="text-center" style="width: 6%;">#</th>
                    <th style="width: 24%;">{{ db_trans('member') }}</th>
                    <th style="width: 12%;">{{ db_trans('gender') }}</th>
                    <th style="width: 16%;">{{ db_trans('family_role') }}</th>
                    <th style="width: 15%;">{{ db_trans('phone') }}</th>
                    <th style="width: 18%;">{{ db_trans('sacraments') }}</th>
                    <th class="text-center" style="width: 9%;">{{ db_trans('status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->sn }}</td>
                        <td>{{ $row->member }}</td>
                        <td>{{ $row->gender }}</td>
                        <td>{{ $row->family_role }}</td>
                        <td>{{ $row->phone }}</td>
                        <td>{{ $row->sacraments }}</td>
                        <td class="text-center">{{ $row->status }}</td>
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