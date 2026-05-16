@php
    $settingsService = app(\App\Services\SystemConfig\SiteSettingsService::class);

    $siteName = $settingsService->get('site.name', config('app.name', 'ChurchMS'));
    $siteLogo = $settingsService->get('site.logo');
    $footerNote = $settingsService->get(
        'footer.tagline',
        db_trans('built_for_clear_secure_ministry_operations')
    );

    $logoUrl = $siteLogo ? asset($siteLogo) : null;
    $siteInitial = strtoupper(mb_substr($siteName, 0, 1));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-success-message" content="{{ session('success') ?: session('status') }}">
    <meta name="app-error-message" content="{{ session('error', '') }}">

    <title>{{ $siteName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('admin/css/auth-ui.css') }}">

    <style>
        /*
         * Clean login layout.
         * Keeps only logo, language switcher, login form, and footer.
         */
        .auth-login-clean .auth-brand-panel {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .auth-login-clean .auth-brand-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            width: 100%;
        }

        .auth-login-clean .auth-brand-top .auth-lang-switch {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        .auth-login-clean .auth-brand-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-login-clean .auth-brand-logo-only {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 220px;
            min-height: 135px;
            padding: 1rem;
            border-radius: 28px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .16);
            box-shadow: 0 24px 55px rgba(0, 0, 0, .28);
            text-decoration: none;
        }

        .auth-login-clean .auth-login-logo-img {
            width: 190px !important;
            height: 105px !important;
            max-width: 190px !important;
            max-height: 105px !important;
            object-fit: contain !important;
            display: block;
            border-radius: 18px;
            background: #ffffff;
            padding: .45rem;
        }

        .auth-login-clean .auth-login-logo-fallback {
            width: 96px !important;
            height: 96px !important;
            border-radius: 24px !important;
            font-size: 2.4rem !important;
        }

        .auth-login-clean .auth-brand-copy,
        .auth-login-clean .auth-feature-list,
        .auth-login-clean .auth-brand__title,
        .auth-login-clean .auth-brand__subtitle {
            display: none !important;
        }

        .auth-login-clean .auth-brand-footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .12);
            padding-top: 1.25rem;
        }

        .auth-login-clean .auth-brand-footer span {
            display: inline-block !important;
        }

        .auth-login-clean .auth-mobile-top {
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-align: center;
        }

        .auth-login-clean .auth-mobile-logo-only {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 170px;
            min-height: 96px;
            padding: .65rem;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 14px 32px rgba(15, 23, 42, .12);
            text-decoration: none;
        }

        .auth-login-clean .auth-login-mobile-logo-img {
            width: 150px !important;
            height: 82px !important;
            max-width: 150px !important;
            max-height: 82px !important;
            object-fit: contain !important;
            display: block;
            border-radius: 14px;
        }

        .auth-login-clean .auth-mobile-top .auth-brand__title,
        .auth-login-clean .auth-mobile-top .auth-brand__subtitle {
            display: none !important;
        }

        @media (max-width: 991.98px) {
            .auth-login-clean .auth-brand-panel {
                display: none !important;
            }

            .auth-login-clean .auth-content-panel {
                padding-top: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .auth-login-clean .auth-mobile-logo-only {
                width: 150px;
                min-height: 86px;
            }

            .auth-login-clean .auth-login-mobile-logo-img {
                width: 132px !important;
                height: 72px !important;
            }
        }
    </style>
</head>

<body class="auth-page-body auth-login-clean">
<div id="appLoader" class="page-loader">
    <div class="page-loader-inner">
        <div class="loader"></div>
        <div class="page-loader-text">{{ db_trans('loading') }}</div>
    </div>
</div>

<div class="auth-shell">
    <div class="auth-shell__bg auth-shell__bg--one"></div>
    <div class="auth-shell__bg auth-shell__bg--two"></div>
    <div class="auth-shell__bg auth-shell__bg--grid"></div>

    <div class="auth-layout">
        <aside class="auth-brand-panel">

            <div class="auth-brand-top">
                <div class="auth-lang-switch" aria-label="{{ db_trans('language_switcher') }}">
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="auth-lang-pill {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
                        <img src="{{ asset('admin/images/flags/gb.png') }}" alt="English" class="auth-lang-pill__flag">
                        <span>EN</span>
                    </a>

                    <a href="{{ route('lang.switch', 'sw') }}"
                       class="auth-lang-pill {{ app()->getLocale() === 'sw' ? 'is-active is-sw' : '' }}">
                        <img src="{{ asset('admin/images/flags/tz.png') }}" alt="Swahili" class="auth-lang-pill__flag">
                        <span>SW</span>
                    </a>
                </div>
            </div>

            <div class="auth-brand-center">
                <a href="{{ url('/') }}" class="auth-brand-logo-only" aria-label="{{ $siteName }}">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="auth-login-logo-img">
                    @else
                        <span class="auth-brand__logo auth-login-logo-fallback">{{ $siteInitial }}</span>
                    @endif
                </a>
            </div>

            <div class="auth-brand-footer">
                <span>© {{ date('Y') }} {{ $siteName }}</span>
                <span>{{ $footerNote }}</span>
            </div>
        </aside>

        <main class="auth-content-panel">
            <div class="auth-mobile-top">
                <a href="{{ url('/') }}" class="auth-mobile-logo-only" aria-label="{{ $siteName }}">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="auth-login-mobile-logo-img">
                    @else
                        <span class="auth-brand__logo auth-login-logo-fallback">{{ $siteInitial }}</span>
                    @endif
                </a>

                <div class="auth-lang-switch">
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="auth-lang-pill {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">
                        <img src="{{ asset('admin/images/flags/gb.png') }}" alt="English" class="auth-lang-pill__flag">
                        <span>EN</span>
                    </a>

                    <a href="{{ route('lang.switch', 'sw') }}"
                       class="auth-lang-pill {{ app()->getLocale() === 'sw' ? 'is-active is-sw' : '' }}">
                        <img src="{{ asset('admin/images/flags/tz.png') }}" alt="Swahili" class="auth-lang-pill__flag">
                        <span>SW</span>
                    </a>
                </div>
            </div>

            {{ $slot }}
        </main>
    </div>
</div>

<script>
window.addEventListener('load', function () {
    const loader = document.getElementById('appLoader');
    if (!loader) return;

    setTimeout(function () {
        loader.classList.add('is-hidden');
    }, 250);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const successMessage = document.querySelector('meta[name="app-success-message"]')?.getAttribute('content') || '';
    const errorMessage = document.querySelector('meta[name="app-error-message"]')?.getAttribute('content') || '';
    const hasErrors = @json($errors->any());
    const errorMessages = @json($errors->all());

    if (successMessage.trim() !== '') {
        Swal.fire({
            icon: 'success',
            title: @json(db_trans('success')),
            text: successMessage,
            confirmButtonColor: '#7c3aed',
            timer: 2600,
            timerProgressBar: true
        });
    }

    if (errorMessage.trim() !== '') {
        Swal.fire({
            icon: 'error',
            title: @json(db_trans('error')),
            text: errorMessage,
            confirmButtonColor: '#7c3aed'
        });
    }

    if (hasErrors && errorMessages.length > 0) {
        Swal.fire({
            icon: 'error',
            title: @json(db_trans('please_fix_the_following_errors')),
            html: '<div style="text-align:left;">' + errorMessages.map(function (message) {
                return '• ' + message;
            }).join('<br>') + '</div>',
            confirmButtonColor: '#7c3aed'
        });
    }
});
</script>

<script src="{{ asset('admin/js/auth-ui.js') }}"></script>
</body>
</html>