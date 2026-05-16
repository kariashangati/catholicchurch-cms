@php
    $record = $record ?? null;
@endphp

<div class="mb-3">
    <label class="form-label">{{ db_trans('source_type') }}</label>
    <select name="source_type" class="form-select" required>
        @foreach($sourceOptions as $option)
            <option value="{{ $option }}" @selected(old('source_type', $record?->source_type) === $option)>
                {{ db_trans($option) }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label class="form-label">{{ db_trans('category') }}</label>
    <input
        type="text"
        name="category_name"
        class="form-control"
        value="{{ old('category_name', $record?->category_name) }}"
        list="incomeCategories{{ $record->id ?? 'new' }}"
        required
    >
    <datalist id="incomeCategories{{ $record->id ?? 'new' }}">
        @foreach($suggestedCategories as $category)
            <option value="{{ $category }}"></option>
        @endforeach
    </datalist>
</div>

<div class="mb-3">
    <label class="form-label">{{ db_trans('group') }}</label>
    <select name="category_group" class="form-select" required>
        @foreach($groupOptions as $option)
            <option value="{{ $option }}" @selected(old('category_group', $record?->category_group) === $option)>
                {{ db_trans($option) }}
            </option>
        @endforeach
    </select>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ db_trans('amount') }}</label>
        <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount', $record?->amount) }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">{{ db_trans('year') }}</label>
        <input type="number" name="budget_year" class="form-control" value="{{ old('budget_year', $record?->budget_year ?? $year) }}" required>
    </div>
</div>

<div class="mt-3">
    <label class="form-label">{{ db_trans('notes') }}</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $record?->notes) }}</textarea>
</div>
