<!doctype html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: 80mm 127mm; margin: 4mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color:#111; font-size: 10px; }
        .receipt-page { page-break-after: always; width: 72mm; min-height:119mm; padding: 2mm; }
        .center { text-align:center; }
        .logo { width: 14mm; height: 14mm; object-fit: contain; }
        .church { font-size: 12px; font-weight: 700; color:#003399; text-transform: uppercase; }
        .line { border-top: 1px dashed #111; margin: 4px 0; }
        .row { width:100%; margin: 2px 0; }
        .label { font-weight:700; }
        .title { font-weight:700; text-align:center; font-size: 11px; margin: 4px 0; }
        table { width:100%; border-collapse:collapse; margin-top:4px; }
        th,td { border-bottom:1px solid #ddd; padding:2px; font-size:8.5px; text-align:left; }
        .amount { font-size: 13px; font-weight:700; }
        .qr-box { width: 24mm; height: 24mm; margin: 0 auto 2px auto; overflow: hidden; text-align: center; }
        .qr-box svg { width: 24mm !important; height: 24mm !important; }
        .qr-box img { width: 24mm !important; height: 24mm !important; object-fit: contain; }
        .qr-box table, .qr-box div { margin-left: auto !important; margin-right: auto !important; }
        .verify-note { font-size: 8px; margin-top: 1px; }
        .footer { text-align:center; font-weight:700; font-size:9px; }
    </style>
</head>
<body>
@foreach($receipts as $receipt)
    @php
        $items = collect(data_get($receipt->meta, 'source_items', []));
        $recipient = data_get($receipt->meta, 'recipient_name') ?: ($receipt->member?->full_name ?? $receipt->member?->name ?? $receipt->jumuiya?->name ?? $receipt->kanda?->name ?? '-');
        $layout = $receipt->receipt_layout ?: 'mwanajumuiya';
        $layoutLabel = $layout === 'kanda' ? db_trans('receipt_layout_kanda') : ($layout === 'jumuiya' ? db_trans('receipt_layout_jumuiya') : db_trans('receipt_layout_member'));
        $typeLabel = $receipt->receipt_type === 'zaka'
            ? db_trans('tithes')
            : ($receipt->contributionType?->name ?? data_get($items->first(), 'contribution_type_name', db_trans('contributions')));
    @endphp
    <div class="receipt-page">
        <div class="center">
            @if(!empty($branding['logo']))<img src="{{ $branding['logo'] }}" class="logo">@endif
            <div class="church">{{ $branding['diocese_name'] ?: $branding['site_name'] }}</div>
            <div>{{ $branding['site_name'] }}</div>
            @if($branding['address'])<div>{{ $branding['address'] }}</div>@endif
            @if($branding['phone'])<div>{{ db_trans('phone') }}: {{ $branding['phone'] }}</div>@endif
            @if($branding['email'])<div>{{ db_trans('email') }}: {{ $branding['email'] }}</div>@endif
        </div>
        <div class="line"></div>
        <div class="title">{{ db_trans('receipt') }}</div>
        <div class="row"><span class="label">{{ db_trans('receipt_no') }}:</span> {{ $receipt->receipt_no }}</div>
        <div class="row"><span class="label">{{ db_trans('receipt_layout') }}:</span> {{ $layoutLabel }}</div>
        <div class="row"><span class="label">{{ db_trans('recipient') }}:</span> {{ $recipient }}</div>
        <div class="row"><span class="label">{{ db_trans('kanda') }}:</span> {{ $receipt->kanda?->name ?? data_get($items->first(), 'kanda_name', '-') }}</div>
        @if($layout !== 'kanda')
            <div class="row"><span class="label">{{ db_trans('jumuiya') }}:</span> {{ $receipt->jumuiya?->name ?? data_get($items->first(), 'jumuiya_name', '-') }}</div>
        @endif
        <div class="line"></div>
        <div class="row"><span class="label">{{ db_trans('type') }}:</span> {{ $typeLabel }}</div>
        <div class="row amount"><span class="label">{{ db_trans('amount') }}:</span> TZS {{ number_format((float)$receipt->amount, 2) }}</div>
        <div class="row"><span class="label">{{ db_trans('period') }}:</span> {{ $receipt->period_month ? str_pad($receipt->period_month, 2, '0', STR_PAD_LEFT).'/' : '' }}{{ $receipt->period_year }}</div>
        <div class="row"><span class="label">{{ db_trans('date') }}:</span> {{ optional($receipt->issued_at)->format('d/m/Y H:i') }}</div>
        @if($items->count() > 1)
            <table>
                <thead><tr><th>{{ db_trans('date') }}</th><th>{{ db_trans('type') }}</th><th>{{ db_trans('amount') }}</th></tr></thead>
                <tbody>
                @foreach($items->take(8) as $item)
                    <tr>
                        <td>{{ !empty($item['contribution_date']) ? \Carbon\Carbon::parse($item['contribution_date'])->format('d/m') : '-' }}</td>
                        <td>{{ $item['contribution_type_name'] ?? $typeLabel }}</td>
                        <td>{{ number_format((float)($item['amount'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
                @if($items->count() > 8)
                    <tr><td colspan="2">{{ db_trans('records') }}</td><td>{{ number_format($items->count()) }}</td></tr>
                @endif
                </tbody>
            </table>
        @endif
        <div class="line"></div>
        <div class="center">
            @if(!empty($qrHtmls) && $qrHtmls->get($receipt->id))
                <div class="qr-box">{!! $qrHtmls->get($receipt->id) !!}</div>
            @endif
            <div>{{ db_trans('verification_code') }}: {{ $receipt->verification_code }}</div>
            <div class="verify-note">{{ db_trans('scan_to_verify_receipt') }}</div>
        </div>
        <div class="line"></div>
        <div class="footer">{{ $branding['footer_text'] ?: 'TUMSIFU YESU KRISTO' }}</div>
    </div>
@endforeach
</body>
</html>
