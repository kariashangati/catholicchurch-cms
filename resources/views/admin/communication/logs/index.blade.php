@extends('layouts.admin')

@section('title', db_trans('communication.logs'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/communication-center.css') }}">
@endpush

@section('content')
<div class="container-fluid communication-center-page">
    <div class="cc-subhero mb-4">
        <div>
            <span class="cc-kicker">{{ db_trans('communication.audit_trail') }}</span>
            <h1 class="cc-subhero__title">{{ db_trans('communication.logs') }}</h1>
            <p class="cc-subhero__text">{{ db_trans('communication.logs_desc') }}</p>
        </div>
    </div>

    <div class="cc-panel mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ db_trans('communication.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ db_trans('communication.all') }}</option>
                    @foreach(['pending','queued','sending','sent','failed','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ db_trans('communication.delivery_status') }}</label>
                <select name="delivery_status" class="form-select">
                    <option value="">{{ db_trans('communication.all') }}</option>
                    @foreach(['pending','delivered','undelivered','rejected','expired'] as $delivery)
                        <option value="{{ $delivery }}" @selected(request('delivery_status') === $delivery)>{{ ucfirst($delivery) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ db_trans('communication.phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ request('phone') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ db_trans('communication.campaign') }}</label>
                <select name="campaign_id" class="form-select">
                    <option value="">{{ db_trans('communication.all') }}</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" @selected((string) request('campaign_id') === (string) $campaign->id)>{{ $campaign->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">{{ db_trans('communication.filter') }}</button>
                <a href="{{ route('admin.communication.logs.index') }}" class="btn btn-light">{{ db_trans('communication.reset') }}</a>
            </div>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="cc-panel">
                <div class="cc-panel__header">
                    <div>
                        <h5>{{ db_trans('communication.message_logs') }}</h5>
                        <p>{{ db_trans('communication.message_logs_desc') }}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table cc-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('communication.recipient') }}</th>
                                <th>{{ db_trans('communication.status') }}</th>
                                <th>{{ db_trans('communication.delivery_status') }}</th>
                                <th>{{ db_trans('communication.segments') }}</th>
                                <th>{{ db_trans('communication.created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                                <tr>
                                    <td>
                                        <strong>{{ $message->recipient_name ?: db_trans('communication.unknown_recipient') }}</strong>
                                        <div class="small text-muted">{{ $message->recipient_phone_normalized ?: $message->recipient_phone }}</div>
                                        @if($message->error_message)
                                            <div class="small text-danger mt-1">{{ $message->error_message }}</div>
                                        @endif
                                    </td>
                                    <td><span class="cc-status cc-status--{{ str_replace('_', '-', $message->status) }}">{{ ucfirst(str_replace('_', ' ', $message->status)) }}</span></td>
                                    <td>
                                        @if($message->delivery_status)
                                            <span class="cc-status cc-status--{{ str_replace('_', '-', $message->delivery_status) }}">{{ ucfirst(str_replace('_', ' ', $message->delivery_status)) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($message->segment_count ?? 0) }}</td>
                                    <td>{{ optional($message->created_at)->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">{{ db_trans('communication.no_logs_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $messages->links() }}</div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="cc-panel">
                <div class="cc-panel__header">
                    <div>
                        <h5>{{ db_trans('communication.audit_activity') }}</h5>
                        <p>{{ db_trans('communication.audit_activity_desc') }}</p>
                    </div>
                </div>

                <div class="cc-list">
                    @forelse($auditLogs as $log)
                        <div class="cc-list-item">
                            <div>
                                <strong>{{ ucfirst($log->action) }}</strong>
                                <div class="small text-muted">{{ $log->log_type }}</div>
                            </div>
                            <div class="text-end small text-muted">
                                {{ optional($log->created_at)->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ db_trans('communication.no_audit_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
