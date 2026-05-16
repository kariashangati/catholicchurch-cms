@extends('layouts.admin')

@section('content')
<div class="container-fluid communication-page">
    <div class="comm-hero mb-4">
        <div>
            <div class="comm-hero-kicker">{{ db_trans('communication.automations.title') }}</div>
            <h2 class="comm-hero-title">{{ db_trans('communication.automations.title') }}</h2>
            <p class="comm-hero-text">{{ db_trans('communication.automations.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.communication.automations.create') }}" class="btn comm-primary-btn">
            <i class="bi bi-magic"></i> {{ db_trans('communication.automations.create') }}
        </a>
    </div>

    <div class="comm-panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Event</th>
                        <th>Template</th>
                        <th>Mode</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($automations as $automation)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $automation->name }}</div>
                            <div class="text-muted small">{{ $automation->code }}</div>
                        </td>
                        <td>{{ $eventOptions[$automation->event_key] ?? $automation->event_key }}</td>
                        <td>{{ $automation->template?->name ?? '—' }}</td>
                        <td><span class="comm-pill">{{ $automation->trigger_mode }}</span></td>
                        <td>
                            <span class="comm-status {{ $automation->is_enabled ? 'is-active' : 'is-muted' }}">
                                {{ $automation->is_enabled ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.communication.automations.edit', $automation) }}">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.communication.automations.toggle', $automation) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No automation rules yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $automations->links() }}
        </div>
    </div>
</div>
@endsection
