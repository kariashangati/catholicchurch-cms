@extends('layouts.admin')

@section('title', db_trans('communication.schedules'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/communication-schedules-v1.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="cc-schedule-hero mb-4">
        <div class="cc-schedule-hero__overlay"></div>
        <div class="cc-schedule-hero__content">
            <div>
                <div class="cc-kicker">
                    <i class="bi bi-calendar2-check"></i>
                    {{ db_trans('communication.scheduling_center') }}
                </div>
                <h1 class="cc-title">{{ db_trans('communication.scheduled_campaigns') }}</h1>
                <p class="cc-subtitle">
                    {{ db_trans('communication.manage_send_later_campaigns_from_one_place') }}
                </p>
            </div>
            <div class="cc-pill">
                <i class="bi bi-clock-history"></i>
                {{ $campaigns->count() }} {{ db_trans('communication.scheduled') }}
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="cc-kpi cc-kpi--purple">
                <div class="cc-kpi__icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="cc-kpi__label">{{ db_trans('communication.total_scheduled') }}</div>
                    <div class="cc-kpi__value">{{ $campaigns->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cc-kpi cc-kpi--blue">
                <div class="cc-kpi__icon"><i class="bi bi-send-check"></i></div>
                <div>
                    <div class="cc-kpi__label">{{ db_trans('communication.ready_to_launch') }}</div>
                    <div class="cc-kpi__value">{{ $campaigns->where('scheduled_at', '<=', now())->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="cc-kpi cc-kpi--emerald">
                <div class="cc-kpi__icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="cc-kpi__label">{{ db_trans('communication.upcoming') }}</div>
                    <div class="cc-kpi__value">{{ $campaigns->where('scheduled_at', '>', now())->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="cc-panel">
        <div class="cc-panel__head">
            <div>
                <h3 class="cc-panel__title">{{ db_trans('communication.scheduled_campaigns') }}</h3>
                <p class="cc-panel__text">{{ db_trans('communication.review_reschedule_or_cancel_campaigns') }}</p>
            </div>
            <div class="cc-panel__badge">
                <i class="bi bi-broadcast"></i>
                {{ db_trans('communication.queue_ready') }}
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle cc-table">
                <thead>
                <tr>
                    <th>{{ db_trans('communication.title') }}</th>
                    <th>{{ db_trans('communication.type') }}</th>
                    <th>{{ db_trans('communication.recipients') }}</th>
                    <th>{{ db_trans('communication.scheduled_at') }}</th>
                    <th>{{ db_trans('communication.status') }}</th>
                    <th class="text-end">{{ db_trans('communication.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($campaigns as $campaign)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $campaign->title }}</div>
                            <div class="text-muted small">{{ $campaign->channel }}</div>
                        </td>
                        <td>{{ $campaign->type }}</td>
                        <td>{{ number_format((int) $campaign->total_recipients) }}</td>
                        <td>
                            <div>{{ optional($campaign->scheduled_at)->format('d M Y H:i') }}</div>
                            <div class="text-muted small">{{ $campaign->schedule_timezone ?? config('app.timezone') }}</div>
                        </td>
                        <td>
                            <span class="cc-status cc-status--scheduled">
                                <i class="bi bi-clock"></i> {{ ucfirst($campaign->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rescheduleModal{{ $campaign->id }}">
                                <i class="bi bi-pencil-square"></i>
                                {{ db_trans('communication.reschedule') }}
                            </button>

                            <form method="POST"
                                  action="{{ route('admin.communication.campaigns.cancel_schedule', $campaign) }}"
                                  class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-circle"></i>
                                    {{ db_trans('communication.cancel') }}
                                </button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="rescheduleModal{{ $campaign->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content cc-modal">
                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="modal-title fw-bold">{{ db_trans('communication.reschedule_campaign') }}</h5>
                                        <div class="text-muted small">{{ $campaign->title }}</div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('admin.communication.campaigns.reschedule', $campaign) }}">
                                    @csrf
                                    @method('PATCH')
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ db_trans('communication.scheduled_at') }}</label>
                                                <input type="datetime-local"
                                                       name="scheduled_at"
                                                       value="{{ optional($campaign->scheduled_at)->timezone($campaign->schedule_timezone ?? config('app.timezone'))->format('Y-m-d\TH:i') }}"
                                                       class="form-control"
                                                       required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ db_trans('communication.timezone') }}</label>
                                                <input type="text"
                                                       name="schedule_timezone"
                                                       value="{{ $campaign->schedule_timezone ?? config('app.timezone') }}"
                                                       class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">{{ db_trans('communication.notes') }}</label>
                                                <textarea name="schedule_notes" class="form-control" rows="3">{{ $campaign->schedule_notes }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                            {{ db_trans('communication.close') }}
                                        </button>
                                        <button class="btn btn-primary">
                                            <i class="bi bi-calendar2-plus"></i>
                                            {{ db_trans('communication.save_schedule') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="cc-empty">
                                <div class="cc-empty__icon"><i class="bi bi-calendar-x"></i></div>
                                <div class="cc-empty__title">{{ db_trans('communication.no_scheduled_campaigns') }}</div>
                                <div class="cc-empty__text">{{ db_trans('communication.schedule_campaigns_to_see_them_here') }}</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
