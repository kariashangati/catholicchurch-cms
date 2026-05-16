@php
    $statusValue = strtolower((string) ($status ?? 'unknown'));

    $statusClasses = [
        'pending_issue' => 'warning',
        'issued' => 'primary',
        'printed' => 'success',
        'sms_sent' => 'info',
        'downloaded' => 'success',
        'reprinted' => 'secondary',
        'voided' => 'dark',
        'exception' => 'danger',
    ];

    $badgeClass = $statusClasses[$statusValue] ?? 'light';
@endphp

<span class="badge text-bg-{{ $badgeClass }} receipt-status-badge">
    {{ db_trans('receipts.statuses.' . $statusValue) }}
</span>
