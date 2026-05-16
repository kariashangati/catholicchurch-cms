@extends('layouts.admin')

@section('content')
<div class="container-fluid communication-page">
    <div class="comm-hero mb-4">
        <div>
            <div class="comm-hero-kicker">Automation Engine</div>
            <h2 class="comm-hero-title">{{ db_trans('communication.automations.create') }}</h2>
            <p class="comm-hero-text">{{ db_trans('communication.automations.subtitle') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.communication.automations.store') }}">
        @csrf
        @include('admin.communication.automations._form')

        <div class="d-flex justify-content-end mt-4 gap-2">
            <a href="{{ route('admin.communication.automations.index') }}" class="btn btn-light">Cancel</a>
            <button class="btn comm-primary-btn" type="submit">Save automation</button>
        </div>
    </form>
</div>
@endsection
