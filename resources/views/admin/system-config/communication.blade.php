@extends('layouts.admin')

@section('title', db_trans('communication_configuration'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/system-config.css') }}">
@endpush

@section('content')
    <div class="syscfg-shell">
        <div class="syscfg-hero syscfg-hero-communication">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="syscfg-hero-title">{{ db_trans('communication_configuration') }}</h2>
                    <p class="syscfg-hero-subtitle">{{ db_trans('manage_mail_and_sms_provider_configuration') }}</p>
                </div>

                <a href="{{ route('system-config.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('system_configuration_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-config.communication.update') }}">
            @csrf
            @method('PUT')

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="syscfg-panel-head">
                        <div>
                            <h4 class="syscfg-panel-title">{{ db_trans('sms_configuration') }}</h4>
                            <p class="syscfg-panel-subtitle">{{ db_trans('configure_beem_sms_access_and_connectivity') }}</p>
                        </div>

                        <span class="syscfg-panel-badge">
                            {{ db_trans('balance') }}:
                            @if(($smsBalance['success'] ?? false) === true)
                                {{ $smsBalance['balance'] ?? '-' }}
                            @else
                                {{ db_trans('unavailable') }}
                            @endif
                        </span>
                    </div>

                    <div class="row g-3">
                        @foreach($smsSettings as $key => $config)
                            <div class="col-lg-6">
                                <label class="form-label">{{ $key }}</label>
                                <input
                                    type="text"
                                    name="{{ $key }}"
                                    value="{{ old($key, $config['value']) }}"
                                    class="form-control"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card syscfg-form-card">
                <div class="card-body">
                    <div class="syscfg-panel-head">
                        <div>
                            <h4 class="syscfg-panel-title">{{ db_trans('mail_configuration') }}</h4>
                            <p class="syscfg-panel-subtitle">{{ db_trans('configure_mail_transport_and_sender_details') }}</p>
                        </div>
                        <span class="syscfg-panel-badge">{{ db_trans('mail') }}</span>
                    </div>

                    <div class="row g-3">
                        @foreach($mailSettings as $key => $config)
                            <div class="col-lg-6">
                                <label class="form-label">{{ $key }}</label>
                                <input
                                    type="text"
                                    name="{{ $key }}"
                                    value="{{ old($key, $config['value']) }}"
                                    class="form-control"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="syscfg-sticky-actions d-flex justify-content-end gap-2">
                <a href="{{ route('system-config.dashboard') }}" class="btn btn-outline-secondary px-4">
                    {{ db_trans('cancel') }}
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/system-config.js') }}"></script>
@endpush