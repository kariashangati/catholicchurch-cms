@php
    $assignment = $assignment ?? null;
    $currentType = old('assignable_type');

    if (! $currentType && $assignment) {
        $map = [
            App\Models\Member::class => 'member',
            App\Models\Jumuiya::class => 'jumuiya',
            App\Models\Kanda::class => 'kanda',
            App\Models\ApostolicGroup::class => 'apostolic_group',
            App\Models\LeadershipAssignment::class => 'leadership_assignment',
        ];
        $currentType = $map[$assignment->assignable_type] ?? 'member';
    }

    $currentType = $currentType ?: 'member';
    $assignableSelectId = $modalId . '_assignable_select';
    $assignableSearchId = $modalId . '_assignable_search';
@endphp

<div class="modal fade finance-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content ui-modal-card border-0">
            <form method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                <input type="hidden" name="mass_schedule_id" value="{{ $schedule->id }}">

                <div class="modal-header">
                    <h5 class="modal-title mb-0">{{ $assignment ? db_trans('edit') : db_trans('add_assignment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('role_name') }}</label>
                            <input type="text" name="role_name" class="form-control" value="{{ old('role_name', $assignment->role_name ?? '') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('assignment_type') }}</label>
                            <select name="assignable_type" class="form-select liturgy-assignable-type" data-target="{{ $assignableSelectId }}" data-search="{{ $assignableSearchId }}" required>
                                @foreach(['member', 'jumuiya', 'kanda', 'apostolic_group', 'leadership_assignment'] as $value)
                                    <option value="{{ $value }}" @selected($currentType === $value)>{{ db_trans($value) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('search') }}</label>
                            <input type="text" class="form-control liturgy-assignable-search" id="{{ $assignableSearchId }}" data-target="{{ $assignableSelectId }}" placeholder="{{ db_trans('search') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ db_trans('assignment_target') }}</label>
                            <select name="assignable_id" id="{{ $assignableSelectId }}" class="form-select liturgy-assignable-select" required>
                                @foreach($assignmentTargets as $type => $items)
                                    @foreach($items as $item)
                                        @php
                                            $label = match($type) {
                                                'member' => trim(($item->full_name ?? '') ?: (($item->first_name ?? '') . ' ' . ($item->middle_name ?? '') . ' ' . ($item->last_name ?? ''))),
                                                'leadership_assignment' => (($item->member->full_name ?? $item->member->name ?? $item->member->first_name ?? db_trans('assignment')) . ' - ' . ($item->position->name ?? db_trans('position'))),
                                                default => $item->name,
                                            };
                                            $searchText = strtolower(trim($label . ' ' . ($item->member_code ?? '') . ' ' . ($item->phone ?? '')));
                                            $selectedId = old('assignable_id', $assignment->assignable_id ?? '');
                                        @endphp
                                        <option value="{{ $item->id }}" data-type="{{ $type }}" data-search="{{ $searchText }}" @selected((string) $selectedId === (string) $item->id && $currentType === $type) @if($currentType !== $type) hidden @endif>
                                            {{ $label ?: ($type . ' #' . $item->id) }}
                                            @if($type === 'member' && !empty($item->member_code)) · {{ $item->member_code }} @endif
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes', $assignment->notes ?? '') }}</textarea>
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
