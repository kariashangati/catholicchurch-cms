@php
    $position = $position ?? null;
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
                    <div><h5 class="modal-title mb-1">{{ $title }}</h5></div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $position->name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('slug') }}</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $position->slug ?? '') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('scope') }}</label>
                            <select name="level_type" class="form-select" required>
                                @foreach(['parish', 'kanda', 'jumuiya', 'apostolic_group', 'family', 'other'] as $scope)
                                    <option value="{{ $scope }}" @selected(old('level_type', $position->level_type ?? 'parish') === $scope)>{{ db_trans($scope) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('committee_type') }}</label>
                            <select name="committee_type" class="form-select" required>
                                @foreach(['executive', 'council', 'secretariat', 'other'] as $type)
                                    <option value="{{ $type }}" @selected(old('committee_type', $position->committee_type ?? 'executive') === $type)>{{ db_trans($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('auto_role') }}</label>
                            <input type="text" name="auto_role_name" class="form-control" value="{{ old('auto_role_name', $position->auto_role_name ?? '') }}" placeholder="leadership.parish_chairman">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ db_trans('display_order') }}</label>
                            <input type="number" name="display_order" min="0" class="form-control" value="{{ old('display_order', $position->display_order ?? 0) }}">
                        </div>

                        <div class="col-md-8 d-flex flex-wrap align-items-end gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="is_system" id="is_system_{{ $modalId }}" @checked(old('is_system', $position->is_system ?? false))>
                                <label class="form-check-label" for="is_system_{{ $modalId }}">{{ db_trans('system_position') }}</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="is_active" id="is_active_{{ $modalId }}" @checked(old('is_active', $position->is_active ?? true))>
                                <label class="form-check-label" for="is_active_{{ $modalId }}">{{ db_trans('active') }}</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('description') }}</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $position->description ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
