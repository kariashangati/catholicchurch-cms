@extends('layouts.admin')

@section('title', db_trans('translations'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/translations-module-v4.css') }}">

    <style>
        .translation-pagination-wrap {
            padding: 0.75rem 1rem;
        }

        .translation-pagination-wrap nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin: 0;
        }

        .translation-pagination-wrap .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .translation-pagination-wrap .page-link {
            padding: 0.35rem 0.65rem;
            font-size: 0.8125rem;
            line-height: 1.2;
            border-radius: 0.55rem;
        }

        .translation-pagination-wrap .page-item:first-child .page-link,
        .translation-pagination-wrap .page-item:last-child .page-link {
            border-radius: 0.55rem;
        }

        .translation-pagination-wrap .small,
        .translation-pagination-wrap p {
            margin: 0;
            font-size: 0.8125rem;
            color: #64748b;
        }
    </style>
@endpush

@section('disable_default_alerts')@endsection

@section('content')
@php
    $translations = $translations ?? collect();
    $filters = $filters ?? [];
    $stats = $stats ?? [];
    $bulkPreview = $bulkPreview ?? null;
@endphp

<div class="admin-ui-v4 translations-module-v4">
    <div class="ui-page-hero mb-4">
        <div class="ui-hero-pattern"></div>

        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-language"></i>
                    {{ db_trans('translations') }}
                </span>

                <h1 class="ui-page-title mt-3">
                    {{ db_trans('translations') }}
                </h1>

                <div class="ui-meta-wrap mt-3">
                    <span class="ui-meta-pill">
                        <i class="fas fa-table me-1"></i>
                        {{ number_format($stats['total_rows'] ?? 0) }} {{ db_trans('records') }}
                    </span>

                    <span class="ui-meta-pill">
                        <i class="fas fa-key me-1"></i>
                        {{ number_format($stats['unique_keys'] ?? 0) }} {{ db_trans('keys') }}
                    </span>

                    <span class="ui-meta-pill ui-meta-pill-warning">
                        <i class="fas fa-filter me-1"></i>
                        {{ number_format($stats['filtered_rows'] ?? 0) }} {{ db_trans('filtered') }}
                    </span>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="ui-actions-grid">
                    <a href="{{ route('translations.create') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('add_translation') }}
                        </span>
                    </a>

                    <a href="{{ route('translations.index', ['pair_status' => 'missing_sw']) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-flag"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('missing_swahili') }}
                        </span>
                    </a>

                    <a href="{{ route('translations.index', ['pair_status' => 'missing_en']) }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-globe"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('missing_english') }}
                        </span>
                    </a>

                    <a href="{{ route('translations.index') }}" class="ui-hero-action">
                        <span class="ui-hero-action-icon">
                            <i class="fas fa-rotate-right"></i>
                        </span>
                        <span class="ui-hero-action-text">
                            {{ db_trans('reset_filters') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="ui-stat-card ui-tone-primary p-4">
                <div class="ui-stat-top">
                    <div class="ui-stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <span class="ui-chip">
                        {{ db_trans('overview') }}
                    </span>
                </div>

                <div class="ui-stat-label">
                    {{ db_trans('total_translations') }}
                </div>

                <div class="ui-stat-value">
                    {{ number_format($stats['total_rows'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="ui-stat-card ui-tone-info p-4">
                <div class="ui-stat-top">
                    <div class="ui-stat-icon">
                        <i class="fas fa-language"></i>
                    </div>
                    <span class="ui-chip">EN</span>
                </div>

                <div class="ui-stat-label">
                    {{ db_trans('english_rows') }}
                </div>

                <div class="ui-stat-value">
                    {{ number_format($stats['en_rows'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="ui-stat-card ui-tone-success p-4">
                <div class="ui-stat-top">
                    <div class="ui-stat-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <span class="ui-chip">SW</span>
                </div>

                <div class="ui-stat-label">
                    {{ db_trans('swahili_rows') }}
                </div>

                <div class="ui-stat-value">
                    {{ number_format($stats['sw_rows'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="ui-stat-card ui-tone-warning p-4">
                <div class="ui-stat-top">
                    <div class="ui-stat-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <span class="ui-chip">
                        {{ db_trans('pairs') }}
                    </span>
                </div>

                <div class="ui-stat-label">
                    {{ db_trans('complete_pairs') }}
                </div>

                <div class="ui-stat-value">
                    {{ number_format($stats['complete_pairs'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-sm-6">
            <div class="ui-mini-card p-4">
                <div class="ui-mini-icon ui-mini-tone-danger">
                    <i class="fas fa-circle-exclamation"></i>
                </div>

                <div class="ui-mini-label">
                    {{ db_trans('missing_english') }}
                </div>

                <div class="ui-mini-value">
                    {{ number_format($stats['missing_en'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-6">
            <div class="ui-mini-card p-4">
                <div class="ui-mini-icon ui-mini-tone-warning">
                    <i class="fas fa-circle-half-stroke"></i>
                </div>

                <div class="ui-mini-label">
                    {{ db_trans('missing_swahili') }}
                </div>

                <div class="ui-mini-value">
                    {{ number_format($stats['missing_sw'] ?? 0) }}
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-sm-12">
            <div class="ui-mini-card p-4">
                <div class="ui-mini-icon ui-mini-tone-primary">
                    <i class="fas fa-filter"></i>
                </div>

                <div class="ui-mini-label">
                    {{ db_trans('filtered_results') }}
                </div>

                <div class="ui-mini-value">
                    {{ number_format($stats['filtered_rows'] ?? 0) }}
                </div>
            </div>
        </div>
    </div>

    <div class="ui-filter-card p-4 mb-4">
        <form method="GET" action="{{ route('translations.index') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label">
                        {{ db_trans('search') }}
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="{{ db_trans('search_by_key_or_value') }}"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">
                        {{ db_trans('locale') }}
                    </label>

                    <select name="locale" class="form-select">
                        <option value="all" @selected(($filters['locale'] ?? 'all') === 'all')>
                            {{ db_trans('all_locales') }}
                        </option>
                        <option value="en" @selected(($filters['locale'] ?? 'all') === 'en')>
                            English
                        </option>
                        <option value="sw" @selected(($filters['locale'] ?? 'all') === 'sw')>
                            Swahili
                        </option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">
                        {{ db_trans('pair_status') }}
                    </label>

                    <select name="pair_status" class="form-select">
                        <option value="all" @selected(($filters['pair_status'] ?? 'all') === 'all')>
                            {{ db_trans('all_records') }}
                        </option>
                        <option value="complete" @selected(($filters['pair_status'] ?? 'all') === 'complete')>
                            {{ db_trans('complete_pairs') }}
                        </option>
                        <option value="missing_en" @selected(($filters['pair_status'] ?? 'all') === 'missing_en')>
                            {{ db_trans('missing_english') }}
                        </option>
                        <option value="missing_sw" @selected(($filters['pair_status'] ?? 'all') === 'missing_sw')>
                            {{ db_trans('missing_swahili') }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">
                        {{ db_trans('per_page') }}
                    </label>

                    <select name="per_page" class="form-select">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 25) === $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-1 d-grid">
                    <button class="ui-btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="ui-filter-card p-4 mb-4">
        <form method="POST" action="{{ route('translations.bulk-replace') }}">
            @csrf

            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">
                        {{ db_trans('find_word') }}
                    </label>

                    <input
                        type="text"
                        name="search_word"
                        class="form-control"
                        value="{{ old('search_word') }}"
                        placeholder="{{ db_trans('find_word') }}"
                        required
                    >
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">
                        {{ db_trans('replace_with') }}
                    </label>

                    <input
                        type="text"
                        name="replace_word"
                        class="form-control"
                        value="{{ old('replace_word') }}"
                        placeholder="{{ db_trans('replace_with') }}"
                        required
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">
                        {{ db_trans('locale') }}
                    </label>

                    <select name="locale" class="form-select">
                        <option value="all" @selected(old('locale', 'all') === 'all')>
                            {{ db_trans('all_locales') }}
                        </option>
                        <option value="en" @selected(old('locale') === 'en')>
                            English
                        </option>
                        <option value="sw" @selected(old('locale') === 'sw')>
                            Swahili
                        </option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">
                        {{ db_trans('match_scope') }}
                    </label>

                    <select name="match_scope" class="form-select">
                        <option value="values_only" @selected(old('match_scope', 'values_only') === 'values_only')>
                            {{ db_trans('values_only') }}
                        </option>
                        <option value="keys_only" @selected(old('match_scope') === 'keys_only')>
                            {{ db_trans('keys_only') }}
                        </option>
                        <option value="keys_and_values" @selected(old('match_scope') === 'keys_and_values')>
                            {{ db_trans('keys_and_values') }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button type="submit" name="bulk_action" value="preview" class="btn ui-btn-light w-100">
                        {{ db_trans('preview') }}
                    </button>

                    <button type="submit" name="bulk_action" value="apply" class="btn ui-btn-primary w-100">
                        {{ db_trans('apply_replacement') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($bulkPreview)
        <div class="ui-table-card p-4 mb-4">
            <div class="ui-section-heading">
                <div>
                    <h5 class="ui-section-title mb-1">
                        {{ db_trans('bulk_replace_preview') }}
                    </h5>
                </div>

                <span class="ui-section-badge">
                    {{ number_format($bulkPreview['affected_rows'] ?? 0) }} {{ db_trans('affected_rows') }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ db_trans('locale') }}</th>
                            <th>{{ db_trans('key') }}</th>
                            <th>{{ db_trans('current_value') }}</th>
                            <th>{{ db_trans('new_value') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(($bulkPreview['rows'] ?? []) as $previewRow)
                            <tr>
                                <td>{{ strtoupper($previewRow['locale']) }}</td>
                                <td class="fw-semibold">{{ $previewRow['translation_key'] }}</td>
                                <td>{{ $previewRow['old_value'] }}</td>
                                <td>{{ $previewRow['new_value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="ui-table-card p-0 overflow-hidden">
        <div class="p-4 border-bottom">
            <div class="ui-section-heading mb-0">
                <div>
                    <h5 class="ui-section-title mb-1">
                        {{ db_trans('translation_records') }}
                    </h5>
                </div>

                <span class="ui-section-badge">
                    {{ number_format(method_exists($translations, 'total') ? $translations->total() : $translations->count()) }}
                    {{ db_trans('rows') }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0" id="translationsTable">
                <thead>
                    <tr>
                        <th>{{ db_trans('id') }}</th>
                        <th>{{ db_trans('locale') }}</th>
                        <th>{{ db_trans('key') }}</th>
                        <th>{{ db_trans('value') }}</th>
                        <th>{{ db_trans('pair_status') }}</th>
                        <th>{{ db_trans('updated_at') }}</th>
                        <th>{{ db_trans('actions') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($translations as $translation)
                        @php
                            $hasEnPair = (int) ($translation->has_en_pair ?? 0) === 1;
                            $hasSwPair = (int) ($translation->has_sw_pair ?? 0) === 1;
                            $pairComplete = $hasEnPair && $hasSwPair;

                            if ($pairComplete) {
                                $statusClass = 'ui-status-success';
                                $statusLabel = db_trans('complete_pairs');
                            } elseif (! $hasEnPair) {
                                $statusClass = 'ui-status-warning';
                                $statusLabel = db_trans('missing_english');
                            } elseif (! $hasSwPair) {
                                $statusClass = 'ui-status-warning';
                                $statusLabel = db_trans('missing_swahili');
                            } else {
                                $statusClass = 'ui-status-warning';
                                $statusLabel = db_trans('missing_pair');
                            }
                        @endphp

                        <tr>
                            <td>{{ $translation->id }}</td>

                            <td>
                                <span class="ui-status-pill {{ $translation->locale === 'en' ? 'ui-status-info' : 'ui-status-success' }}">
                                    {{ strtoupper($translation->locale) }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-bold">
                                    {{ $translation->translation_key }}
                                </div>
                            </td>

                            <td>
                                <div class="translation-value-preview">
                                    {{ $translation->translation_value }}
                                </div>
                            </td>

                            <td>
                                <span class="ui-status-pill {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td>
                                {{ optional($translation->updated_at)->format('d M Y H:i') ?: '—' }}
                            </td>

                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('translations.edit', $translation) }}" class="btn btn-sm ui-btn-light">
                                        {{ db_trans('edit') }}
                                    </a>

                                    <form method="POST" action="{{ route('translations.destroy', $translation) }}" class="delete-translation-form">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm ui-btn-danger">
                                            {{ db_trans('delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                {{ db_trans('no_translations_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($translations, 'links') && $translations->hasPages())
            <div class="translation-pagination-wrap border-top">
                {{ $translations->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-translation-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    form.submit();
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: @json(db_trans('are_you_sure')),
                    text: @json(db_trans('delete_this_translation')),
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#7c3aed',
                    confirmButtonText: @json(db_trans('delete')),
                    cancelButtonText: @json(db_trans('cancel'))
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush