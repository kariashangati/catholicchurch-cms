@extends('layouts.admin')

@section('title', db_trans('profile'))

@section('content')
    <div class="row">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card shadow-sm border-0 border-left-danger">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection