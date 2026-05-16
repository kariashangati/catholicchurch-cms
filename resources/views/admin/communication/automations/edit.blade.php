@extends('layouts.admin')

@section('content')
<div class="container-fluid communication-page">
    <div class="comm-hero mb-4">
        <div>
            <div class="comm-hero-kicker">Automation Engine</div>
            <h2 class="comm-hero-title">Edit automation</h2>
            <p class="comm-hero-text">{{ db_trans('communication.automations.subtitle') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.communication.automations.update', $automation) }}">
        @csrf
        @method('PUT')
        @include('admin.communication.automations._form')

        <div class="d-flex justify-content-between mt-4">
            <form method="POST" action="{{ route('admin.communication.automations.destroy', $automation) }}" onsubmit="return confirm('Delete this automation?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Delete</button>
            </form>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.communication.automations.index') }}" class="btn btn-light">Back</a>
                <button class="btn comm-primary-btn" type="submit">Update automation</button>
            </div>
        </div>
    </form>
</div>
@endsection
