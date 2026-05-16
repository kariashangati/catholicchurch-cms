@php
    use Illuminate\Support\Facades\Route;

    $settingsService = app(\App\Services\SystemConfig\SiteSettingsService::class);

    $siteName = $settingsService->get('site.name', config('app.name', 'ChurchMS'));
    $siteLogo = $settingsService->get('site.logo');
    $siteTagline = $settingsService->get('site.tagline', 'Mfumo wa Usimamizi wa Kanisa');
    $footerNote = $settingsService->get('footer.tagline', 'Imejengwa kwa uendeshaji wa huduma ulio wazi na salama');

    $logoUrl = $siteLogo ? asset(ltrim($siteLogo, '/')) : asset('uploads/frontend/logo/logo2.jpeg');
    $homeUrl = Route::has('frontend.home') ? route('frontend.home') : url('/');
    $loginUrl = Route::has('login') ? route('login') : url('/login');
    $contactUrl = Route::has('frontend.contact') ? route('frontend.contact') : url('/');

    $code = $code ?? ($exception?->getStatusCode() ?? 500);
    $title = $title ?? 'Hitilafu imetokea';
    $message = $message ?? 'Samahani, kumetokea changamoto wakati wa kufungua ukurasa huu.';
    $hint = $hint ?? 'Unaweza kurudi mwanzo au kujaribu tena baada ya muda mfupi.';
@endphp

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $code }} | {{ $title }} | {{ $siteName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/error-ui.css') }}">
</head>
<body class="error-page-body">
    <main class="error-shell">
        <section class="error-card" aria-labelledby="error-title">
            <div class="error-card__glow error-card__glow--one"></div>
            <div class="error-card__glow error-card__glow--two"></div>

            <header class="error-header">
                <a href="{{ $homeUrl }}" class="error-brand" aria-label="{{ $siteName }}">
                    <span class="error-brand__logo-wrap">
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="error-brand__logo">
                    </span>
                    <span class="error-brand__text">
                        <strong>{{ $siteName }}</strong>
                        <small>{{ $siteTagline }}</small>
                    </span>
                </a>

                <span class="error-chip">Msaada wa Mfumo</span>
            </header>

            <div class="error-content">
                <div class="error-code-wrap" aria-hidden="true">
                    <span class="error-code">{{ $code }}</span>
                    <span class="error-code-shadow">{{ $code }}</span>
                </div>

                <div class="error-copy">
                    <span class="error-eyebrow">Samahani, kuna changamoto</span>
                    <h1 id="error-title">{{ $title }}</h1>
                    <p class="error-message">{{ $message }}</p>
                    <p class="error-hint">{{ $hint }}</p>

                    <div class="error-actions">
                        <a href="{{ $homeUrl }}" class="error-btn error-btn--primary">Rudi Mwanzo</a>
                        <button type="button" class="error-btn error-btn--ghost" onclick="window.history.back()">Rudi Nyuma</button>
                        <a href="{{ $contactUrl }}" class="error-btn error-btn--light">Wasiliana Nasi</a>
                    </div>
                </div>
            </div>

            <footer class="error-footer">
                <span>© {{ date('Y') }} {{ $siteName }}</span>
                <span>{{ $footerNote }}</span>
                <a href="{{ $loginUrl }}">Ingia kwenye mfumo</a>
            </footer>
        </section>
    </main>
</body>
</html>
