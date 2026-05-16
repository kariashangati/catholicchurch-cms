@extends('layouts.admin')

@section('title', $pageTitle)
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
<div class="admin-ui-v4 sacrament-reports-v4 print-friendly">
    <div class="ui-print-actions d-flex flex-wrap gap-2">
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i>{{ db_trans('print') }}
        </button>

        <a href="{{ route('pdf.reports.sacraments.export', request()->query()) }}" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
        </a>

        <a href="{{ route('reports.sacraments.export.excel', request()->query()) }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
        </a>
    </div>

    <div class="card ui-panel border-0 mb-4">
        <div class="card-body">
            <div class="ui-panel-head">
                <div>
                    <h4 class="ui-panel-title mb-0">{{ $pageTitle }}</h4>
                </div>
                <div class="ui-panel-icon"><i class="fas fa-cross"></i></div>
            </div>

            <div class="row g-3 mb-0 mt-3">
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('members') }}</div><div class="value">{{ $summary['members_total'] }}</div></div></div>
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('baptized') }}</div><div class="value">{{ $summary['baptized'] }}</div></div></div>
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('communion') }}</div><div class="value">{{ $summary['communion'] }}</div></div></div>
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('confirmation') }}</div><div class="value">{{ $summary['confirmation'] }}</div></div></div>
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('married') }}</div><div class="value">{{ $summary['married'] }}</div></div></div>
                <div class="col-md-2"><div class="ui-mini-stat"><div class="label">{{ db_trans('receiving_eucharist') }}</div><div class="value">{{ $summary['receives_eucharist'] }}</div></div></div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('summary') }}</h5></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ db_trans('scope') }}</th>
                        <th class="text-end">{{ db_trans('members') }}</th>
                        <th class="text-end">{{ db_trans('baptized') }}</th>
                        <th class="text-end">{{ db_trans('communion') }}</th>
                        <th class="text-end">{{ db_trans('confirmation') }}</th>
                        <th class="text-end">{{ db_trans('married') }}</th>
                        <th class="text-end">{{ db_trans('receiving_eucharist') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="text-end">{{ $row['members_total'] }}</td>
                        <td class="text-end">{{ $row['baptized'] }}</td>
                        <td class="text-end">{{ $row['communion'] }}</td>
                        <td class="text-end">{{ $row['confirmation'] }}</td>
                        <td class="text-end">{{ $row['married'] }}</td>
                        <td class="text-end">{{ $row['receives_eucharist'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_data_found') }}</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('members') }}</h5></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('familia') }}</th>
                        <th>{{ db_trans('jumuiya') }}</th>
                        <th>{{ db_trans('kanda') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->familia?->name }}</td>
                        <td>{{ $member->familia?->jumuiya?->name }}</td>
                        <td>{{ $member->familia?->jumuiya?->kanda?->name }}</td>
                        <td>{{ ucfirst((string) $member->gender) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_data_found') }}</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection