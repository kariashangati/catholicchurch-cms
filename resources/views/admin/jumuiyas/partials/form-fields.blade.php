<div class="row g-4">
    <div class="col-lg-7">
        <label class="form-label fw-semibold">{{ db_trans('jumuiya') }}</label>
        <input
            type="text"
            name="name"
            class="form-control"
            value="{{ $valueName ?? old('name') }}"
            placeholder="{{ db_trans('enter_jumuiya_name') }}"
            required
        >
        <div class="form-text">{{ db_trans('jumuiya_name_helper_text') }}</div>
    </div>

    <div class="col-lg-5">
        <label class="form-label fw-semibold">{{ db_trans('kanda') }}</label>
        <select name="kanda_id" class="form-select" required>
            <option value="">{{ db_trans('select_kanda') }}</option>
            @foreach($kandas as $kanda)
                <option value="{{ $kanda->id }}" @selected((string) ($selectedKandaId ?? old('kanda_id')) === (string) $kanda->id)>
                    {{ $kanda->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-8">
        <label class="form-label fw-semibold">{{ db_trans('comment') }}</label>
        <textarea
            name="comment"
            rows="4"
            class="form-control"
            placeholder="{{ db_trans('jumuiya_comment_helper_text') }}"
        >{{ $valueComment ?? old('comment') }}</textarea>
    </div>

    <div class="col-lg-4">
        <label class="form-label fw-semibold">{{ db_trans('upload_image') }}</label>
        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">

        <div class="jumuiya-form-preview mt-3 {{ empty($imageUrl ?? null) ? 'd-none' : '' }}" data-image-preview-wrapper>
            <div class="jumuiya-form-preview-label">{{ db_trans('community_image') }}</div>
            <img src="{{ $imageUrl ?? '' }}" alt="Preview" class="jumuiya-form-preview-image" data-image-preview>
        </div>
    </div>

    <div class="col-12">
        <div class="admin-ui-switch-row">
            <div>
                <div class="fw-semibold">{{ db_trans('active_status') }}</div>
                <div class="text-muted small">{{ db_trans('jumuiya_active_status_helper_text') }}</div>
            </div>
            <div class="form-check form-switch m-0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ (bool) ($selectedActive ?? old('is_active', true)) ? 'checked' : '' }}
                >
            </div>
        </div>
    </div>
</div>
