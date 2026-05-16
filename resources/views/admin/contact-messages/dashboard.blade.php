@extends('layouts.admin')

@section('title', db_trans('contact_messages_center'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contact-messages-v1.css') }}">
@endpush

@section('content')
<div class="contact-msg-shell">
    <section class="contact-msg-hero mb-4">
        <div>
            <span class="contact-msg-eyebrow"><i class="fas fa-envelope-open-text"></i> {{ db_trans('contact_messages') }}</span>
            <h2 class="contact-msg-title">{{ db_trans('contact_messages_center') }}</h2>
            <p class="contact-msg-subtitle">{{ db_trans('contact_messages_center_subtitle') }}</p>
        </div>
        <div class="contact-msg-hero-actions">
            @can('contact-messages.view')
                <a href="{{ route('contact-messages.index') }}" class="btn btn-light btn-sm"><i class="fas fa-inbox me-1"></i>{{ db_trans('view_messages') }}</a>
            @endcan
            @can('contact-messages.reasons.manage')
                <a href="{{ route('contact-messages.reasons.index') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-list me-1"></i>{{ db_trans('contact_reasons') }}</a>
            @endcan
        </div>
    </section>

    <div class="row g-4 mb-4">
        @foreach($kpis as $kpi)
            <div class="col-xl-3 col-md-6">
                <div class="contact-msg-kpi tone-{{ $kpi['tone'] }}">
                    <div class="contact-msg-kpi-icon"><i class="{{ $kpi['icon'] }}"></i></div>
                    <div>
                        <div class="contact-msg-kpi-label">{{ $kpi['title'] }}</div>
                        <div class="contact-msg-kpi-value">{{ number_format($kpi['value']) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="contact-msg-panel h-100">
                <div class="contact-msg-panel-head">
                    <h5>{{ db_trans('contact_messages_trend') }}</h5>
                    <span class="contact-msg-chip">{{ db_trans('monthly') }}</span>
                </div>
                <div class="contact-msg-chart"><canvas id="contactMessagesTrendChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="contact-msg-panel h-100">
                <div class="contact-msg-panel-head">
                    <h5>{{ db_trans('contact_status_distribution') }}</h5>
                    <span class="contact-msg-chip">{{ db_trans('status') }}</span>
                </div>
                <div class="contact-msg-chart"><canvas id="contactMessagesStatusChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="contact-msg-panel h-100">
                <div class="contact-msg-panel-head">
                    <h5>{{ db_trans('latest_contact_messages') }}</h5>
                    <a href="{{ route('contact-messages.index') }}" class="btn btn-sm btn-outline-primary">{{ db_trans('view_all') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table contact-msg-table align-middle mb-0">
                        <thead><tr><th>{{ db_trans('name') }}</th><th>{{ db_trans('reason') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('date') }}</th></tr></thead>
                        <tbody>
                        @forelse($latest as $message)
                            <tr>
                                <td><strong>{{ $message->full_name }}</strong><div class="small text-muted">{{ $message->phone ?: $message->email }}</div></td>
                                <td>{{ $message->reason?->name ?: '-' }}</td>
                                <td><span class="contact-status-badge status-{{ $message->status }}">{{ $message->status_label }}</span></td>
                                <td>{{ optional($message->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="contact-msg-panel h-100">
                <div class="contact-msg-panel-head">
                    <h5>{{ db_trans('pending_contact_messages') }}</h5>
                    <span class="contact-msg-chip">{{ db_trans('needs_action') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table contact-msg-table align-middle mb-0">
                        <thead><tr><th>{{ db_trans('name') }}</th><th>{{ db_trans('phone') }}</th><th>{{ db_trans('reason') }}</th><th>{{ db_trans('date') }}</th></tr></thead>
                        <tbody>
                        @forelse($pending as $message)
                            <tr>
                                <td><strong>{{ $message->full_name }}</strong></td>
                                <td>{{ $message->phone ?: '-' }}</td>
                                <td>{{ $message->reason?->name ?: '-' }}</td>
                                <td>{{ optional($message->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
window.contactMessagesCharts = {
    monthly: @json($monthlyChart),
    status: @json($statusChart)
};
</script>
<script src="{{ asset('admin/js/contact-messages-v1.js') }}"></script>
@endpush
