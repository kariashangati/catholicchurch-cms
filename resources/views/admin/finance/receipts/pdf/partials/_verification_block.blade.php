<div class="section">
    <strong>{{ db_trans('receipts.verification') }}</strong><br>
    {{ db_trans('receipts.code') }}: {{ $receipt->verification_code ?? '-' }}<br>

    @if(!empty($verification_url))
        {{ db_trans('receipts.labels.verification_link') }}: {{ $verification_url }}<br>
    @endif

    @if(!empty($qr_svg))
        <div style="margin-top: 12px;">
            {!! $qr_svg !!}
        </div>
    @endif
</div>