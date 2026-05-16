@extends('layouts.admin')

@section('title', db_trans('access_notification_templates'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/access-control.css') }}">
@endpush

@section('content')
    <div class="d-flex flex-column gap-4">
        <div class="p-4 rounded-4 text-white" style="background:linear-gradient(135deg,#0f172a 0%,#1e293b 35%,#312e81 100%);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2">{{ db_trans('access_notification_templates') }}</h2>
                    <p class="mb-0 text-white-50">{{ db_trans('manage_locale_based_sms_templates_for_access_notifications') }}</p>
                </div>
                <a href="{{ route('system-access.dashboard') }}" class="btn btn-light">{{ db_trans('access_control_center') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('system-access.templates.update') }}">
            @csrf
            @method('PUT')

            <div class="d-flex flex-column gap-4">
                @foreach($templates as $index => $template)
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-body p-4">
                            <input type="hidden" name="templates[{{ $index }}][id]" value="{{ $template->id }}">

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $template->name }}</h5>
                                    <div class="text-muted">
                                        {{ $template->code }} • {{ strtoupper($template->locale) }} • {{ strtoupper($template->channel) }}
                                    </div>
                                </div>

                                <div style="min-width:180px;">
                                    <label class="form-label">{{ db_trans('status') }}</label>
                                    <select name="templates[{{ $index }}][is_active]" class="form-select">
                                        <option value="1" @selected($template->is_active)>{{ db_trans('active') }}</option>
                                        <option value="0" @selected(!$template->is_active)>{{ db_trans('inactive') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ db_trans('template_name') }}</label>
                                <input type="text" name="templates[{{ $index }}][name]" value="{{ old("templates.$index.name", $template->name) }}" class="form-control">
                            </div>

                            <div>
                                <label class="form-label">{{ db_trans('message') }}</label>
                                <textarea name="templates[{{ $index }}][message]" rows="6" class="form-control">{{ old("templates.$index.message", $template->message) }}</textarea>
                            </div>

                            <div class="small text-muted mt-3">
                                {{ db_trans('available_tokens') }}:
                                <code>@{{ name }}, @{{ email }}, @{{ password }}, @{{ role_name }}, @{{ login_url }}, @{{ church_name }}</code>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary px-4" type="submit">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
                <a href="{{ route('system-access.dashboard') }}" class="btn btn-outline-secondary px-4">
                    {{ db_trans('cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/access-control.js') }}"></script>
@endpush