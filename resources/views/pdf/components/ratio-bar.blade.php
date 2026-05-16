@php
    $primary = (float) ($primary ?? 0);
    $secondary = (float) ($secondary ?? 0);
    $total = $primary + $secondary;
    $primaryPercent = $total > 0 ? round(($primary / $total) * 100, 1) : 0;
    $secondaryPercent = $total > 0 ? round(($secondary / $total) * 100, 1) : 0;
@endphp

<div class="panel-card">
    <div class="card-value" style="font-size: 26px; text-align: center; margin-bottom: 8px;">
        {{ $totalFormatted ?? number_format($total, 2) }}
    </div>
    <div class="muted" style="text-align: center; margin-bottom: 16px;">{{ $totalLabel ?? (db_trans('grand_total') ?: 'Grand Total') }}</div>

    <div style="display:flex; justify-content:space-between; font-size: 11px; margin-bottom: 4px;">
        <span>{{ $primaryLabel ?? 'Primary' }} {{ $primaryPercent }}%</span>
        <span>{{ $primaryFormatted ?? number_format($primary, 2) }}</span>
    </div>
    <div class="bar-track" style="height: 16px; margin-bottom: 4px;">
        <div style="display:flex; width:100%; height:16px;">
            <div class="bar-fill bar-fill-blue" style="width: {{ $primaryPercent }}%; border-radius: 999px 0 0 999px;"></div>
            <div class="bar-fill bar-fill-slate" style="width: {{ $secondaryPercent }}%; border-radius: 0 999px 999px 0;"></div>
        </div>
    </div>
    <div style="display:flex; justify-content:space-between; font-size: 11px;">
        <span>{{ $secondaryLabel ?? 'Secondary' }} {{ $secondaryPercent }}%</span>
        <span>{{ $secondaryFormatted ?? number_format($secondary, 2) }}</span>
    </div>
</div>
