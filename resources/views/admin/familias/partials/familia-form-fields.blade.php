@php
    $prefix = $prefix ?? '';
    $mode = $mode ?? 'create';
    $model = $model ?? null;
    $kandas = $kandas ?? collect();
    $jumuiyas = $jumuiyas ?? collect();

    $selectedJumuiyaId = old('jumuiya_id', $model?->jumuiya_id);
    $selectedJumuiya = $selectedJumuiyaId ? $jumuiyas->firstWhere('id', (int) $selectedJumuiyaId) : null;
    $selectedKandaId = old('kanda_id', $selectedJumuiya?->kanda_id);
@endphp

<div class="row g-4">
    <div class="col-lg-7">
        <div class="familia-form-card">
            <div class="familia-form-card-title">{{ db_trans('basic_information') }}</div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('family_name') }}</label>
                    <input type="text" name="name" id="{{ $prefix }}name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $model?->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
<div class="col-md-6">
    <label class="form-label">{{ db_trans('kanda') }}</label>
    <select name="kanda_id" id="{{ $prefix }}kanda_id" class="form-select js-familia-kanda-filter" required>
        <option value="">{{ db_trans('select_kanda') }}</option>
        @foreach($kandas as $kanda)
            <option value="{{ $kanda->id }}" @selected((string) $selectedKandaId === (string) $kanda->id)>
                {{ $kanda->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="col-md-6 js-familia-jumuiya-wrap {{ $selectedKandaId ? '' : 'd-none' }}">
    <label class="form-label">{{ db_trans('jumuiya') }}</label>
    <select name="jumuiya_id" id="{{ $prefix }}jumuiya_id" class="form-select js-familia-jumuiya-select @error('jumuiya_id') is-invalid @enderror" required>
        <option value="">{{ db_trans('select_jumuiya') }}</option>
        @foreach($jumuiyas as $jumuiya)
            <option
                value="{{ $jumuiya->id }}"
                data-kanda-id="{{ $jumuiya->kanda_id }}"
                @selected((string) $selectedJumuiyaId === (string) $jumuiya->id)
            >
                {{ $jumuiya->name }}
            </option>
        @endforeach
    </select>
    @error('jumuiya_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('phone') }}</label>
                    <input type="text" name="phone" id="{{ $prefix }}phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $model?->phone) }}">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ db_trans('envelope_no') }}</label>
                    <input type="text" name="envelope_no" id="{{ $prefix }}envelope_no" class="form-control @error('envelope_no') is-invalid @enderror" value="{{ old('envelope_no', $model?->envelope_no) }}">
                    @error('envelope_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">{{ db_trans('family_address') }}</label>
                    <input type="text" name="address" id="{{ $prefix }}address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $model?->address) }}">
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="familia-form-card">
            <div class="familia-form-card-title">{{ db_trans('contact_and_status') }}</div>

            <div class="familia-form-feature mb-3">
                <div>
                    <div class="familia-form-feature-title">{{ db_trans('active_family') }}</div>
                    <div class="familia-form-feature-text">{{ db_trans('family_profile_completion_hint') }}</div>
                </div>
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" value="1" name="is_active" id="{{ $prefix }}is_active" @checked(old('is_active', $model?->is_active ?? true))>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label">{{ db_trans('notes') }}</label>
                <textarea name="notes" id="{{ $prefix }}notes" rows="8" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $model?->notes) }}</textarea>
                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>