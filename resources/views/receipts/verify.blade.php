<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ db_trans('verify_receipt') }}</title>
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/contributions-v4.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; min-height: 100vh; }
        .receipt-public-shell { max-width: 980px; margin: 0 auto; padding: 32px 16px; }
        .receipt-public-hero { border-radius: 28px; padding: 34px; color: #fff; background: linear-gradient(135deg, #6d28d9 0%, #9333ea 50%, #a855f7 100%); position: relative; overflow: hidden; box-shadow: 0 20px 45px rgba(109, 40, 217, .18); }
        .receipt-public-hero:before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 24px 24px; opacity: .5; }
        .receipt-public-hero > * { position: relative; z-index: 1; }
        .receipt-public-badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.22); font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .receipt-public-card { border: 0; border-radius: 24px; box-shadow: 0 16px 40px rgba(15,23,42,.08); }
        .receipt-result-valid { background: #dcfce7; color: #166534; }
        .receipt-result-invalid { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
<main class="receipt-public-shell">
    <section class="receipt-public-hero mb-4">
        <span class="receipt-public-badge"><i class="fas fa-qrcode"></i>{{ db_trans('verify_receipt') }}</span>
        <h1 class="mt-3 mb-0 fw-bold">{{ db_trans('verify_receipt') }}</h1>
    </section>

    <section class="card receipt-public-card mb-4">
        <div class="card-body p-4 p-lg-5">
            <form method="GET" action="{{ Route::has('receipt.verify.search') ? route('receipt.verify.search') : url('/receipts/verify/search') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">{{ db_trans('receipt_no') }}</label>
                    <input type="text" name="receipt_no" class="form-control" value="{{ request('receipt_no') }}" placeholder="{{ db_trans('receipt_no') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">{{ db_trans('verification_code') }}</label>
                    <input type="text" name="verification_code" class="form-control" value="{{ request('verification_code') }}" placeholder="{{ db_trans('verification_code') }}">
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-semibold"><i class="fas fa-search me-1"></i>{{ db_trans('verify') }}</button>
                </div>
            </form>
        </div>
    </section>

    @if(!empty($result))
        <section class="card receipt-public-card">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <span class="badge rounded-pill px-4 py-3 {{ $result['valid'] ? 'receipt-result-valid' : 'receipt-result-invalid' }}">
                        {{ $result['message'] }}
                    </span>
                </div>

                @if(!empty($receipt))
                    <div class="row g-3">
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('receipt_no') }}</div><div class="fw-bold">{{ $receipt->receipt_no ?? '—' }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('verification_code') }}</div><div class="fw-bold">{{ $receipt->verification_code ?? '—' }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('amount') }}</div><div class="fw-bold">TZS {{ number_format((float) ($receipt->amount ?? 0), 2) }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('recipient') }}</div><div class="fw-bold">{{ $receipt->recipient_name ?? $receipt->member?->full_name ?? $receipt->member?->name ?? data_get($receipt->meta, 'recipient_name', '—') }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('issued_at') }}</div><div class="fw-bold">{{ optional($receipt->issued_at ?? $receipt->created_at)->format('d M Y H:i') ?: '—' }}</div></div></div>
                        <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><div class="text-muted small text-uppercase fw-bold">{{ db_trans('status') }}</div><div class="fw-bold">{{ db_trans($receipt->status ?? 'issued') }}</div></div></div>
                    </div>
                @endif
            </div>
        </section>
    @endif
</main>
</body>
</html>
