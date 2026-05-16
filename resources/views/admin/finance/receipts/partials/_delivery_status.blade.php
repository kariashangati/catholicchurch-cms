@php
    $deliveryStatus = $receipt->delivery_status ?? 'pending';
    $deliveryClass = match($deliveryStatus) {
        'sent' => 'receipt-status-issued',
        'opened' => 'receipt-status-pending',
        'downloaded' => 'receipt-status-downloaded',
        'failed', 'exception' => 'receipt-status-exception',
        default => 'receipt-status-pending',
    };
@endphp

<span class="receipt-status-badge {{ $deliveryClass }}">
    {{ db_trans('receipts.delivery_statuses.' . $deliveryStatus) }}
</span>
