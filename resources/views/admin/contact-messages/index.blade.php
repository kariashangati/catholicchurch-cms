@extends('layouts.admin')

@section('title', db_trans('contact_messages'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contact-messages-v1.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<div class="contact-msg-shell">
    <section class="contact-msg-hero mb-4">
        <div>
            <span class="contact-msg-eyebrow"><i class="fas fa-inbox"></i> {{ db_trans('contact_messages') }}</span>
            <h2 class="contact-msg-title">{{ db_trans('manage_contact_messages') }}</h2>
            <p class="contact-msg-subtitle">{{ db_trans('manage_contact_messages_subtitle') }}</p>
        </div>
        <div class="contact-msg-hero-actions">
            @can('contact-messages.dashboard.view')
                <a href="{{ route('contact-messages.dashboard') }}" class="btn btn-light btn-sm"><i class="fas fa-chart-pie me-1"></i>{{ db_trans('overview') }}</a>
            @endcan
            @can('contact-messages.reasons.manage')
                <a href="{{ route('contact-messages.reasons.index') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-list me-1"></i>{{ db_trans('contact_reasons') }}</a>
            @endcan
        </div>
    </section>

    <div class="contact-msg-filter-card mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-3">
                <label class="form-label">{{ db_trans('search') }}</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="{{ db_trans('search_name_phone_message') }}">
            </div>
            <div class="col-lg-2">
                <label class="form-label">{{ db_trans('status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ db_trans('all') }}</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ db_trans('contact_status_'.$status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">{{ db_trans('reason') }}</label>
                <select name="reason_id" class="form-select">
                    <option value="">{{ db_trans('all') }}</option>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason->id }}" @selected((string)($filters['reason_id'] ?? '') === (string)$reason->id)>{{ $reason->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">{{ db_trans('priority') }}</label>
                <select name="priority" class="form-select">
                    <option value="">{{ db_trans('all') }}</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ db_trans('priority_'.$priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i>{{ db_trans('filter') }}</button>
                <a href="{{ route('contact-messages.index') }}" class="btn btn-outline-secondary">{{ db_trans('reset') }}</a>
            </div>
        </form>
    </div>

    <div class="contact-msg-panel">
        <div class="contact-msg-panel-head">
            <div>
                <h5>{{ db_trans('contact_messages') }}</h5>
                <p class="mb-0 text-muted small">{{ db_trans('contact_messages_table_subtitle') }}</p>
            </div>
        </div>
        <div class="table-responsive">
            <table id="contactMessagesTable" class="table contact-msg-table align-middle w-100">
                <thead>
                    <tr>
                        <th>{{ db_trans('date') }}</th>
                        <th>{{ db_trans('sender') }}</th>
                        <th>{{ db_trans('reason') }}</th>
                        <th>{{ db_trans('message') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th>{{ db_trans('priority') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                        <tr>
                            <td data-order="{{ optional($message->created_at)->timestamp }}">
                                <strong>{{ optional($message->created_at)->format('d M Y') }}</strong><br>
                                <span class="small text-muted">{{ optional($message->created_at)->format('h:i A') }}</span>
                            </td>
                            <td>
                                <strong>{{ $message->full_name }}</strong>
                                <div class="small text-muted">{{ $message->phone ?: '-' }}</div>
                                <div class="small text-muted">{{ $message->email ?: '-' }}</div>
                            </td>
                            <td>
                                {{ $message->reason?->name ?: '-' }}
                                <div class="small text-muted">{{ $message->kanda?->name ?: '-' }} / {{ $message->jumuiya?->name ?: '-' }}</div>
                            </td>
                            <td><div class="contact-msg-preview">{{ $message->message }}</div></td>
                            <td><span class="contact-status-badge status-{{ $message->status }}">{{ $message->status_label }}</span></td>
                            <td><span class="contact-priority-badge priority-{{ $message->priority }}">{{ $message->priority_label }}</span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewContactMessage{{ $message->id }}"><i class="fas fa-eye"></i></button>
                                    @can('contact-messages.answer')
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#answerContactMessage{{ $message->id }}"><i class="fas fa-reply"></i></button>
                                    @endcan
                                    @can('contact-messages.assign')
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#assignContactMessage{{ $message->id }}"><i class="fas fa-user-tag"></i></button>
                                    @endcan
                                    @can('contact-messages.update')
                                        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#statusContactMessage{{ $message->id }}"><i class="fas fa-check-circle"></i></button>
                                    @endcan
                                    @can('contact-messages.delete')
                                        <form method="POST" action="{{ route('contact-messages.destroy', $message) }}" class="js-confirm-delete d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($messages as $message)
    <div class="modal fade" id="viewContactMessage{{ $message->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered"><div class="modal-content contact-msg-modal">
            <div class="modal-header"><h5 class="modal-title">{{ db_trans('contact_message_details') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><div class="contact-detail-item"><span>{{ db_trans('name') }}</span><strong>{{ $message->full_name }}</strong></div></div>
                    <div class="col-md-6"><div class="contact-detail-item"><span>{{ db_trans('phone') }}</span><strong>{{ $message->phone ?: '-' }}</strong></div></div>
                    <div class="col-md-6"><div class="contact-detail-item"><span>{{ db_trans('email') }}</span><strong>{{ $message->email ?: '-' }}</strong></div></div>
                    <div class="col-md-6"><div class="contact-detail-item"><span>{{ db_trans('reason') }}</span><strong>{{ $message->reason?->name ?: '-' }}</strong></div></div>
                </div>
                <div class="contact-message-box mb-3">{{ $message->message }}</div>
                @if($message->replies->isNotEmpty())
                    <h6>{{ db_trans('replies') }}</h6>
                    @foreach($message->replies as $reply)
                        <div class="contact-reply-box mb-2">
                            <div class="d-flex justify-content-between gap-2"><strong>{{ $reply->user?->name ?: '-' }}</strong><span class="small text-muted">{{ optional($reply->created_at)->format('d M Y h:i A') }}</span></div>
                            <div>{{ $reply->reply_body }}</div>
                            <div class="small text-muted mt-1">SMS: {{ $reply->sms_status ?: '-' }} {{ $reply->sms_error ? ' - '.$reply->sms_error : '' }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('close') }}</button></div>
        </div></div>
    </div>

    <div class="modal fade" id="answerContactMessage{{ $message->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content contact-msg-modal">
            <form method="POST" action="{{ route('contact-messages.answer', $message) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ db_trans('answer_contact_message') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-light border"><strong>{{ $message->full_name }}</strong><br>{{ $message->message }}</div>
                    <label class="form-label">{{ db_trans('reply_message') }}</label>
                    <textarea name="reply_body" rows="5" class="form-control" required>{{ old('reply_body') }}</textarea>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="send_sms" value="1" id="sendSms{{ $message->id }}" checked @disabled(blank($message->phone))>
                        <label class="form-check-label" for="sendSms{{ $message->id }}">{{ db_trans('send_sms_reply') }} {{ blank($message->phone) ? '('.db_trans('phone_missing').')' : '' }}</label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="close_after_answer" value="1" id="closeAfter{{ $message->id }}">
                        <label class="form-check-label" for="closeAfter{{ $message->id }}">{{ db_trans('close_after_answer') }}</label>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>{{ db_trans('send_answer') }}</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="assignContactMessage{{ $message->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content contact-msg-modal">
            <form method="POST" action="{{ route('contact-messages.assign', $message) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ db_trans('assign_and_prioritize') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ db_trans('assign_to') }}</label>
                    <select name="assigned_to" class="form-select mb-3"><option value="">{{ db_trans('not_assigned') }}</option>@foreach($admins as $admin)<option value="{{ $admin->id }}" @selected($message->assigned_to == $admin->id)>{{ $admin->name }}</option>@endforeach</select>
                    <label class="form-label">{{ db_trans('priority') }}</label>
                    <select name="priority" class="form-select mb-3">@foreach($priorities as $priority)<option value="{{ $priority }}" @selected($message->priority === $priority)>{{ db_trans('priority_'.$priority) }}</option>@endforeach</select>
                    <label class="form-label">{{ db_trans('admin_note') }}</label>
                    <textarea name="admin_note" class="form-control" rows="3">{{ $message->admin_note }}</textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">{{ db_trans('save_changes') }}</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="statusContactMessage{{ $message->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content contact-msg-modal">
            <form method="POST" action="{{ route('contact-messages.change-status', $message) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">{{ db_trans('change_status') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select mb-3">@foreach($statuses as $status)<option value="{{ $status }}" @selected($message->status === $status)>{{ db_trans('contact_status_'.$status) }}</option>@endforeach</select>
                    <label class="form-label">{{ db_trans('note') }}</label>
                    <textarea name="note" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">{{ db_trans('update_status') }}</button></div>
            </form>
        </div></div>
    </div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('admin/js/contact-messages-v1.js') }}"></script>
@endpush
