<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ db_trans('receipt') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5">
                        <h1 class="h3 mb-4">{{ db_trans('receipt') }}</h1>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6"><strong>{{ db_trans('receipt_no') }}:</strong> {{ $receipt->receipt_no ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ db_trans('amount') }}:</strong> TZS {{ number_format((float) ($receipt->amount ?? 0), 2) }}</div>
                            <div class="col-md-6"><strong>{{ db_trans('recipient') }}:</strong> {{ $receipt->recipient_name ?? $receipt->member?->full_name ?? $receipt->member?->name ?? '—' }}</div>
                            <div class="col-md-6"><strong>{{ db_trans('issued_at') }}:</strong> {{ optional($receipt->issued_at ?? $receipt->created_at)->format('d M Y H:i') ?: '—' }}</div>
                        </div>
                        <a href="{{ route('receipt.access.download', $token) }}" class="btn btn-primary">
                            {{ db_trans('download_receipt') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
