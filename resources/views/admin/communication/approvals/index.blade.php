@extends('layouts.admin')

@section('title', db_trans('communication.approvals.title'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/admin/css/communication-center-phase12.css') }}">
@endpush

@section('content')
<div class="container-fluid py-3">
    @include('admin.communication.partials.hero', [
        'kicker' => db_trans('communication.approvals.kicker'),
        'title' => db_trans('communication.approvals.title'),
        'subtitle' => db_trans('communication.approvals.subtitle'),
        'stats' => [
            ['label' => db_trans('communication.approvals.pending_count'), 'value' => $campaigns->total()],
        ],
    ])

    <div class="card cc-panel border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="cc-table-head">
                        <tr>
                            <th>{{ db_trans('communication.common.title') }}</th>
                            <th>{{ db_trans('communication.common.type') }}</th>
                            <th>{{ db_trans('communication.common.recipients') }}</th>
                            <th>{{ db_trans('communication.common.status') }}</th>
                            <th>{{ db_trans('communication.common.scheduled_at') }}</th>
                            <th class="text-end">{{ db_trans('communication.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $campaign->title }}</div>
                                <small class="text-muted">#{{ $campaign->id }}</small>
                            </td>
                            <td><span class="cc-badge">{{ $campaign->type }}</span></td>
                            <td>{{ number_format($campaign->total_recipients ?? 0) }}</td>
                            <td><span class="cc-badge cc-badge-warning">{{ $campaign->approval_status ?? $campaign->status }}</span></td>
                            <td>{{ optional($campaign->scheduled_at)->format('d M Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('admin.communication.approvals.approve', $campaign) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="bi bi-check2-circle me-1"></i>{{ db_trans('communication.common.approve') }}</button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#reject-{{ $campaign->id }}">
                                        <i class="bi bi-x-circle me-1"></i>{{ db_trans('communication.common.reject') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="reject-{{ $campaign->id }}">
                            <td colspan="6" class="bg-light">
                                <div class="p-3">
                                    <form method="POST" action="{{ route('admin.communication.approvals.reject', $campaign) }}" class="row g-2">
                                        @csrf
                                        <div class="col-md-10">
                                            <textarea name="approval_notes" class="form-control" rows="2" placeholder="{{ db_trans('communication.approvals.reject_reason') }}" required></textarea>
                                        </div>
                                        <div class="col-md-2 d-grid">
                                            <button class="btn btn-danger">{{ db_trans('communication.common.confirm_reject') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">{{ db_trans('communication.approvals.empty') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $campaigns->links() }}
    </div>
</div>
@endsection
