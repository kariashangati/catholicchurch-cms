@php
    use Illuminate\Support\Str;

    $settings = $settings ?? [];
    $church = $church ?? null;
    $navItems = $navItems ?? [];

    $siteName = optional($church)->centre_name
        ?: ($settings['site.name'] ?? 'ECCLESIA');

    $siteTagline = $settings['site.tagline'] ?? 'Contemporary Worship Community';
    $siteLogo = $settings['site.logo'] ?? null;

    $logoUrl = ! empty($siteLogo)
        ? asset(ltrim($siteLogo, '/'))
        : asset('uploads/frontend/logo/logo2.jpeg');

    $showContact = ($settings['nav.show_contact'] ?? '1') === '1';
    $showGiving = ($settings['nav.show_giving'] ?? '1') === '1';
    $showLogin = ($settings['nav.show_login'] ?? '1') === '1';

    $contactLabel = db_trans('nav.contact');
    $giveLabel = db_trans('nav.give');
    $loginLabel = db_trans('nav.login');

    $currentLocale = app()->getLocale();

    $languages = [
        [
            'code' => 'en',
            'label' => 'English',
            'short' => 'EN',
            'flag' => asset('admin/images/flags/gb.png'),
        ],
        [
            'code' => 'sw',
            'label' => 'Kiswahili',
            'short' => 'SW',
            'flag' => asset('admin/images/flags/tz.png'),
        ],
    ];

    $homeActive = request()->routeIs('frontend.home');

    // New desktop navigation structure
    $desktopMenuRoutes = [
        'frontend.home',
        'frontend.kandas',
        'frontend.jumuiyas',
        'frontend.mass-schedules',
        'frontend.announcements',
        'frontend.projects',
    ];

    $desktopMoreItems = [];

    foreach ($navItems as $item) {
        if (! ($item['show'] ?? false)) {
            continue;
        }

        $desktopMoreItems[] = $item;
    }

    $moreActive = collect($desktopMoreItems)->contains(fn ($item) => request()->routeIs($item['route']))
        || request()->routeIs('frontend.contact')
        || request()->routeIs('login');

    $dashboardUrl = '/dashboard';

    if (auth()->check()) {
        $user = auth()->user();

        if (! empty($user->jumuiya_id) && Route::has('jumuiyas.show')) {
            $dashboardUrl = route('jumuiyas.show', $user->jumuiya_id);
        } elseif (! empty($user->kanda_id) && Route::has('kandas.show')) {
            $dashboardUrl = route('kandas.show', $user->kanda_id);
        } elseif (Route::has('dashboard')) {
            $dashboardUrl = route('dashboard');
        }
    }
@endphp

<nav class="navbar" id="navbar">
    <div class="nav-container">

        <a href="{{ route('frontend.home') }}"
           class="logo-wrap {{ $homeActive ? 'active-logo' : '' }}"
           aria-label="{{ $siteName }}">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="nav-logo">
            <div class="logo-text-wrap">
            
            </div>
        </a>

        <!-- Desktop navigation: only "More" dropdown -->
        <div class="nav-links">
            <div class="nav-dropdown {{ $moreActive ? 'active' : '' }}">
                <button type="button"
                        class="nav-link-item nav-dropdown-toggle {{ $moreActive ? 'active' : '' }}"
                        aria-expanded="false">
                    <span>{{ db_trans('nav.more') }}</span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="nav-dropdown-menu">
                    @foreach($desktopMoreItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    @if($showContact)
                        <a href="{{ route('frontend.contact') }}"
                           class="{{ request()->routeIs('frontend.contact') ? 'active' : '' }}">
                            {{ $contactLabel }}
                        </a>
                    @endif

                    @if($showLogin)
                        @auth
                            <a href="{{ $dashboardUrl }}">
                                {{ Str::limit(auth()->user()->name, 18) }}
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="{{ request()->routeIs('login') ? 'active' : '' }}">
                                {{ $loginLabel }}
                            </a>
                        @endauth
                    @endif

                    <!-- Language switcher inside More dropdown -->
                    @foreach($languages as $language)
                        <a href="{{ route('lang.switch', $language['code']) }}"
                           class="{{ $currentLocale === $language['code'] ? 'active' : '' }}">
                            <span>
                                <img src="{{ $language['flag'] }}" class="lang-flag" alt="{{ $language['label'] }}">
                                {{ $language['label'] }}
                            </span>

                            @if($currentLocale === $language['code'])
                                <i class="bi bi-check2"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Nav actions: only Give button (language removed) -->
        <div class="nav-actions">
            @if($showGiving)
                <a href="{{ route('frontend.giving') }}"
                   class="btn-outline nav-pill-btn {{ request()->routeIs('frontend.giving') ? 'active-pill' : '' }}">
                    {{ $giveLabel }}
                </a>
            @endif
        </div>

        <button class="menu-icon" id="menuIcon" type="button" aria-label="{{ db_trans('open_menu') }}">
            <i class="bi bi-list"></i>
        </button>
    </div>
</nav>

<div class="mobile-drawer-backdrop" id="mobileDrawerBackdrop"></div>

<aside class="mobile-drawer" id="mobileDrawer" aria-hidden="true">
    <div class="mobile-drawer-top">
        <a href="{{ route('frontend.home') }}" class="mobile-brand">
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="mobile-nav-logo">
            <span>
                <span class="mobile-brand-title">{{ $siteName }}</span>
                <small class="mobile-brand-subtitle">{{ $siteTagline }}</small>
            </span>
        </a>

        <button type="button" class="close-drawer" id="closeDrawer" aria-label="{{ db_trans('close_menu') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="mobile-language-block">
        <div class="mobile-language-title">{{ db_trans('language_switcher') }}</div>
        <div class="mobile-language-switch">
            @foreach($languages as $language)
                <a href="{{ route('lang.switch', $language['code']) }}"
                   class="mobile-language-option {{ $currentLocale === $language['code'] ? 'active' : '' }}">
                    <span class="mobile-language-left">
                        <img src="{{ $language['flag'] }}" class="lang-flag" alt="{{ $language['label'] }}">
                        <span>{{ $language['label'] }}</span>
                    </span>

                    @if($currentLocale === $language['code'])
                        <i class="bi bi-check2"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="mobile-nav-links">
        @foreach($desktopMoreItems as $item)
            <a href="{{ route($item['route']) }}"
               class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        @if($showContact)
            <a href="{{ route('frontend.contact') }}"
               class="{{ request()->routeIs('frontend.contact') ? 'active' : '' }}">
                {{ $contactLabel }}
            </a>
        @endif

        @if($showGiving)
            <a href="{{ route('frontend.giving') }}" class="mobile-give-btn">
                {{ $giveLabel }}
            </a>
        @endif

        @if($showLogin)
            @auth
                <a href="{{ $dashboardUrl }}" class="mobile-login-btn">
                    {{ Str::limit(auth()->user()->name, 20) }}
                </a>
            @else
                <a href="{{ route('login') }}" class="mobile-login-btn">
                    {{ $loginLabel }}
                </a>
            @endauth
        @endif
    </div>
</aside>