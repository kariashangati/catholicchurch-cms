<aside class="admin-sidebar" id="adminSidebar">
    @php
        $isParokia = request()->routeIs('kandas.*')
            || request()->routeIs('jumuiyas.*')
            || request()->routeIs('apostolic-groups.*')
            || request()->routeIs('familias.*')
            || request()->routeIs('members.*');

        $isSacraments = request()->routeIs('sacraments.*');
        $isSermons = request()->routeIs('sermons.*');
        $isMafundisho = request()->routeIs('mafundisho.*') || $isSermons;

        $mafundishoSidebarYear = now()->year;

        $isMafundishoOverview = request()->routeIs('mafundisho.summary');
        $isMafundishoStudents = request()->routeIs('mafundisho.index')
            || request()->routeIs('mafundisho.enrollments.*');
        $isMafundishoTypes = request()->routeIs('mafundisho.types.*');

        $isSermonsDashboard = request()->routeIs('sermons.dashboard');
        $isSermonsIndex = request()->routeIs('sermons.index') || request()->routeIs('sermons.show') || request()->routeIs('sermons.edit');
        $isSermonsCreate = request()->routeIs('sermons.create');
        $isSermonRequests = request()->routeIs('sermons.requests.*');
        $isSermonRecipients = request()->routeIs('sermons.recipients.*');

        $defaultTeachingTypeSlug = \App\Models\TeachingType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->value('slug') ?? 'komunio';

        $isLeadership = request()->routeIs('leadership.*');
        $isLiturgy = request()->routeIs('liturgy.*');
        $isMembershipClassification = request()->routeIs('membership.*');
        $isOperations = request()->routeIs('operations.*');
        $isProfile = request()->routeIs('profile.*');
        $isTranslations = request()->routeIs('translations.*');

        $isFinanceDashboard = request()->routeIs('finance.dashboard');
        $isOfferings = request()->routeIs('finance.offerings.*');
        $isTithes = request()->routeIs('finance.tithes.*');
        $isContributions = request()->routeIs('finance.contributions.*');
        $isProjectFinance = request()->routeIs('finance.projects.*');
        $isHallBookingDashboard = request()->routeIs('hall-bookings.dashboard');
        $isHallBookingHalls = request()->routeIs('hall-bookings.halls.*');
        $isHallBookingPrices = request()->routeIs('hall-bookings.prices.*');
        $isHallBookingBookings = request()->routeIs('hall-bookings.bookings.*');
        $isHallBookingBlockedDates = request()->routeIs('hall-bookings.blocked-dates.*');
        $isHallBookingMenu = request()->routeIs('hall-bookings.*');
        $isBudgetEstimates = request()->routeIs('finance.budgets.*');
        $isBankAccounts = request()->routeIs('finance.bank-accounts.*');
        $isWaliotoaWasiotoaReport = request()->routeIs('finance.reports.waliotoa.*');
        
        $isFinanceMenu = $isFinanceDashboard
            || $isOfferings
            || $isTithes
            || $isContributions
            || $isProjectFinance
            || $isHallBookingMenu
            || $isBudgetEstimates
            || $isWaliotoaWasiotoaReport;

        $isOthersMenu = $isLiturgy
            || $isBankAccounts
            || $isOperations
            || $isMembershipClassification;

        $isReportsDashboard = request()->routeIs('reports.index');
        $isKandaReports = request()->routeIs('kanda-reports.*');
        $isJumuiyaReports = request()->routeIs('jumuiya-reports.*');
        $isFinanceReports = request()->routeIs('reports.finance.index');
        $isBudgetVsActual = request()->routeIs('reports.budgets.*');
        $isSacramentReports = request()->routeIs('reports.sacraments.*');
        
        $isReportsMenu = $isReportsDashboard
            || $isSacramentReports
            || $isFinanceReports
            || $isKandaReports
            || $isJumuiyaReports;
            
        $isCommunication = request()->routeIs('admin.communication.*');

        $isFinanceReceipt = request()->routeIs('receipts.*');
        $isFinanceReceiptDashboard = request()->routeIs('receipts.dashboard')
            || request()->routeIs('receipts.analytics');
        $isFinanceReceiptCreate = request()->routeIs('receipts.create')
            || request()->routeIs('receipts.preview')
            || request()->routeIs('receipts.issue');
        $isFinanceReceiptHistory = request()->routeIs('receipts.history')
            || request()->routeIs('receipts.show')
            || request()->routeIs('receipts.pdf.download');
        $isFinanceReceiptPending = request()->routeIs('receipts.pending');
        $isFinanceReceiptExceptions = request()->routeIs('receipts.exceptions');

        $isAuditCenter = request()->routeIs('system.audit.index');
        $isActivityLogs = request()->routeIs('system.activity-logs.index');
        $isLoginHistory = request()->routeIs('system.login-history.index');

        $isSystemAudit = $isAuditCenter || $isActivityLogs || $isLoginHistory;
		
		$isAccessDashboard = request()->routeIs('system-access.dashboard');
        $isAccessUsers = request()->routeIs('system-access.users.*');
        $isAccessRoles = request()->routeIs('system-access.roles.*');
        $isAccessPermissions = request()->routeIs('system-access.permissions.*');
        $isAccessTemplates = request()->routeIs('system-access.templates.*');

        $isAccessControl = $isAccessDashboard || $isAccessUsers || $isAccessRoles || $isAccessPermissions || $isAccessTemplates;

        $isSystemConfigDashboard = request()->routeIs('system-config.dashboard');
        $isSystemConfigGeneral = request()->routeIs('system-config.general.*');
        $isSystemConfigBranding = request()->routeIs('system-config.branding.*');
        $isSystemConfigMaintenance = request()->routeIs('system-config.maintenance.*');
        $isSystemConfigEnvironment = request()->routeIs('system-config.environment.*');
        $isSystemConfigSystemInformation = request()->routeIs('system-config.system-information.*');
        $isSystemConfigCommunication = request()->routeIs('system-config.communication.*');

        $isSystemConfiguration = $isSystemConfigDashboard;
        $isSecurityMenu = $isAuditCenter;
		
		$isCmsDashboard = request()->routeIs('cms.dashboard');
		$isCmsHistories = request()->routeIs('cms.histories.*');
		$isCmsAnnouncements = request()->routeIs('cms.announcements.*');
		$isCmsGalleries = request()->routeIs('cms.galleries.*');

		$isContactMessagesDashboard = request()->routeIs('contact-messages.dashboard');
		$isContactMessagesIndex = request()->routeIs('contact-messages.index');
		$isContactReasons = request()->routeIs('contact-messages.reasons.*');
		$isContactMessagesMenu = request()->routeIs('contact-messages.*');

		$isWebsiteMenu = $isCmsDashboard
			|| $isCmsHistories
			|| $isCmsAnnouncements
			|| $isCmsGalleries
			|| $isContactMessagesMenu;

        $user = auth()->user();
        $scopeResolver = app(\App\Services\Access\UserScopeResolver::class);
        $resolvedScope = $user ? $scopeResolver->resolve($user) : null;

        $frontendContentService = app(\App\Services\Frontend\FrontendContentService::class);
        $frontendSettings = $frontendContentService->settings();

        $sidebarSiteName = $frontendSettings['site.name'] ?? config('app.name', 'ChurchMS');
        $sidebarTagline = $frontendSettings['site.tagline'] ?? db_trans('church_management_system');
        $sidebarLogo = $frontendSettings['site.logo'] ?? null;

        $sidebarLogoUrl = null;
        if (!empty($sidebarLogo)) {
            if (\Illuminate\Support\Str::startsWith($sidebarLogo, ['http://', 'https://'])) {
                $sidebarLogoUrl = $sidebarLogo;
            } else {
                $sidebarLogoUrl = asset(ltrim($sidebarLogo, '/'));
            }
        }

        $canOpenDashboard = $user && $user->can('dashboard.view') && Route::has('dashboard');

        $sidebarScopeLabel = db_trans('global');

        if ($resolvedScope) {
            if (method_exists($resolvedScope, 'isInvalid') && $resolvedScope->isInvalid()) {
                $sidebarScopeLabel = db_trans('invalid_scope');
            } elseif (method_exists($resolvedScope, 'isJumuiya') && $resolvedScope->isJumuiya()) {
                $sidebarScopeLabel = db_trans('jumuiya') . ': '
                    . (\App\Models\Jumuiya::query()->find($resolvedScope->jumuiyaId)?->name ?? db_trans('unknown'));
            } elseif (method_exists($resolvedScope, 'isKanda') && $resolvedScope->isKanda()) {
                $sidebarScopeLabel = db_trans('kanda') . ': '
                    . (\App\Models\Kanda::query()->find($resolvedScope->kandaId)?->name ?? db_trans('unknown'));
            } else {
                $sidebarScopeLabel = db_trans('global');
            }
        }
		
    @endphp

    <div class="sidebar-inner d-flex flex-column h-100">

        @if($canOpenDashboard)
            <a class="sidebar-brand sidebar-brand-logo-only text-decoration-none"
               href="{{ route('dashboard') }}"
               aria-label="{{ $sidebarSiteName }}">
                <div class="sidebar-brand-icon brand-icon-v2">
                    @if(!empty($sidebarLogoUrl))
                        <img src="{{ $sidebarLogoUrl }}"
                             alt="{{ $sidebarSiteName }}"
                             class="sidebar-brand-logo">
                    @else
                        <i class="fas fa-church"></i>
                    @endif
                </div>
            </a>
        @else
            <div class="sidebar-brand sidebar-brand-logo-only sidebar-brand-disabled"
                 title="{{ db_trans('you_do_not_have_permission_to_view_this_section') }}"
                 aria-label="{{ $sidebarSiteName }}">
                <div class="sidebar-brand-icon brand-icon-v2">
                    @if(!empty($sidebarLogoUrl))
                        <img src="{{ $sidebarLogoUrl }}"
                             alt="{{ $sidebarSiteName }}"
                             class="sidebar-brand-logo">
                    @else
                        <i class="fas fa-church"></i>
                    @endif
                </div>
            </div>
        @endif

        @canany([
            'kandas.view',
            'jumuiyas.view',
            'familias.view',
            'members.view',
            'apostolic-groups.view',
            'sacraments.view',
            'sacraments.kanda.view',
            'sacraments.jumuiya.view',
            'mafundisho.view',
            'mafundisho-types.view',
            'sermons.dashboard.view',
            'sermons.view',
            'sermons.create',
            'sermons.requests.view',
            'sermons.recipients.view'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('church_management') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">

                @canany(['kandas.view', 'jumuiyas.view', 'apostolic-groups.view', 'familias.view', 'members.view'])
                    <li class="nav-item sidebar-group {{ $isParokia ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isParokia ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarParokia"
                           role="button"
                           aria-expanded="{{ $isParokia ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-sitemap"></i></span>
                            <span class="nav-link-text">{{ db_trans('parokia') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isParokia ? 'show' : '' }}" id="sidebarParokia">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('kandas.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('kandas.*') ? 'is-active active' : '' }}"
                                           href="{{ route('kandas.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-layer-group"></i></span>
                                            <span>{{ db_trans('kandas') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('jumuiyas.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('jumuiyas.*') ? 'is-active active' : '' }}"
                                           href="{{ route('jumuiyas.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-project-diagram"></i></span>
                                            <span>{{ db_trans('jumuiyas') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('apostolic-groups.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('apostolic-groups.*') ? 'is-active active' : '' }}"
                                           href="{{ route('apostolic-groups.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-people-arrows"></i></span>
                                            <span>{{ db_trans('apostolic_groups') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('familias.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('familias.*') ? 'is-active active' : '' }}"
                                           href="{{ route('familias.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-home"></i></span>
                                            <span>{{ db_trans('familias') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('members.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('members.*') ? 'is-active active' : '' }}"
                                           href="{{ route('members.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-user-friends"></i></span>
                                            <span>{{ db_trans('members') }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcanany

                @canany(['sacraments.view', 'sacraments.kanda.view', 'sacraments.jumuiya.view'])
                    <li class="nav-item sidebar-group {{ $isSacraments ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isSacraments ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarSacraments"
                           role="button"
                           aria-expanded="{{ $isSacraments ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-cross"></i></span>
                            <span class="nav-link-text">{{ db_trans('sacraments') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isSacraments ? 'show' : '' }}" id="sidebarSacraments">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('sacraments.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('sacraments.index') ? 'is-active active' : '' }}"
                                           href="{{ route('sacraments.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-globe"></i></span>
                                            <span>{{ db_trans('sacrament_global') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('sacraments.kanda.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('sacraments.kandas') || request()->routeIs('sacraments.kandas.show') ? 'is-active active' : '' }}"
                                           href="{{ route('sacraments.kandas') }}">
                                            <span class="nav-child-icon"><i class="fas fa-layer-group"></i></span>
                                            <span>{{ db_trans('sacrament_zonal_wide') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('sacraments.jumuiya.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('sacraments.jumuiyas') || request()->routeIs('sacraments.jumuiyas.show') ? 'is-active active' : '' }}"
                                           href="{{ route('sacraments.jumuiyas') }}">
                                            <span class="nav-child-icon"><i class="fas fa-project-diagram"></i></span>
                                            <span>{{ db_trans('sacrament_jumuiya_wide') }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcanany

                @canany([
                    'mafundisho.view',
                    'mafundisho-types.view',
                    'sermons.dashboard.view',
                    'sermons.view',
                    'sermons.create',
                    'sermons.requests.view',
                    'sermons.recipients.view'
                ])
                    <li class="nav-item sidebar-group {{ $isMafundisho ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isMafundisho ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarMafundisho"
                           role="button"
                           aria-expanded="{{ $isMafundisho ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span class="nav-link-text">{{ db_trans('teaching_enrollments') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isMafundisho ? 'show' : '' }}" id="sidebarMafundisho">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('mafundisho.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isMafundishoOverview ? 'is-active active' : '' }}"
                                           href="{{ route('mafundisho.summary') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
                                            <span>{{ db_trans('overview') }}</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isMafundishoStudents ? 'is-active active' : '' }}"
                                           href="{{ route('mafundisho.index', ['type' => $defaultTeachingTypeSlug, 'year' => $mafundishoSidebarYear]) }}">
                                            <span class="nav-child-icon"><i class="fas fa-users"></i></span>
                                            <span>{{ db_trans('students_enrollments') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('mafundisho-types.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isMafundishoTypes ? 'is-active active' : '' }}"
                                           href="{{ route('mafundisho.types.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-list-check"></i></span>
                                            <span>{{ db_trans('teaching_types') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @canany([
                                    'sermons.dashboard.view',
                                    'sermons.view',
                                    'sermons.create',
                                    'sermons.requests.view',
                                    'sermons.recipients.view'
                                ])
                                    @can('sermons.dashboard.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isSermonsDashboard ? 'is-active active' : '' }}"
                                               href="{{ route('sermons.dashboard') }}">
                                                <span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
                                                <span>{{ db_trans('sermons_overview') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('sermons.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isSermonsIndex ? 'is-active active' : '' }}"
                                               href="{{ route('sermons.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-book-bible"></i></span>
                                                <span>{{ db_trans('sermons') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('sermons.create')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isSermonsCreate ? 'is-active active' : '' }}"
                                               href="{{ route('sermons.create') }}">
                                                <span class="nav-child-icon"><i class="fas fa-pen-nib"></i></span>
                                                <span>{{ db_trans('write_sermon') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('sermons.requests.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isSermonRequests ? 'is-active active' : '' }}"
                                               href="{{ route('sermons.requests.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-inbox"></i></span>
                                                <span>{{ db_trans('sermon_requests') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('sermons.recipients.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isSermonRecipients ? 'is-active active' : '' }}"
                                               href="{{ route('sermons.recipients.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-link"></i></span>
                                                <span>{{ db_trans('sermon_recipients') }}</span>
                                            </a>
                                        </li>
                                    @endcan
                                @endcanany

                            </ul>
                        </div>
                    </li>
                @endcanany
            </ul>
        @endcanany

        @canany([
            'finance.view',
            'finance.tithes.view',
            'finance.tithes.bulk',
            'finance.tithes.duplicates.view',
            'finance.projects.view',
            'finance.contributions.dashboard.view',
            'finance.contributions.cash.view',
            'finance.contributions.bank.view',
            'finance.contributions.types.view',
            'finance.budgets.view',
            'finance.budgets.income.view',
            'finance.budgets.expense.view',
            'finance.bank-accounts.view',
            'finance.reports.waliotoa.view',
            'hall-bookings.dashboard.view',
            'hall-bookings.halls.view',
            'hall-bookings.prices.manage',
            'hall-bookings.bookings.view',
            'hall-bookings.blocked-dates.manage'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('finance') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">
                @canany([
                    'finance.view',
                    'finance.tithes.view',
                    'finance.tithes.bulk',
                    'finance.tithes.duplicates.view',
                    'finance.projects.view',
                    'finance.contributions.dashboard.view',
                    'finance.contributions.cash.view',
                    'finance.contributions.bank.view',
                    'finance.contributions.types.view',
                    'finance.budgets.view',
                    'finance.budgets.income.view',
                    'finance.budgets.expense.view',
                    'hall-bookings.dashboard.view',
                    'hall-bookings.halls.view',
                    'hall-bookings.prices.manage',
                    'hall-bookings.bookings.view',
                    'hall-bookings.blocked-dates.manage'
                ])
                    <li class="nav-item sidebar-group {{ $isFinanceMenu ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isFinanceMenu ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarFinanceMenu"
                           role="button"
                           aria-expanded="{{ $isFinanceMenu ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-wallet"></i></span>
                            <span class="nav-link-text">{{ db_trans('finance') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isFinanceMenu ? 'show' : '' }}" id="sidebarFinanceMenu">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('finance.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isFinanceDashboard ? 'is-active active' : '' }}"
                                           href="{{ route('finance.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
                                            <span>{{ db_trans('finance_overview') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('finance.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isOfferings ? 'is-active active' : '' }}"
                                           href="{{ route('finance.offerings.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-hand-holding-heart"></i></span>
                                            <span>{{ db_trans('offerings') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @canany(['finance.tithes.view', 'finance.tithes.bulk', 'finance.tithes.duplicates.view'])
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isTithes ? 'is-active active' : '' }}"
                                           href="{{ route('finance.tithes.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-coins"></i></span>
                                            <span>{{ db_trans('tithes') }}</span>
                                        </a>
                                    </li>
                                @endcanany

                                @can('finance.projects.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isProjectFinance ? 'is-active active' : '' }}"
                                           href="{{ route('finance.projects.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-project-diagram"></i></span>
<span>{{ db_trans('finance_projects') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @canany([
                                    'hall-bookings.dashboard.view',
                                    'hall-bookings.halls.view',
                                    'hall-bookings.prices.manage',
                                    'hall-bookings.bookings.view',
                                    'hall-bookings.blocked-dates.manage',
                                ])
                                    @can('hall-bookings.dashboard.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isHallBookingDashboard ? 'is-active active' : '' }}"
                                               href="{{ route('hall-bookings.dashboard') }}">
                                                <span class="nav-child-icon"><i class="fas fa-building"></i></span>
                                                <span>{{ db_trans('hall_booking') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('hall-bookings.halls.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isHallBookingHalls ? 'is-active active' : '' }}"
                                               href="{{ route('hall-bookings.halls.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-warehouse"></i></span>
                                                <span>{{ db_trans('halls') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('hall-bookings.prices.manage')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isHallBookingPrices ? 'is-active active' : '' }}"
                                               href="{{ route('hall-bookings.prices.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-tags"></i></span>
                                                <span>{{ db_trans('hall_prices') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('hall-bookings.bookings.view')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isHallBookingBookings ? 'is-active active' : '' }}"
                                               href="{{ route('hall-bookings.bookings.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-calendar-check"></i></span>
                                                <span>{{ db_trans('bookings') }}</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('hall-bookings.blocked-dates.manage')
                                        <li class="nav-item">
                                            <a class="nav-link nav-child-link {{ $isHallBookingBlockedDates ? 'is-active active' : '' }}"
                                               href="{{ route('hall-bookings.blocked-dates.index') }}">
                                                <span class="nav-child-icon"><i class="fas fa-ban"></i></span>
                                                <span>{{ db_trans('blocked_dates') }}</span>
                                            </a>
                                        </li>
                                    @endcan
                                @endcanany

                                @canany([
                                    'finance.contributions.dashboard.view',
                                    'finance.contributions.cash.view',
                                    'finance.contributions.bank.view',
                                    'finance.contributions.types.view'
                                ])
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isContributions ? 'is-active active' : '' }}"
                                           href="{{ route('finance.contributions.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-donate"></i></span>
                                            <span>{{ db_trans('contributions') }}</span>
                                        </a>
                                    </li>
                                @endcanany

                                @canany(['finance.budgets.view', 'finance.budgets.income.view', 'finance.budgets.expense.view'])
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isBudgetEstimates ? 'is-active active' : '' }}"
                                           href="{{ route('finance.budgets.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                            <span>{{ db_trans('budget_estimates') }}</span>
                                        </a>
                                    </li>
                                @endcanany

                                @can('finance.reports.waliotoa.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isWaliotoaWasiotoaReport ? 'is-active active' : '' }}"
                                           href="{{ route('finance.reports.waliotoa.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-user-check"></i></span>
                                            <span>{{ db_trans('waliotoa_wasiotoa') }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcanany
            </ul>
        @endcanany

        @canany([
            'reports.view',
            'reports.finance.view',
            'reports.budgets.view',
            'reports.sacraments.view',
            'kanda-reports.view',
            'jumuiya-reports.view'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('reports') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">
                @canany([
                    'reports.view',
                    'reports.finance.view',
                    'reports.sacraments.view',
                    'kanda-reports.view',
                    'jumuiya-reports.view'
                ])
                    <li class="nav-item sidebar-group {{ $isReportsMenu ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isReportsMenu ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarReportsMenu"
                           role="button"
                           aria-expanded="{{ $isReportsMenu ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-chart-line"></i></span>
                            <span class="nav-link-text">{{ db_trans('reports') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isReportsMenu ? 'show' : '' }}" id="sidebarReportsMenu">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('reports.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isReportsDashboard ? 'is-active active' : '' }}"
                                           href="{{ route('reports.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chart-line"></i></span>
                                            <span>{{ db_trans('reports_overview') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('reports.sacraments.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isSacramentReports ? 'is-active active' : '' }}"
                                           href="{{ route('reports.sacraments.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-book-medical"></i></span>
                                            <span>{{ db_trans('reports_sacraments') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('reports.finance.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isFinanceReports ? 'is-active active' : '' }}"
                                           href="{{ route('reports.finance.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chart-bar"></i></span>
                                            <span>{{ db_trans('reports_finance') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('kanda-reports.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isKandaReports ? 'is-active active' : '' }}"
                                           href="{{ route('kanda-reports.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-file-alt"></i></span>
                                            <span>{{ db_trans('reports_kanda_finance') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('jumuiya-reports.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isJumuiyaReports ? 'is-active active' : '' }}"
                                           href="{{ route('jumuiya-reports.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-file-alt"></i></span>
                                            <span>{{ db_trans('reports_jumuiya_finance') }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcanany
            </ul>
        @endcanany

        @canany([
            'leadership.dashboard.view',
            'leadership.assignments.view',
            'leadership.positions.view'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('leadership') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">

                @canany(['leadership.dashboard.view', 'leadership.assignments.view', 'leadership.positions.view'])
                    <li class="nav-item sidebar-group {{ $isLeadership ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isLeadership ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarLeadership"
                           role="button"
                           aria-expanded="{{ $isLeadership ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-user-shield"></i></span>
                            <span class="nav-link-text">{{ db_trans('leadership') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isLeadership ? 'show' : '' }}" id="sidebarLeadership">
                            <ul class="list-unstyled sidebar-submenu">
                                @can('leadership.dashboard.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('leadership.dashboard') ? 'is-active active' : '' }}"
                                           href="{{ route('leadership.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chalkboard"></i></span>
                                            <span>{{ db_trans('leadership_dashboard') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('leadership.assignments.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('leadership.assignments.*') ? 'is-active active' : '' }}"
                                           href="{{ route('leadership.assignments.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-user-tag"></i></span>
                                            <span>{{ db_trans('leadership_assignments') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('leadership.positions.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ request()->routeIs('leadership.positions.*') ? 'is-active active' : '' }}"
                                           href="{{ route('leadership.positions.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-id-badge"></i></span>
                                            <span>{{ db_trans('leadership_positions') }}</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcanany

            </ul>
        @endcanany

        @canany([
            'liturgy.mass-types.view',
            'liturgy.offering-types.view',
            'liturgy.mass-schedules.view',
            'finance.bank-accounts.view',
            'operations.service-providers.view',
            'operations.church-assets.view',
            'membership.age-groups.view'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('others') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">

                <li class="nav-item sidebar-group {{ $isOthersMenu ? 'is-open' : '' }}">
                    <a class="nav-link nav-group-toggle {{ $isOthersMenu ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse"
                       href="#sidebarOthersMenu"
                       role="button"
                       aria-expanded="{{ $isOthersMenu ? 'true' : 'false' }}">
                        <span class="nav-icon-wrap"><i class="fas fa-ellipsis-h"></i></span>
                        <span class="nav-link-text">{{ db_trans('others') }}</span>
                        <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                    </a>

                    <div class="collapse {{ $isOthersMenu ? 'show' : '' }}" id="sidebarOthersMenu">
                        <ul class="list-unstyled sidebar-submenu">

                            @can('liturgy.mass-types.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('liturgy.mass-types.*') ? 'is-active active' : '' }}"
                                       href="{{ route('liturgy.mass-types.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-book-open"></i></span>
                                        <span>{{ db_trans('mass_types') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('liturgy.offering-types.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('liturgy.offering-types.*') ? 'is-active active' : '' }}"
                                       href="{{ route('liturgy.offering-types.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-gift"></i></span>
                                        <span>{{ db_trans('offering_types') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('liturgy.mass-schedules.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('liturgy.mass-schedules.*') ? 'is-active active' : '' }}"
                                       href="{{ route('liturgy.mass-schedules.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-calendar-alt"></i></span>
                                        <span>{{ db_trans('mass_schedules') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('finance.bank-accounts.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ $isBankAccounts ? 'is-active active' : '' }}"
                                       href="{{ route('finance.bank-accounts.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-university"></i></span>
                                        <span>{{ db_trans('bank_accounts') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('operations.service-providers.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('operations.service-providers.*') ? 'is-active active' : '' }}"
                                       href="{{ route('operations.service-providers.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-hands-helping"></i></span>
                                        <span>{{ db_trans('service_providers') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('operations.church-assets.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('operations.church-assets.*') ? 'is-active active' : '' }}"
                                       href="{{ route('operations.church-assets.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-warehouse"></i></span>
                                        <span>{{ db_trans('church_assets') }}</span>
                                    </a>
                                </li>
                            @endcan

                            @can('membership.age-groups.view')
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ request()->routeIs('membership.age-groups.*') ? 'is-active active' : '' }}"
                                       href="{{ route('membership.age-groups.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-users-cog"></i></span>
                                        <span>{{ db_trans('age_groups') }}</span>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </div>
                </li>

            </ul>
        @endcanany

		@canany([
			'cms.dashboard.view',
			'cms.histories.view',
			'cms.announcements.view',
			'cms.galleries.view',
			'contact-messages.dashboard.view',
			'contact-messages.view',
			'contact-messages.reasons.manage'
		])
			<div class="sidebar-section-title px-4 mt-4 mb-2">
				{{ db_trans('website') }}
			</div>

			<ul class="sidebar-menu list-unstyled px-3 mb-0">

				<li class="nav-item sidebar-group {{ $isWebsiteMenu ? 'is-open' : '' }}">
					<a class="nav-link nav-group-toggle {{ $isWebsiteMenu ? '' : 'collapsed' }}"
					   data-bs-toggle="collapse"
					   href="#sidebarWebsiteMenu"
					   role="button"
					   aria-expanded="{{ $isWebsiteMenu ? 'true' : 'false' }}">
						<span class="nav-icon-wrap"><i class="fas fa-globe"></i></span>
						<span class="nav-link-text">{{ db_trans('website') }}</span>
						<span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
					</a>

					<div class="collapse {{ $isWebsiteMenu ? 'show' : '' }}" id="sidebarWebsiteMenu">
						<ul class="list-unstyled sidebar-submenu">

							@can('cms.dashboard.view')
								<li class="nav-item">
									<a class="nav-link nav-child-link {{ $isCmsDashboard ? 'is-active active' : '' }}"
									   href="{{ route('cms.dashboard') }}">
										<span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
										<span>{{ db_trans('website_overview') }}</span>
									</a>
								</li>
							@endcan

							@can('cms.galleries.view')
								<li class="nav-item">
									<a class="nav-link nav-child-link {{ $isCmsGalleries ? 'is-active active' : '' }}"
									   href="{{ route('cms.galleries.index') }}">
										<span class="nav-child-icon"><i class="fas fa-calendar-alt"></i></span>
										<span>{{ db_trans('website_events') }}</span>
									</a>
								</li>
							@endcan

							@can('cms.histories.view')
								<li class="nav-item">
									<a class="nav-link nav-child-link {{ $isCmsHistories ? 'is-active active' : '' }}"
									   href="{{ route('cms.histories.index') }}">
										<span class="nav-child-icon"><i class="fas fa-landmark"></i></span>
										<span>{{ db_trans('website_histories') }}</span>
									</a>
								</li>
							@endcan

							@can('cms.announcements.view')
								<li class="nav-item">
									<a class="nav-link nav-child-link {{ $isCmsAnnouncements ? 'is-active active' : '' }}"
									   href="{{ route('cms.announcements.index') }}">
										<span class="nav-child-icon"><i class="fas fa-bullhorn"></i></span>
										<span>{{ db_trans('website_announcements') }}</span>
									</a>
								</li>
							@endcan

							@canany([
								'contact-messages.dashboard.view',
								'contact-messages.view',
								'contact-messages.reasons.manage'
							])
								@can('contact-messages.dashboard.view')
									<li class="nav-item">
										<a class="nav-link nav-child-link {{ $isContactMessagesDashboard ? 'is-active active' : '' }}"
										   href="{{ route('contact-messages.dashboard') }}">
											<span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
											<span>{{ db_trans('contact_messages_overview') }}</span>
										</a>
									</li>
								@endcan

								@can('contact-messages.view')
									<li class="nav-item">
										<a class="nav-link nav-child-link {{ $isContactMessagesIndex ? 'is-active active' : '' }}"
										   href="{{ route('contact-messages.index') }}">
											<span class="nav-child-icon"><i class="fas fa-envelope-open-text"></i></span>
											<span>{{ db_trans('contact_messages') }}</span>
										</a>
									</li>
								@endcan

								@can('contact-messages.reasons.manage')
									<li class="nav-item">
										<a class="nav-link nav-child-link {{ $isContactReasons ? 'is-active active' : '' }}"
										   href="{{ route('contact-messages.reasons.index') }}">
											<span class="nav-child-icon"><i class="fas fa-list"></i></span>
											<span>{{ db_trans('contact_reasons') }}</span>
										</a>
									</li>
								@endcan
							@endcanany

						</ul>
					</div>
				</li>

			</ul>
		@endcanany

        @canany([
            'system.config.dashboard.view',
            'access.dashboard.view',
            'access.users.view',
            'access.roles.view',
            'access.permissions.view',
            'access.templates.view',
            'audit.view'
        ])
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('system') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">

                @can('system.config.dashboard.view')
                    <li class="nav-item sidebar-group {{ $isSystemConfiguration ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isSystemConfiguration ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarSystemConfiguration"
                           role="button"
                           aria-expanded="{{ $isSystemConfiguration ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-cogs"></i></span>
                            <span class="nav-link-text">{{ db_trans('system_configuration') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isSystemConfiguration ? 'show' : '' }}" id="sidebarSystemConfiguration">
                            <ul class="list-unstyled sidebar-submenu">
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ $isSystemConfigDashboard ? 'is-active active' : '' }}"
                                       href="{{ route('system-config.dashboard') }}">
                                        <span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
                                        <span>{{ db_trans('overview') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan

                @canany([
                    'access.dashboard.view',
                    'access.users.view',
                    'access.roles.view',
                    'access.permissions.view',
                    'access.templates.view'
                ])
                    <li class="nav-item sidebar-group {{ $isAccessControl ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isAccessControl ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarRolesPermissions"
                           role="button"
                           aria-expanded="{{ $isAccessControl ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-user-lock"></i></span>
                            <span class="nav-link-text">{{ db_trans('roles_and_permissions') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isAccessControl ? 'show' : '' }}" id="sidebarRolesPermissions">
                            <ul class="list-unstyled sidebar-submenu">

                                @can('access.dashboard.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isAccessDashboard ? 'is-active active' : '' }}"
                                           href="{{ route('system-access.dashboard') }}">
                                            <span class="nav-child-icon"><i class="fas fa-chart-pie"></i></span>
                                            <span>{{ db_trans('overview') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('access.users.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isAccessUsers ? 'is-active active' : '' }}"
                                           href="{{ route('system-access.users.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-user-shield"></i></span>
                                            <span>{{ db_trans('admin_users') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('access.roles.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isAccessRoles ? 'is-active active' : '' }}"
                                           href="{{ route('system-access.roles.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-id-badge"></i></span>
                                            <span>{{ db_trans('roles') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('access.permissions.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isAccessPermissions ? 'is-active active' : '' }}"
                                           href="{{ route('system-access.permissions.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-key"></i></span>
                                            <span>{{ db_trans('permissions') }}</span>
                                        </a>
                                    </li>
                                @endcan

                                @can('access.templates.view')
                                    <li class="nav-item">
                                        <a class="nav-link nav-child-link {{ $isAccessTemplates ? 'is-active active' : '' }}"
                                           href="{{ route('system-access.templates.index') }}">
                                            <span class="nav-child-icon"><i class="fas fa-comment-dots"></i></span>
                                            <span>{{ db_trans('access_notification_templates') }}</span>
                                        </a>
                                    </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
                @endcanany

        @role('Super Admin')
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('system') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">
                <li class="nav-item {{ $isTranslations ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('translations.index') }}">
                        <span class="nav-icon-wrap"><i class="fas fa-language"></i></span>
                        <span class="nav-link-text">{{ db_trans('translations') }}</span>
                    </a>
                </li>
            </ul>
        @endrole

                @can('audit.view')
                    <li class="nav-item sidebar-group {{ $isSecurityMenu ? 'is-open' : '' }}">
                        <a class="nav-link nav-group-toggle {{ $isSecurityMenu ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#sidebarSecurity"
                           role="button"
                           aria-expanded="{{ $isSecurityMenu ? 'true' : 'false' }}">
                            <span class="nav-icon-wrap"><i class="fas fa-shield-alt"></i></span>
                            <span class="nav-link-text">{{ db_trans('security') }}</span>
                            <span class="nav-chevron ms-auto"><i class="fas fa-chevron-right"></i></span>
                        </a>

                        <div class="collapse {{ $isSecurityMenu ? 'show' : '' }}" id="sidebarSecurity">
                            <ul class="list-unstyled sidebar-submenu">
                                <li class="nav-item">
                                    <a class="nav-link nav-child-link {{ $isAuditCenter ? 'is-active active' : '' }}"
                                       href="{{ route('system.audit.index') }}">
                                        <span class="nav-child-icon"><i class="fas fa-shield-alt"></i></span>
                                        <span>{{ db_trans('audit_access_center') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endcan

            </ul>
        @endcanany

        <div class="mt-auto px-3 pb-4 pt-4">
            <div class="sidebar-user-card">
                <div class="d-flex align-items-center">
                    <div class="sidebar-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <div class="sidebar-user-label">{{ db_trans('logged_in_as') }}</div>
                        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
                        <div class="sidebar-user-scope">{{ $sidebarScopeLabel }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</aside>

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>