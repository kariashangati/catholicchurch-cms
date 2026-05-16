@php
    $schedule = $schedule ?? null;
    $statusOptions = $statusOptions ?? \App\Models\MassSchedule::availableStatuses();
    $selectedStatus = old('status', $schedule ? \App\Models\MassSchedule::normalizeStatus($schedule->status ?? null) : \App\Models\MassSchedule::STATUS_DRAFT);
@endphp

<div class="modal fade finance-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-header">
                    <h5 class="modal-title mb-0">{{ $schedule ? db_trans('edit') : db_trans('new_mass_schedule') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('title') }}</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $schedule->title ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('mass_type') }}</label>
                            <select name="mass_type_id" class="form-select" required>
                                <option value="">{{ db_trans('select_option') }}</option>
                                @foreach($massTypes as $massType)
                                    <option value="{{ $massType->id }}" @selected((string) old('mass_type_id', $schedule->mass_type_id ?? '') === (string) $massType->id)>
                                        {{ $massType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('date') }}</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $schedule && $schedule->scheduled_at ? $schedule->scheduled_at->format('Y-m-d\TH:i') : '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('location') }}</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $schedule->location ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('status') }}</label>
                            <select name="status" class="form-select" required>
                                @foreach($statusOptions as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ db_trans($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="special_occasion" value="1" id="{{ $modalId }}_special" @checked(old('special_occasion', $schedule->special_occasion ?? false))>
                                <label class="form-check-label" for="{{ $modalId }}_special">{{ db_trans('special_occasion') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $schedule->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
