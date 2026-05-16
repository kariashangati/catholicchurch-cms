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
                    <th class="text-center" style="width: 4%;">#</th>
                    <th style="width: 19%;">{{ db_trans('member') }}</th>
                    <th style="width: 15%;">{{ db_trans('familia') }}</th>
                    <th style="width: 15%;">{{ db_trans('jumuiya') }}</th>
                    <th style="width: 9%;">{{ db_trans('gender') }}</th>
                    <th class="text-center" style="width: 8%;">{{ db_trans('baptized') }}</th>
                    <th class="text-center" style="width: 8%;">{{ db_trans('communion') }}</th>
                    <th class="text-center" style="width: 8%;">{{ db_trans('confirmation') }}</th>
                    <th class="text-center" style="width: 7%;">{{ db_trans('married') }}</th>
                    <th class="text-center" style="width: 7%;">{{ db_trans('receiving_eucharist') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->sn }}</td>
                        <td>
                            {{ $row->member }}
                            <br>
                            <span class="muted">{{ $row->member_code }}</span>
                        </td>
                        <td>{{ $row->familia }}</td>
                        <td>{{ $row->jumuiya }}</td>
                        <td>{{ $row->gender }}</td>
                        <td class="text-center">{{ $row->baptized }}</td>
                        <td class="text-center">{{ $row->communion }}</td>
                        <td class="text-center">{{ $row->confirmation }}</td>
                        <td class="text-center">{{ $row->married }}</td>
                        <td class="text-center">{{ $row->eucharist }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">{{ db_trans('no_data_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection