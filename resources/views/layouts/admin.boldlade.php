<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="app-success-message" content="{{ session('success') ?: session('status') }}">
    <meta name="app-error-message" content="{{ session('error', '') }}">

    <title>@yield('title', config('app.name', 'ChurchMS'))</title>

    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/custom-admin.css') }}" rel="stylesheet">

    {{-- Global admin/module styles --}}
    <link rel="stylesheet" href="{{ asset('admin/css/sacraments-module-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-kanda.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-jumuiya.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-jumuiya-report.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-apostolic-groups.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-familia.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/tithes-module.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/project-finance.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/finance-contributions.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/finance-reports-budget.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/reports.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/leadership-module.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/dashboard-v2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-members-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/jumuiya-module-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-apostolic-groups-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/admin-familia-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/mafundisho-module-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/finance-dashboard-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/project-finance-v4.css') }}">


    {{-- v4 unified styles --}}
    <link rel="stylesheet" href="{{ asset('admin/css/admin-ui-v4.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/offerings-module-v4.css') }}">

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-app" id="page-top">
        @include('layouts.partials.sidebar')

        <div class="admin-main">
            @include('layouts.partials.header')

            <main class="admin-page-content">
                <div class="container-fluid py-4">
                    @if((session('success') || session('status')) && !View::hasSection('disable_default_alerts'))
                        <div class="alert alert-success border-0 shadow-sm admin-alert d-none">
                            {{ session('success') ?: session('status') }}
                        </div>
                    @endif

                    @if(session('error') && !View::hasSection('disable_default_alerts'))
                        <div class="alert alert-danger border-0 shadow-sm admin-alert d-none">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any() && !View::hasSection('disable_default_alerts'))
                        <div class="alert alert-danger border-0 shadow-sm admin-alert d-none">
                            <strong>{{ db_trans('please_fix_the_following_errors') }}</strong>
                            <ul class="mb-0 mt-2 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>

    {{-- Core scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>

    {{-- Keep your partial scripts after jQuery/bootstrap --}}
    @include('layouts.partials.scripts')

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Global alert handling --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = document.querySelector('meta[name="app-success-message"]')?.getAttribute('content') || '';
            const errorMessage = document.querySelector('meta[name="app-error-message"]')?.getAttribute('content') || '';
            const hasErrors = @json($errors->any());
            const errorMessages = @json($errors->all());

            if (successMessage.trim() !== '') {
                Swal.fire({
                    icon: 'success',
                    title: @json(db_trans('success')),
                    text: successMessage,
                    confirmButtonColor: '#7c3aed',
                    timer: 2600,
                    timerProgressBar: true
                });
            }

            if (errorMessage.trim() !== '') {
                Swal.fire({
                    icon: 'error',
                    title: @json(db_trans('error')),
                    text: errorMessage,
                    confirmButtonColor: '#7c3aed'
                });
            }

            if (hasErrors && errorMessages.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: @json(db_trans('please_fix_the_following_errors')),
                    html: '<div style="text-align:left;">' + errorMessages.map(function (e) {
                        return '• ' + e;
                    }).join('<br>') + '</div>',
                    confirmButtonColor: '#7c3aed'
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>