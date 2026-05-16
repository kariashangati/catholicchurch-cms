@extends('layouts.admin')

@section('title', db_trans('receipts.exceptions.title'))

@section('content')
<div class="receipt-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 mb-1">{{ db_trans('receipts.exceptions.title') }}</h1>
            <p class="mb-0 opacity-75">{{ db_trans('receipts.exceptions.subtitle') }}</p>
        </div>
        <a href="{{ route('receipts.history') }}" class="btn btn-light">
            {{ db_trans('receipts.actions.back_to_history') }}
        </a>
    </div>
</div>

<div class="receipt-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ db_trans('receipts.exceptions.list') }}</span>
        <span class="badge bg-danger">{{ $exceptions->total() ?? $exceptions->count() }}</span>
    </div>
    <div class="card-body p-0">
        @if(($exceptions->count() ?? 0) > 0)
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ db_trans('receipts.labels.receipt_no') }}</th>
                            <th>{{ db_trans('receipts.labels.exception_type') }}</th>
                            <th>{{ db_trans('receipts.labels.message') }}</th>
                            <th>{{ db_trans('receipts.labels.happened_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exceptions as $exception)
                            <tr>
                                <td>{{ optional($exception->receiptIssue)->receipt_no ?? '-' }}</td>
                                <td>{{ $exception->exception_type ?? '-' }}</td>
                                <td>{{ $exception->message ?? '-' }}</td>
                                <td>{{ optional($exception->happened_at ?? $exception->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($exceptions, 'links'))
                <div class="p-3">
                    {{ $exceptions->links() }}
                </div>
            @endif
        @else
            <div class="receipt-empty-state">
                <h5>{{ db_trans('receipts.exceptions.empty_title') }}</h5>
                <p class="mb-0">{{ db_trans('receipts.exceptions.empty_message') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
