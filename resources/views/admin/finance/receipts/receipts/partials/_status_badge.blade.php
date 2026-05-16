@php
    $status = $status ?? ($receipt->status ?? 'issued');
    $classes = match($status) {
        'issued' => 'receipt-status-issued',
        'pending', 'pending_issue' => 'receipt-status-pending',
        'downloaded', 'delivered', 'verified' => 'receipt-status-downloaded',
        'exception', 'failed', 'voided' => 'receipt-status-exception',
        default => 'receipt-status-neutral',
    };
@endphp

<span class="receipt-status-badge {{ $classes }}">
    {{ db_trans('receipts.statuses.' . $status) }}
</span>
