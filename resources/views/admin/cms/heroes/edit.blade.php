@extends('layouts.admin')

@section('title', db_trans('edit_hero_banner'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    @include('admin.cms.heroes.partials.form', [
        'pageTitle' => db_trans('edit_hero_banner'),
        'action' => route('cms.heroes.update', $heroBanner),
        'method' => 'PUT',
        'heroBanner' => $heroBanner,
    ])
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
@endpush
