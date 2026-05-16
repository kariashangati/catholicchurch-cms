<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-inner d-flex flex-column h-100">
        <a class="sidebar-brand d-flex align-items-center text-decoration-none" href="{{ route('dashboard') }}">
            <div class="sidebar-brand-icon brand-icon-v2">
                <i class="fas fa-church"></i>
            </div>
            <div class="sidebar-brand-text ms-3 text-start">
                <div class="brand-title">{{ config('app.name', 'ChurchMS') }}</div>
                <div class="brand-subtitle">{{ db_trans('church_management_system') }}</div>
            </div>
        </a>



        <div class="sidebar-section-title px-4 mt-4 mb-2">
            {{ db_trans('main_navigation') }}
        </div>

        <ul class="sidebar-menu list-unstyled px-3 mb-0">
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <span class="nav-icon-wrap"><i class="fas fa-home"></i></span>
                    <span>{{ db_trans('dashboard') }}</span>
                </a>
            </li>
			
			@can('kandas.view')
    <li class="nav-item {{ request()->routeIs('kandas.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kandas.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-map-marked-alt"></i></span>
            <span>{{ db_trans('kandas') }}</span>
        </a>
    </li>
@endcan

@can('kanda-reports.view')
    <li class="nav-item {{ request()->routeIs('kanda-reports.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kanda-reports.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-pie"></i></span>
            <span>{{ db_trans('kanda_financial_reports') }}</span>
        </a>
    </li>
@endcan

@can('jumuiyas.view')
    <li class="nav-item {{ request()->routeIs('jumuiyas.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('jumuiyas.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-sitemap"></i></span>
            <span>{{ db_trans('jumuiyas') }}</span>
        </a>
    </li>
@endcan
@can('jumuiya-reports.view')
    <li class="nav-item {{ request()->routeIs('jumuiya-reports.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('jumuiya-reports.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-bar"></i></span>
            <span>{{ db_trans('jumuiya_financial_reports') }}</span>
        </a>
    </li>
@endcan

            @can('members.view')
                <li class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('members.index') }}">
                        <span class="nav-icon-wrap"><i class="fas fa-users"></i></span>
                        <span>{{ db_trans('members') }}</span>
                    </a>
                </li>
            @endcan
			
			@can('apostolic-groups.view')
    <li class="nav-item {{ request()->routeIs('apostolic-groups.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('apostolic-groups.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-hands-helping"></i></span>
            <span>{{ db_trans('apostolic_groups') }}</span>
        </a>
    </li>
@endcan

@can('familias.view')
    <li class="nav-item {{ request()->routeIs('familias.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('familias.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-home"></i></span>
            <span>{{ db_trans('familias') }}</span>
        </a>
    </li>
@endcan
@can('sacraments.view')
    <li class="nav-item {{ request()->routeIs('sacraments.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('sacraments.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-cross"></i></span>
            <span>{{ db_trans('sacraments') }}</span>
        </a>
    </li>
@endcan

@can('mafundisho.view')
    <li class="nav-item {{ request()->routeIs('mafundisho.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('mafundisho.summary') }}">
            <span class="nav-icon-wrap"><i class="fas fa-graduation-cap"></i></span>
            <span>{{ db_trans('teaching_enrollments') }}</span>
        </a>
    </li>
@endcan
@can('finance.view')
    <li class="nav-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.dashboard') }}">
            <span class="nav-icon-wrap"><i class="fas fa-wallet"></i></span>
            <span>{{ db_trans('finance') }}</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('finance.offerings.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.offerings.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-hand-holding-heart"></i></span>
            <span>{{ db_trans('offerings') }}</span>
        </a>
    </li>
@endcan

@can('finance.tithes.view')
    <li class="nav-item {{ request()->routeIs('finance.tithes.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.tithes.dashboard') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-area"></i></span>
            <span>{{ db_trans('tithes_dashboard') }}</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('finance.tithes.*') && !request()->routeIs('finance.tithes.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.tithes.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-coins"></i></span>
            <span>{{ db_trans('tithes') }}</span>
        </a>
    </li>
@endcan

@can('finance.projects.view')
    <li class="nav-item {{ request()->routeIs('finance.projects.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.projects.dashboard') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-line"></i></span>
            <span>{{ db_trans('project_finance_dashboard') }}</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('finance.projects.*') && !request()->routeIs('finance.projects.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.projects.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-diagram-project"></i></span>
            <span>{{ db_trans('project_finance') }}</span>
        </a>
    </li>
@endcan

@can('finance.contributions.dashboard.view')
    <li class="nav-item {{ request()->routeIs('finance.contributions.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.contributions.dashboard') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-column"></i></span>
            <span>{{ db_trans('michango_dashboard') }}</span>
        </a>
    </li>
@endcan

@can('finance.contributions.cash.view')
    <li class="nav-item {{ request()->routeIs('finance.contributions.cash.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.contributions.cash.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-hand-holding-dollar"></i></span>
            <span>{{ db_trans('cash_contributions') }}</span>
        </a>
    </li>
@endcan

@can('finance.contributions.bank.view')
    <li class="nav-item {{ request()->routeIs('finance.contributions.bank.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.contributions.bank.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-building-columns"></i></span>
            <span>{{ db_trans('bank_contributions') }}</span>
        </a>
    </li>
@endcan

@can('finance.contributions.types.view')
    <li class="nav-item {{ request()->routeIs('finance.contributions.types.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.contributions.types.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-tags"></i></span>
            <span>{{ db_trans('contribution_types') }}</span>
        </a>
    </li>
@endcan
@canany(['finance.reports.summary.view', 'finance.reports.compliance.view'])
<li class="nav-item"><a class="nav-link" data-bs-toggle="collapse" href="#financeReportsMenu"><span class="nav-icon-wrap"><i class="fas fa-chart-line"></i></span><span>{{ db_trans('financial_reports') }}</span></a><div class="collapse {{ request()->routeIs('finance.reports.*') ? 'show' : '' }}" id="financeReportsMenu"><ul class="list-unstyled ps-4 pt-2">@can('finance.reports.summary.view')<li class="nav-item"><a class="nav-link" href="{{ route('finance.reports.financial-summary') }}">{{ db_trans('financial_summary') }}</a></li>@endcan @can('finance.reports.compliance.view')<li class="nav-item"><a class="nav-link" href="{{ route('finance.reports.compliance') }}">{{ db_trans('contribution_compliance_report') }}</a></li>@endcan</ul></div></li>
@endcanany
@can('finance.budgets.view')
<li class="nav-item"><a class="nav-link" data-bs-toggle="collapse" href="#financeBudgetMenu"><span class="nav-icon-wrap"><i class="fas fa-calculator"></i></span><span>{{ db_trans('budget_estimates') }}</span></a><div class="collapse {{ request()->routeIs('finance.budgets.*') ? 'show' : '' }}" id="financeBudgetMenu"><ul class="list-unstyled ps-4 pt-2"><li class="nav-item"><a class="nav-link" href="{{ route('finance.budgets.dashboard') }}">{{ db_trans('budget_overview') }}</a></li>@can('finance.budgets.income.view')<li class="nav-item"><a class="nav-link" href="{{ route('finance.budgets.income.index') }}">{{ db_trans('income_budget_estimates') }}</a></li>@endcan @can('finance.budgets.expense.view')<li class="nav-item"><a class="nav-link" href="{{ route('finance.budgets.expense.index') }}">{{ db_trans('expense_budget_estimates') }}</a></li>@endcan</ul></div></li>
@endcan
@can('reports.view')
    <li class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('reports.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-chart-line"></i></span>
            <span>{{ db_trans('reports_dashboard') }}</span>
        </a>
    </li>
@endcan

@can('reports.finance.view')
    <li class="nav-item {{ request()->routeIs('reports.finance.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('reports.finance.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-file-invoice-dollar"></i></span>
            <span>{{ db_trans('finance_reports') }}</span>
        </a>
    </li>
@endcan

@can('reports.finance.compliance.view')
    <li class="nav-item {{ request()->routeIs('reports.finance.compliance') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('reports.finance.compliance') }}">
            <span class="nav-icon-wrap"><i class="fas fa-user-check"></i></span>
            <span>{{ db_trans('monthly_contribution_compliance') }}</span>
        </a>
    </li>
@endcan

@can('reports.budgets.view')
    <li class="nav-item {{ request()->routeIs('reports.budgets.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('reports.budgets.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-scale-balanced"></i></span>
            <span>{{ db_trans('budget_vs_actual') }}</span>
        </a>
    </li>
@endcan

@can('reports.sacraments.view')
    <li class="nav-item {{ request()->routeIs('reports.sacraments.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('reports.sacraments.index') }}">
            <span class="nav-icon-wrap"><i class="fas fa-book-bible"></i></span>
            <span>{{ db_trans('sacrament_reports') }}</span>
        </a>
    </li>
@endcan
@canany(['leadership.dashboard.view', 'leadership.assignments.view', 'leadership.positions.view'])
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLeadership" aria-expanded="false">
        <i class="fas fa-user-shield me-2"></i>
        <span>{{ db_trans('leadership') }}</span>
    </a>
    <div id="collapseLeadership" class="collapse">
        <div class="collapse-inner rounded bg-white py-2">
            @can('leadership.dashboard.view')
                <a class="collapse-item" href="{{ route('leadership.dashboard') }}">{{ db_trans('leadership_dashboard') }}</a>
            @endcan
            @can('leadership.assignments.view')
                <a class="collapse-item" href="{{ route('leadership.assignments.index') }}">{{ db_trans('leadership_assignments') }}</a>
            @endcan
            @can('leadership.positions.view')
                <a class="collapse-item" href="{{ route('leadership.positions.index') }}">{{ db_trans('leadership_positions') }}</a>
            @endcan
        </div>
    </div>
</li>
@endcanany


@canany(['liturgy.mass-types.view', 'liturgy.offering-types.view', 'liturgy.mass-schedules.view'])
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('liturgy.*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" href="#sidebarLiturgy" role="button" aria-expanded="{{ request()->routeIs('liturgy.*') ? 'true' : 'false' }}">
        <i class="bi bi-book"></i>
        <span>Liturgy</span>
    </a>
    <div class="collapse {{ request()->routeIs('liturgy.*') ? 'show' : '' }}" id="sidebarLiturgy">
        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
            @can('liturgy.mass-types.view')
                <li><a href="{{ route('liturgy.mass-types.index') }}" class="nav-link {{ request()->routeIs('liturgy.mass-types.*') ? 'active' : '' }}">Mass Types</a></li>
            @endcan
            @can('liturgy.offering-types.view')
                <li><a href="{{ route('liturgy.offering-types.index') }}" class="nav-link {{ request()->routeIs('liturgy.offering-types.*') ? 'active' : '' }}">Offering Types</a></li>
            @endcan
            @can('liturgy.mass-schedules.view')
                <li><a href="{{ route('liturgy.mass-schedules.index') }}" class="nav-link {{ request()->routeIs('liturgy.mass-schedules.*') ? 'active' : '' }}">Mass Schedules</a></li>
            @endcan
        </ul>
    </div>
</li>
@endcanany

@can('finance.bank-accounts.view')
<li class="nav-item">
    <a href="{{ route('finance.bank-accounts.index') }}" class="nav-link {{ request()->routeIs('finance.bank-accounts.*') ? 'active' : '' }}">
        <i class="bi bi-bank"></i>
        <span>Bank Accounts</span>
    </a>
</li>
@endcan

@can('membership.age-groups.view')
<li class="nav-item">
    <a href="{{ route('membership.age-groups.index') }}" class="nav-link {{ request()->routeIs('membership.age-groups.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Age Groups</span>
    </a>
</li>
@endcan
{{-- Liturgy --}}
@canany(['liturgy.mass-types.view', 'liturgy.offering-types.view', 'liturgy.mass-schedules.view'])
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#sidebarLiturgy" role="button" aria-expanded="false"><span>{{ db_trans('liturgy') }}</span></a>
    <div class="collapse" id="sidebarLiturgy"><ul class="nav flex-column ms-3">
        @can('liturgy.mass-types.view')<li class="nav-item"><a class="nav-link" href="{{ route('liturgy.mass-types.index') }}">{{ db_trans('mass_types') }}</a></li>@endcan
        @can('liturgy.offering-types.view')<li class="nav-item"><a class="nav-link" href="{{ route('liturgy.offering-types.index') }}">{{ db_trans('offering_types') }}</a></li>@endcan
        @can('liturgy.mass-schedules.view')<li class="nav-item"><a class="nav-link" href="{{ route('liturgy.mass-schedules.index') }}">{{ db_trans('mass_schedules') }}</a></li>@endcan
    </ul></div>
</li>
@endcanany

{{-- Finance Setup --}}
@can('finance.bank-accounts.view')
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#sidebarFinanceSetup" role="button" aria-expanded="false"><span>{{ db_trans('finance_setup') }}</span></a>
    <div class="collapse" id="sidebarFinanceSetup"><ul class="nav flex-column ms-3"><li class="nav-item"><a class="nav-link" href="{{ route('finance.bank-accounts.index') }}">{{ db_trans('bank_accounts') }}</a></li></ul></div>
</li>
@endcan

{{-- Membership --}}
@can('membership.age-groups.view')
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#sidebarMembership" role="button" aria-expanded="false"><span>{{ db_trans('membership_classification') }}</span></a>
    <div class="collapse" id="sidebarMembership"><ul class="nav flex-column ms-3"><li class="nav-item"><a class="nav-link" href="{{ route('membership.age-groups.index') }}">{{ db_trans('age_groups') }}</a></li></ul></div>
</li>
@endcan

{{-- Operations --}}
@canany(['operations.service-categories.view', 'operations.service-providers.view', 'operations.asset-categories.view', 'operations.church-assets.view'])
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#sidebarOperations" role="button" aria-expanded="false"><span>{{ db_trans('operations') }}</span></a>
    <div class="collapse" id="sidebarOperations"><ul class="nav flex-column ms-3">
        @can('operations.service-categories.view')<li class="nav-item"><a class="nav-link" href="{{ route('operations.service-categories.index') }}">{{ db_trans('service_categories') }}</a></li>@endcan
        @can('operations.service-providers.view')<li class="nav-item"><a class="nav-link" href="{{ route('operations.service-providers.index') }}">{{ db_trans('service_providers') }}</a></li>@endcan
        @can('operations.asset-categories.view')<li class="nav-item"><a class="nav-link" href="{{ route('operations.asset-categories.index') }}">{{ db_trans('asset_categories') }}</a></li>@endcan
        @can('operations.church-assets.view')<li class="nav-item"><a class="nav-link" href="{{ route('operations.church-assets.index') }}">{{ db_trans('church_assets') }}</a></li>@endcan
    </ul></div>
</li>
@endcanany



            <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('profile.edit') }}">
                    <span class="nav-icon-wrap"><i class="fas fa-user"></i></span>
                    <span>{{ db_trans('profile') }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span class="nav-icon-wrap"><i class="fas fa-cog"></i></span>
                    <span>{{ db_trans('settings') }}</span>
                </a>
            </li>

            @can('finance.view')
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-icon-wrap"><i class="fas fa-wallet"></i></span>
                        <span>{{ db_trans('finance') }}</span>
                    </a>
                </li>
            @endcan

            @can('reports.view')
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-icon-wrap"><i class="fas fa-chart-line"></i></span>
                        <span>{{ db_trans('reports') }}</span>
                    </a>
                </li>
            @endcan
        </ul>

        @role('Super Admin')
            <div class="sidebar-section-title px-4 mt-4 mb-2">
                {{ db_trans('system') }}
            </div>

            <ul class="sidebar-menu list-unstyled px-3 mb-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <span class="nav-icon-wrap"><i class="fas fa-user-shield"></i></span>
                        <span>{{ db_trans('users') }}</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('translations.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('translations.index') }}">
                        <span class="nav-icon-wrap"><i class="fas fa-language"></i></span>
                        <span>{{ db_trans('translations') }}</span>
                    </a>
                </li>
            </ul>
        @endrole

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
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>