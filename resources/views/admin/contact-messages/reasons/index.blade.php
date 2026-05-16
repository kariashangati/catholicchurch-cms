@extends('layouts.admin')

@section('title', db_trans('contact_reasons'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/contact-messages-v1.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="contact-msg-shell">
    <section class="contact-msg-hero mb-4">
        <div>
            <span class="contact-msg-eyebrow"><i class="fas fa-list"></i> {{ db_trans('contact_reasons') }}</span>
            <h2 class="contact-msg-title">{{ db_trans('manage_contact_reasons') }}</h2>
            <p class="contact-msg-subtitle">{{ db_trans('manage_contact_reasons_subtitle') }}</p>
        </div>
        <div class="contact-msg-hero-actions">
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createReasonModal"><i class="fas fa-plus me-1"></i>{{ db_trans('add_reason') }}</button>
            <a href="{{ route('contact-messages.index') }}" class="btn btn-outline-light btn-sm"><i class="fas fa-inbox me-1"></i>{{ db_trans('contact_messages') }}</a>
        </div>
    </section>

    <div class="contact-msg-panel">
        <div class="table-responsive">
            <table id="contactReasonsTable" class="table contact-msg-table align-middle w-100">
                <thead><tr><th>{{ db_trans('name') }}</th><th>{{ db_trans('display_order') }}</th><th>{{ db_trans('status') }}</th><th>{{ db_trans('actions') }}</th></tr></thead>
                <tbody>
                @foreach($reasons as $reason)
                    <tr>
                        <td><strong>{{ $reason->name }}</strong></td>
                        <td>{{ $reason->display_order }}</td>
                        <td><span class="contact-status-badge {{ $reason->is_active ? 'status-imejibiwa' : 'status-imefungwa' }}">{{ $reason->is_active ? db_trans('active') : db_trans('inactive') }}</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editReason{{ $reason->id }}"><i class="fas fa-pen"></i></button>
                                <form method="POST" action="{{ route('contact-messages.reasons.toggle', $reason) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-warning"><i class="fas fa-toggle-on"></i></button></form>
                                <form method="POST" action="{{ route('contact-messages.reasons.destroy', $reason) }}" class="js-confirm-delete d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createReasonModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content contact-msg-modal">
<form method="POST" action="{{ route('contact-messages.reasons.store') }}">@csrf
    <div class="modal-header"><h5 class="modal-title">{{ db_trans('add_reason') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">{{ db_trans('name') }}</label><input name="name" class="form-control mb-3" required>
        <label class="form-label">{{ db_trans('display_order') }}</label><input type="number" name="display_order" class="form-control mb-3" value="0" min="0">
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="reasonActiveNew"><label class="form-check-label" for="reasonActiveNew">{{ db_trans('active') }}</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">{{ db_trans('save') }}</button></div>
</form>
</div></div></div>

@foreach($reasons as $reason)
<div class="modal fade" id="editReason{{ $reason->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content contact-msg-modal">
<form method="POST" action="{{ route('contact-messages.reasons.update', $reason) }}">@csrf @method('PUT')
    <div class="modal-header"><h5 class="modal-title">{{ db_trans('edit_reason') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">{{ db_trans('name') }}</label><input name="name" class="form-control mb-3" value="{{ $reason->name }}" required>
        <label class="form-label">{{ db_trans('display_order') }}</label><input type="number" name="display_order" class="form-control mb-3" value="{{ $reason->display_order }}" min="0">
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($reason->is_active) id="reasonActive{{ $reason->id }}"><label class="form-check-label" for="reasonActive{{ $reason->id }}">{{ db_trans('active') }}</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">{{ db_trans('save_changes') }}</button></div>
</form>
</div></div></div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('admin/js/contact-messages-v1.js') }}"></script>
@endpush
