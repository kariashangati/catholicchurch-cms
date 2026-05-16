@extends('layouts.admin')

@section('title', db_trans('communication.templates.create_title'))

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
                <h2 class="dashboard-title mb-2">{{ db_trans('communication.templates.create_title') }}</h2>
                <p class="dashboard-subtitle mb-3">{{ db_trans('communication.templates.create_subtitle') }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.communication.templates.store') }}">
        @csrf
        @include('admin.communication.templates.partials_form', ['submitLabel' => db_trans('communication.templates.save_action')])
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/communication-templates.js') }}"></script>
@endpush
