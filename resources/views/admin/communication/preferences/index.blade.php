@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">{{ db_trans('communication.preferences.title') }}</h1>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ db_trans('communication.preferences.member') }}</th>
                        <th>{{ db_trans('communication.preferences.phone') }}</th>
                        <th>{{ db_trans('communication.preferences.locale') }}</th>
                        <th>{{ db_trans('communication.preferences.allow_sms') }}</th>
                        <th>{{ db_trans('communication.preferences.status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        @php($preference = $member->communicationPreference)
                        <tr>
                            <td>{{ $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}</td>
                            <td>{{ $preference?->preferred_phone ?: $member->phone }}</td>
                            <td>{{ strtoupper($preference?->preferred_locale ?: app()->getLocale()) }}</td>
                            <td>{{ $preference?->allow_sms ? db_trans('communication.common.yes') : db_trans('communication.common.no') }}</td>
                            <td>
                                @if($preference?->opted_out_at)
                                    <span class="badge bg-danger">{{ db_trans('communication.preferences.opted_out') }}</span>
                                @else
                                    <span class="badge bg-success">{{ db_trans('communication.preferences.active') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.communication.preferences.edit', $member) }}" class="btn btn-sm btn-primary">
                                    {{ db_trans('communication.common.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $members->links() }}
    </div>
</div>
@endsection
