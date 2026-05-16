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
                <div class="ui-panel-icon"><i class="fas fa-people-roof"></i></div>
            </div>

            <div class="row g-3 mb-4 mt-3">
                <div class="col-md-3"><div class="ui-mini-stat"><div class="label">{{ db_trans('members') }}</div><div class="value">{{ $summary['members_total'] }}</div></div></div>
                <div class="col-md-3"><div class="ui-mini-stat"><div class="label">{{ db_trans('baptized') }}</div><div class="value">{{ $summary['baptized'] }}</div></div></div>
                <div class="col-md-3"><div class="ui-mini-stat"><div class="label">{{ db_trans('communion') }}</div><div class="value">{{ $summary['communion'] }}</div></div></div>
                <div class="col-md-3"><div class="ui-mini-stat"><div class="label">{{ db_trans('confirmation') }}</div><div class="value">{{ $summary['confirmation'] }}</div></div></div>
            </div>
        </div>
    </div>

    <div class="card ui-table-card border-0 mb-4">
        <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('parents_and_heads') }}</h5></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('phone') }}</th>
                        <th>{{ db_trans('family_role') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                        <th>{{ db_trans('married') }}</th>
                        <th>{{ db_trans('marriage_type') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($parents as $parent)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $parent->full_name }}</td>
                        <td>{{ $parent->phone ?: '-' }}</td>
                        <td>{{ $parent->family_role }}</td>
                        <td>{{ ucfirst((string) $parent->gender) }}</td>
                        <td>{{ $parent->is_married ? db_trans('married') : db_trans('not_married') }}</td>
                        <td>{{ $parent->marriage_type ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_data_found') }}</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card ui-table-card border-0">
        <div class="card-header"><h5 class="ui-panel-title mb-1">{{ db_trans('family_members') }}</h5></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('gender') }}</th>
                        <th>{{ db_trans('baptized') }}</th>
                        <th>{{ db_trans('communion') }}</th>
                        <th>{{ db_trans('confirmation') }}</th>
                        <th>{{ db_trans('receiving_eucharist') }}</th>
                        <th>{{ db_trans('married') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($members as $member)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ ucfirst((string) $member->gender) }}</td>
                        <td><span class="{{ $member->is_baptized ? 'ui-yes' : 'ui-no' }}">{{ $member->is_baptized ? db_trans('yes') : db_trans('no') }}</span></td>
                        <td><span class="{{ $member->has_communion ? 'ui-yes' : 'ui-no' }}">{{ $member->has_communion ? db_trans('yes') : db_trans('no') }}</span></td>
                        <td><span class="{{ $member->has_confirmation ? 'ui-yes' : 'ui-no' }}">{{ $member->has_confirmation ? db_trans('yes') : db_trans('no') }}</span></td>
                        <td><span class="{{ $member->receives_eucharist ? 'ui-yes' : 'ui-no' }}">{{ $member->receives_eucharist ? db_trans('yes') : db_trans('no') }}</span></td>
                        <td><span class="{{ $member->is_married ? 'ui-yes' : 'ui-no' }}">{{ $member->is_married ? db_trans('yes') : db_trans('no') }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="ui-empty-state"><i class="fas fa-inbox"></i><div>{{ db_trans('no_data_found') }}</div></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection