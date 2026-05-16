@extends('layouts.admin')

@section('title', db_trans('create_history'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    @include('admin.cms.histories.partials.form', [
        'pageTitle' => db_trans('create_history'),
        'action' => route('cms.histories.store'),
        'method' => 'POST',
        'history' => null,
    ])
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/cms.js') }}"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '.cms-richtext',
            height: 520,
            menubar: true,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat code fullscreen preview help'
        });
    </script>
@endpush
