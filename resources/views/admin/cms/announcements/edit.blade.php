@extends('layouts.admin')

@section('title', db_trans('edit_announcement'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    @include('admin.cms.announcements.partials.form', [
        'pageTitle' => db_trans('edit_announcement'),
        'pageSubtitle' => db_trans('manage_announcements_and_public_notices'),
        'action' => route('cms.announcements.update', $announcement),
        'method' => 'PUT',
        'announcement' => $announcement,
    ])
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '.cms-richtext',
            height: 420,
            menubar: false,
            plugins: 'lists link image table code fullscreen preview',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | alignleft aligncenter alignright | code fullscreen preview'
        });
    </script>
@endpush