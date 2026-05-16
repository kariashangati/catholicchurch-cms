
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $receipt->receipt_no ?? 'Receipt' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { padding: 20px; }
        .section { margin-bottom: 15px; }
        .title { font-size: 16px; font-weight: bold; }
        .divider { border-bottom: 1px solid #ccc; margin: 10px 0; }
    </style>
</head>
<body>
<div class="container">
    @include('admin.finance.receipts.pdf.partials._header')
    <div class="divider"></div>

    @include('admin.finance.receipts.pdf.partials._recipient_block')
    @include('admin.finance.receipts.pdf.partials._transaction_block')

    <div class="divider"></div>
    @include('admin.finance.receipts.pdf.partials._verification_block')

    <div class="divider"></div>
    @include('admin.finance.receipts.pdf.partials._footer')
</div>
</body>
</html>
