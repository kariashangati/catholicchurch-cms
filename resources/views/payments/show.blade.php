<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .card {
            max-width: 760px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        .success {
            background: #e7ffe8;
            color: #076b12;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .error {
            background: #ffe8e8;
            color: #a10000;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        td {
            border-bottom: 1px solid #eee;
            padding: 10px;
        }

        td:first-child {
            font-weight: bold;
            width: 240px;
        }

        pre {
            background: #111;
            color: #00ff80;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 13px;
        }

        a.button {
            display: inline-block;
            margin-top: 20px;
            background: #e60000;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Payment Status</h2>

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <table>
        <tr>
            <td>Status</td>
            <td>{{ strtoupper($payment->status) }}</td>
        </tr>
        <tr>
            <td>Payment Reference</td>
            <td>{{ $payment->payment_reference }}</td>
        </tr>
        <tr>
            <td>Phone</td>
            <td>{{ $payment->phone }}</td>
        </tr>
        <tr>
            <td>Amount</td>
            <td>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
        </tr>
        <tr>
            <td>Conversation ID</td>
            <td>{{ $payment->conversation_id ?? '-' }}</td>
        </tr>
        <tr>
            <td>Transaction ID</td>
            <td>{{ $payment->transaction_id ?? '-' }}</td>
        </tr>
    </table>

    <h3>Request Payload</h3>
    <pre>{{ json_encode($payment->request_payload, JSON_PRETTY_PRINT) }}</pre>

    <h3>M-Pesa Response</h3>
    <pre>{{ json_encode($payment->mpesa_response, JSON_PRETTY_PRINT) }}</pre>

    <a href="{{ route('payments.create') }}" class="button">New Payment</a>
</div>

</body>
</html>