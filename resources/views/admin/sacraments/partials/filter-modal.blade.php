@php
    $modalId = $modalId ?? 'filterModal';
    $action = $action ?? url()->current();

    $selectedGender = strtolower((string) request('gender'));
    $selectedSacrament = request('sacrament', 'all');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg sacrament-filter-card overflow-hidden">
            <form method="GET" action="{{ $action }}">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">{{ db_trans('filter_report') }}</h5>
                        <div class="text-muted small">{{ db_trans('refine_sacrament_report_results') }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ db_trans('search') }}</label>
                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="{{ db_trans('search_members_or_code') }}"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ db_trans('gender') }}</label>
                            <select name="gender" class="form-select">
                                <option value="">{{ db_trans('all') }}</option>

                                <option
                                    value="mwanaume"
                                    @selected(in_array($selectedGender, ['male', 'mwanaume'], true))
                                >
                                    {{ db_trans('male') }}
                                </option>

                                <option
                                    value="mwanamke"
                                    @selected(in_array($selectedGender, ['female', 'mwanamke'], true))
                                >
                                    {{ db_trans('female') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ db_trans('sacrament_status') }}</label>
                            <select name="sacrament" class="form-select">
                                <option value="all" @selected($selectedSacrament === 'all')>
                                    {{ db_trans('all') }}
                                </option>

                                <option value="baptized" @selected($selectedSacrament === 'baptized')>
                                    {{ db_trans('baptized') }}
                                </option>

                                <option value="communion" @selected($selectedSacrament === 'communion')>
                                    {{ db_trans('communion') }}
                                </option>

                                <option value="confirmation" @selected($selectedSacrament === 'confirmation')>
                                    {{ db_trans('confirmation') }}
                                </option>

                                <option value="married" @selected($selectedSacrament === 'married')>
                                    {{ db_trans('married') }}
                                </option>

                                <option value="eucharist" @selected($selectedSacrament === 'eucharist')>
                                    {{ db_trans('receiving_eucharist') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            value="1"
                            name="active_only"
                            id="{{ $modalId }}_active_only"
                            @checked(request('active_only') === '1')
                        >
                        <label class="form-check-label" for="{{ $modalId }}_active_only">
                            {{ db_trans('show_active_members_only') }}
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <a href="{{ $action }}" class="btn btn-light rounded-pill px-3">
                        {{ db_trans('reset_filters') }}
                    </a>

                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        {{ db_trans('cancel') }}
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill px-3">
                        {{ db_trans('apply_filters') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>