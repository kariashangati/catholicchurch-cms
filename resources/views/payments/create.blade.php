<!DOCTYPE html>
<html>
<head>
    <title>M-Pesa Checkout Test</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px;
        }

        .card {
            max-width: 480px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
            color: #222;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 13px;
            background: #e60000;
            border: none;
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #c80000;
        }

        .error {
            background: #ffe8e8;
            color: #a10000;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .hint {
            color: #666;
            font-size: 14px;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>M-Pesa Checkout Test</h2>

    <p>Enter customer phone number and amount. Customer will receive a USSD push prompt.</p>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf

        <label>Customer Phone</label>
        <input
            type="text"
            name="phone"
            value="{{ old('phone') }}"
            placeholder="255712345678"
            required
        >

        <div class="hint">Use international format. Example: 255712345678</div>

        <label>Amount</label>
        <input
            type="number"
            name="amount"
            value="{{ old('amount', 1000) }}"
            min="100"
            step="1"
            required
        >

        <button type="submit">Send M-Pesa Push</button>
    </form>
</div>

</body>
</html>