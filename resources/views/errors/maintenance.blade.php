<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? db_trans('maintenance_mode') }}</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f4f7fb;color:#0f172a;padding:24px}
        .card{width:min(720px,100%);background:#fff;border-radius:28px;padding:34px;box-shadow:0 24px 70px rgba(15,23,42,.14);border:1px solid #e2e8f0;text-align:center}
        .badge{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;background:#ede9fe;color:#6d28d9;font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:.04em}
        h1{font-size:clamp(28px,4vw,44px);margin:18px 0 12px;line-height:1.1}
        p{font-size:17px;line-height:1.7;color:#475569;margin:0 auto;max-width:560px}
    </style>
</head>
<body>
    <main class="card">
        <div class="badge">{{ db_trans('maintenance_mode') }}</div>
        <h1>{{ $title ?? db_trans('maintenance_mode') }}</h1>
        <p>{{ $message ?? db_trans('system_is_currently_under_maintenance') }}</p>
    </main>
</body>
</html>
