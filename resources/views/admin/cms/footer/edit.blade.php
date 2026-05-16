@extends('layouts.admin')

@section('title', db_trans('footer_center_details'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-hero cms-hero-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="cms-hero-badge"><i class="fas fa-address-card"></i>{{ db_trans('footer_center_details') }}</span>
                    <h2 class="cms-hero-title">{{ db_trans('footer_center_details') }}</h2>
                </div>

                <a href="{{ route('cms.dashboard') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>{{ db_trans('content_management_center') }}
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('cms.footer.update') }}">
            @csrf
            @method('PUT')

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('footer_content') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('footer_tagline') }}</label>
                            <input type="text" name="footer.tagline" value="{{ old('footer.tagline', $settings['footer.tagline'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('footer_bottom_note') }}</label>
                            <input type="text" name="footer.bottom_note" value="{{ old('footer.bottom_note', $settings['footer.bottom_note'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('newsletter_text') }}</label>
                            <textarea name="footer.newsletter_text" rows="3" class="form-control">{{ old('footer.newsletter_text', $settings['footer.newsletter_text'] ?? '') }}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label">{{ db_trans('newsletter_note') }}</label>
                            <textarea name="footer.newsletter_note" rows="3" class="form-control">{{ old('footer.newsletter_note', $settings['footer.newsletter_note'] ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('footer_description') }}</label>
                            <textarea name="footer.description" rows="4" class="form-control">{{ old('footer.description', $settings['footer.description'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('social_links') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">Facebook</label>
                            <input type="text" name="footer.facebook_url" value="{{ old('footer.facebook_url', $settings['footer.facebook_url'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Instagram</label>
                            <input type="text" name="footer.instagram_url" value="{{ old('footer.instagram_url', $settings['footer.instagram_url'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">YouTube</label>
                            <input type="text" name="footer.youtube_url" value="{{ old('footer.youtube_url', $settings['footer.youtube_url'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">TikTok</label>
                            <input type="text" name="footer.tiktok_url" value="{{ old('footer.tiktok_url', $settings['footer.tiktok_url'] ?? '') }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card cms-form-card">
                <div class="card-body">
                    <div class="cms-section-title">{{ db_trans('center_contact_details') }}</div>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('email') }}</label>
                            <input type="email" name="church.email" value="{{ old('church.email', $settings['church.email'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('phone') }}</label>
                            <input type="text" name="church.phone" value="{{ old('church.phone', $settings['church.phone'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">{{ db_trans('working_hours') }}</label>
                            <input type="text" name="contact.working_hours" value="{{ old('contact.working_hours', $settings['contact.working_hours'] ?? '') }}" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('address') }}</label>
                            <textarea name="church.address" rows="3" class="form-control">{{ old('church.address', $settings['church.address'] ?? '') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ db_trans('map_embed') }}</label>
                            <textarea name="contact.map_embed" rows="4" class="form-control">{{ old('contact.map_embed', $settings['contact.map_embed'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cms-sticky-actions d-flex justify-content-end gap-2">
                <a href="{{ route('cms.dashboard') }}" class="btn btn-outline-secondary px-4">{{ db_trans('cancel') }}</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>{{ db_trans('save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
@endpush
