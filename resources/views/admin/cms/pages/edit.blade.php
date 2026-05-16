@extends('layouts.admin')

@section('title', db_trans('edit_page'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    @include('admin.cms.pages.partials.form', [
        'pageTitle' => db_trans('edit_page'),
        'pageSubtitle' => db_trans('manage_static_pages_and_menu_footer_visibility'),
        'action' =>