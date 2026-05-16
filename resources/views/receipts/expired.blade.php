<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ db_trans('receipt_link_expired') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center">
                        <div class="display-6 mb-3">⚠️</div>
                        <h1 class="h3 mb-3">{{ db_trans('receipt_link_expired') }}</h1>
                        <p class="text-muted mb-0">{{ $message ?? db_trans('receipt_not_found') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
