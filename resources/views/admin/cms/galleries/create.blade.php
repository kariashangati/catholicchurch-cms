@extends('layouts.admin')

@section('title', db_trans('create_gallery'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    @include('admin.cms.galleries.partials.form', [
        'pageTitle' => db_trans('create_gallery'),
        'action' => route('cms.galleries.store'),
        'method' => 'POST',
        'gallery' => null,
    ])
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
@endpush
