@extends('layouts.admin')

@section('title', db_trans('communication.templates.title'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/communication-templates.css') }}">
@endpush

@section('content')
<div class="communication-templates-page">
    <div class="dashboard-hero communication-hero mb-4">
        <div class="hero-pattern"></div>
        <div class="row align-items-center g-4 position-relative">
            <div class="col-lg-8">
                <span class="dashboard-hero-badge">{{ db_trans('communication.templates.hero_badge') }}</span>
                <h2 class="dashboard-title mb-2">{{ db_trans('communication.templates.title') }}</h2>
                <p class="dashboard-subtitle mb-3">{{ db_trans('communication.templates.subtitle') }}</p>
                <div class="hero-meta-wrap">
                    <span class="hero-pill"><i class="fas fa-language me-2"></i>{{ db_trans('communication.templates.locale_ready') }}</span>
                    <span class="hero-pill hero-pill-warning"><i class="fas fa-sms me-2"></i>{{ db_trans('communication.templates.sms_only') }}</span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hero-actions-grid">
                    <a href="{{ route('admin.communication.templates.create') }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-plus"></i></span>
                        <span class="hero-action-text">{{ db_trans('communication.templates.create_action') }}</span>
                    </a>
                    <a href="{{ route('admin.communication.templates.index', ['status' => 'active']) }}" class="hero-action-btn">
                        <span class="hero-action-icon"><i class="fas fa-check-circle"></i></span>
                        <span class="hero-action-text">{{ db_trans('communication.templates.active_action') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-card-purple h-100 border-0">
                <div class="card-body">
                    <div class="stat-top-row"><div class="stat-icon"><i class="fas fa-layer-group"></i></div><span class="stat-chip">{{ db_trans('overview') }}</span></div>
                    <div class="stat-label">{{ db_trans('communication.templates.kpi_all') }}</div>
                    <div class="stat-number">{{ $stats['all'] }}</div>
                    <div class="stat-meta">{{ db_trans('communication.templates.kpi_all_meta') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-card-green h-100 border-0">
                <div class="card-body">
                    <div class="stat-top-row"><div class="stat-icon"><i class="fas fa-check"></i></div><span class="stat-chip">{{ db_trans('overview') }}</span></div>
                    <div class="stat-label">{{ db_trans('communication.templates.kpi_active') }}</div>
                    <div class="stat-number">{{ $stats['active'] }}</div>
                    <div class="stat-meta">{{ db_trans('communication.templates.kpi_active_meta') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-card-orange h-100 border-0">
                <div class="card-body">
                    <div class="stat-top-row"><div class="stat-icon"><i class="fas fa-pen"></i></div><span class="stat-chip">{{ db_trans('overview') }}</span></div>
                    <div class="stat-label">{{ db_trans('communication.templates.kpi_draft') }}</div>
                    <div class="stat-number">{{ $stats['draft'] }}</div>
                    <div class="stat-meta">{{ db_trans('communication.templates.kpi_draft_meta') }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card stat-card-blue h-100 border-0">
                <div class="card-body">
                    <div class="stat-top-row"><div class="stat-icon"><i class="fas fa-flag"></i></div><span class="stat-chip">{{ db_trans('overview') }}</span></div>
                    <div class="stat-label">{{ db_trans('communication.templates.kpi_sw') }}</div>
                    <div class="stat-number">{{ $stats['sw'] }}</div>
                    <div class="stat-meta">{{ db_trans('communication.templates.kpi_sw_meta') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 filter-card communication-filter-card mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">{{ db_trans('communication.templates.search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ db_trans('communication.templates.search_placeholder') }}">
                </div>
                <div class="col-lg-2">
                    <label class="form-label">{{ db_trans('communication.templates.status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('communication.templates.all_statuses') }}</option>
                        <option value="draft" @selected(request('status') === 'draft')>{{ db_trans('communication.templates.status_draft') }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ db_trans('communication.templates.status_active') }}</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>{{ db_trans('communication.templates.status_inactive') }}</option>
                        <option value="archived" @selected(request('status') === 'archived')>{{ db_trans('communication.templates.status_archived') }}</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">{{ db_trans('communication.templates.locale') }}</label>
                    <select name="locale" class="form-select">
                        <option value="">{{ db_trans('communication.templates.all_locales') }}</option>
                        <option value="sw" @selected(request('locale') === 'sw')>Swahili</option>
                        <option value="en" @selected(request('locale') === 'en')>English</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">{{ db_trans('communication.templates.category') }}</label>
                    <input type="text" name="category" class="form-control" value="{{ request('category') }}" placeholder="thank_you">
                </div>
                <div class="col-lg-2 d-grid">
                    <button class="btn btn-primary"><i class="fas fa-filter me-2"></i>{{ db_trans('communication.templates.filter_action') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($templates as $template)
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 template-grid-card h-100">
                    <div class="card-body p-4">
                        <div class="template-grid-top mb-3">
                            <div>
                                <span class="template-locale-badge">{{ strtoupper($template->locale) }}</span>
                                <span class="template-status-badge template-status-{{ $template->status }}">{{ db_trans('communication.templates.status_'.$template->status) }}</span>
                            </div>
                            <div class="template-icon-wrap">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>
                        <h5 class="template-grid-title">{{ $template->name }}</h5>
                        <div class="template-grid-meta mb-3">{{ $template->code }} · {{ $template->category }}</div>
                        <p class="template-grid-body">{{ \Illuminate\Support\Str::limit($template->body, 135) }}</p>

                        <div class="template-grid-foot d-flex justify-content-between align-items-center mt-4">
                            <small class="text-muted">{{ $template->variables ? count($template->variables) : 0 }} {{ db_trans('communication.templates.variables_count') }}</small>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.communication.templates.show', $template) }}" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.communication.templates.edit', $template) }}" class="btn btn-outline-secondary"><i class="fas fa-pen"></i></a>
                                @if(!$template->is_system)
                                    <form method="POST" action="{{ route('admin.communication.templates.destroy', $template) }}" onsubmit="return confirm('{{ db_trans('communication.templates.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 template-empty-card">
                    <div class="card-body p-5 text-center">
                        <div class="template-empty-icon mb-3"><i class="fas fa-comment-slash"></i></div>
                        <h4>{{ db_trans('communication.templates.empty_title') }}</h4>
                        <p class="text-muted mb-4">{{ db_trans('communication.templates.empty_text') }}</p>
                        <a href="{{ route('admin.communication.templates.create') }}" class="btn btn-primary">{{ db_trans('communication.templates.create_action') }}</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $templates->links() }}</div>
</div>
@endsection
