@php
    $authUser = Auth::user();

    $scopeLabel = db_trans('global');

    if ($authUser?->jumuiya_id) {
        $jumuiyaName = optional($authUser->jumuiya)->name;
        $scopeLabel = $jumuiyaName
            ? db_trans('jumuiya') . ': ' . $jumuiyaName
            : db_trans('jumuiya');
    } elseif ($authUser?->kanda_id) {
        $kandaName = optional($authUser->kanda)->name;
        $scopeLabel = $kandaName
            ? db_trans('kanda') . ': ' . $kandaName
            : db_trans('kanda');
    }

    $currentAdminTheme = session('admin_theme', $authUser->admin_theme ?? 'classic');
    $currentAdminTheme = in_array($currentAdminTheme, ['classic', 'light', 'dark'], true) ? $currentAdminTheme : 'classic';

    $adminThemeLabels = [
        'light' => db_trans('theme_light'),
        'classic' => db_trans('theme_classic'),
        'dark' => db_trans('theme_dark'),
    ];
@endphp

<nav class="navbar navbar-expand-lg admin-topbar admin-topbar-clean">
    <div class="container-fluid px-3 px-lg-4">

        {{-- LEFT: only mobile sidebar toggle. --}}
        <div class="d-flex align-items-center gap-3">
            <button class="btn admin-icon-btn d-lg-none" type="button" id="mobileSidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        {{-- RIGHT --}}
        <div class="d-flex align-items-center gap-2 gap-lg-3 ms-auto">

            {{-- LANGUAGE --}}
            <div class="lang-switch-wrap">
                <a href="{{ route('lang.switch', 'en') }}"
                   class="lang-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                    <img src="{{ asset('admin/images/flags/gb.png') }}" class="lang-flag-img" alt="English">
                    <span>EN</span>
                </a>

                <a href="{{ route('lang.switch', 'sw') }}"
                   class="lang-pill {{ app()->getLocale() === 'sw' ? 'active sw' : '' }}">
                    <img src="{{ asset('admin/images/flags/tz.png') }}" class="lang-flag-img" alt="Swahili">
                    <span>SW</span>
                </a>
            </div>

            {{-- THEME SWITCHER --}}
            <div class="dropdown admin-theme-dropdown">
                <button class="admin-icon-btn admin-theme-toggle"
                        type="button"
                        id="adminThemeDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        title="{{ db_trans('theme') }}">
                    <i class="fas fa-adjust"></i>
                    <span class="visually-hidden">{{ db_trans('theme') }}</span>
                </button>

                <div class="dropdown-menu dropdown-menu-end admin-dropdown-menu admin-theme-menu admin-theme-menu-appearance shadow-sm"
                     aria-labelledby="adminThemeDropdown">
                    <div class="admin-theme-appearance-title">
                        {{ db_trans('appearance') }}
                    </div>

                    <form method="POST"
                          action="{{ route('admin.theme.update') }}"
                          class="admin-theme-appearance-form"
                          data-admin-theme-form>
                        @csrf

                        <button type="submit"
                                name="theme"
                                value="light"
                                class="admin-theme-appearance-option {{ $currentAdminTheme === 'light' ? 'active' : '' }}"
                                data-admin-theme-option="light"
                                title="{{ $adminThemeLabels['light'] }}">
                            <span class="admin-theme-appearance-icon">
                                <i class="fas fa-sun"></i>
                            </span>
                            <span>{{ $adminThemeLabels['light'] }}</span>
                        </button>

                        <button type="submit"
                                name="theme"
                                value="classic"
                                class="admin-theme-appearance-option {{ $currentAdminTheme === 'classic' ? 'active' : '' }}"
                                data-admin-theme-option="classic"
                                title="{{ $adminThemeLabels['classic'] }}">
                            <span class="admin-theme-appearance-icon">
                                <i class="fas fa-desktop"></i>
                            </span>
                            <span>{{ $adminThemeLabels['classic'] }}</span>
                        </button>

                        <button type="submit"
                                name="theme"
                                value="dark"
                                class="admin-theme-appearance-option {{ $currentAdminTheme === 'dark' ? 'active' : '' }}"
                                data-admin-theme-option="dark"
                                title="{{ $adminThemeLabels['dark'] }}">
                            <span class="admin-theme-appearance-icon">
                                <i class="fas fa-moon"></i>
                            </span>
                            <span>{{ $adminThemeLabels['dark'] }}</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- QUICK ACTIONS MENU --}}
            @canany([
                'finance.tithes.bulk',
                'receipts.view',
                'receipts.analytics.view',
                'finance.contributions.cash.view',
                'finance.contributions.bank.view',
                'audit.logs.view'
            ])
                <div class="dropdown">
                    <a class="admin-icon-btn"
                       href="#"
                       id="quickActionsDropdown"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fas fa-layer-group"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end communication-dropdown-card shadow-lg p-3"
                         aria-labelledby="quickActionsDropdown">

                        <div class="communication-dropdown-header mb-3">
                            <div class="fw-bold">{{ db_trans('quick_actions_center') }}</div>
                            <small class="text-muted">{{ db_trans('quick_access') }}</small>
                        </div>

                        <div class="communication-grid">
                            @can('finance.tithes.bulk')
                                <a href="{{ url('/finance/tithes/bulk-entry') }}" class="communication-grid-item">
                                    <i class="fas fa-coins"></i>
                                    <span>{{ db_trans('tithes_bulk') }}</span>
                                </a>
                            @endcan

                            @canany(['receipts.view', 'receipts.analytics.view'])
                                <a href="{{ route('receipts.dashboard') }}" class="communication-grid-item">
                                    <i class="fas fa-receipt"></i>
                                    <span>{{ db_trans('receipt_center') }}</span>
                                </a>
                            @endcanany

                            @canany(['finance.contributions.cash.view', 'finance.contributions.bank.view'])
                                <a href="{{ route('finance.contributions.bulk.index') }}" class="communication-grid-item">
                                    <i class="fas fa-donate"></i>
                                    <span>{{ db_trans('bulk_contributions') }}</span>
                                </a>
                            @endcanany

                            @can('audit.logs.view')
                                <a href="{{ route('system.activity-logs.index') }}" class="communication-grid-item">
                                    <i class="fas fa-stream"></i>
                                    <span>{{ db_trans('system_activity_logs') }}</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endcanany

            {{-- COMMUNICATION QUICK MENU --}}
            @canany([
                'communication.view',
                'communication.manage_controls'
            ])
                <div class="dropdown">
                    <a class="admin-icon-btn"
                       href="#"
                       id="communicationDropdown"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fas fa-envelope"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end communication-dropdown-card shadow-lg p-3"
                         aria-labelledby="communicationDropdown">

                        <div class="communication-dropdown-header mb-3">
                            <div class="fw-bold">{{ db_trans('communication_message_center') }}</div>
                            <small class="text-muted">{{ db_trans('quick_access') }}</small>
                        </div>

                        <div class="communication-grid">
                            @can('communication.view')
                                <a href="{{ route('admin.communication.dashboard') }}" class="communication-grid-item">
                                    <i class="fas fa-chart-pie"></i>
                                    <span>{{ db_trans('overview') }}</span>
                                </a>
                            @endcan

                            @canany(['communication.view', 'communication.manage_controls'])
                                <a href="{{ url('/admin/communication/balance') }}" class="communication-grid-item">
                                    <i class="fas fa-wallet"></i>
                                    <span>{{ db_trans('sms_balance') }}</span>
                                </a>
                            @endcanany

                            @can('communication.manage_controls')
                                <a href="{{ route('admin.communication.controls.edit') }}" class="communication-grid-item">
                                    <i class="fas fa-sliders-h"></i>
                                    <span>{{ db_trans('controls') }}</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @endcanany

            {{-- USER --}}
            <div class="dropdown">
                <a class="admin-user-menu dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                    <div class="text-end d-none d-md-block">
                        <div class="admin-user-meta">{{ db_trans('logged_in_as') }}</div>
                        <div class="admin-user-name-topbar">{{ $authUser->name }}</div>
                        <div class="small text-muted">{{ $scopeLabel }}</div>
                    </div>

                    <div class="header-user-avatar">
                        {{ strtoupper(substr($authUser->name, 0, 1)) }}
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu shadow-sm">
                    <li class="dropdown-user-summary px-3 py-3">
                        <div class="dropdown-user-summary-name">{{ $authUser->name }}</div>
                        <div class="dropdown-user-summary-email">{{ $authUser->email }}</div>
                        <div class="small text-muted mt-1">{{ $scopeLabel }}</div>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2 text-muted"></i>
                            {{ db_trans('profile') }}
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="{{ url('/finance/tithes/activity-log') }}">
                            <i class="fas fa-history me-2 text-muted"></i>
                            {{ db_trans('tithe_activity_records') }}
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item admin-logout-btn">
                                <i class="fas fa-sign-out-alt me-2 text-muted"></i>
                                {{ db_trans('logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>