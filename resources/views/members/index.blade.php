@extends('layouts.admin')

@section('title', db_trans('members'))

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">{{ db_trans('members_list') }}</h1>
            <p class="mb-0 text-muted">{{ db_trans('manage_all_church_members_here') }}</p>
        </div>

        @can('members.create')
            <a href="{{ route('members.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm mr-1"></i>
                {{ db_trans('add_member') }}
            </a>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ db_trans('id') }}</th>
                            <th>{{ db_trans('first_name') }}</th>
                            <th>{{ db_trans('last_name') }}</th>
                            <th>{{ db_trans('phone') }}</th>
                            <th>{{ db_trans('gender') }}</th>
                            <th>{{ db_trans('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                            <tr>
                                <td class="font-weight-bold text-gray-700">{{ $member->id }}</td>
                                <td>{{ $member->first_name }}</td>
                                <td>{{ $member->last_name }}</td>
                                <td>{{ $member->phone ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $member->gender === 'Male' ? 'badge-primary' : 'badge-info' }} px-3 py-2">
                                        {{ $member->gender }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap" style="gap: 8px;">
                                        @can('members.update')
                                            <a href="{{ route('members.edit', $member) }}" class="btn btn-warning btn-sm">
                                                {{ db_trans('edit') }}
                                            </a>
                                        @endcan

                                        @can('members.delete')
                                            <form method="POST"
                                                  action="{{ route('members.destroy', $member) }}"
                                                  onsubmit="return confirm('{{ db_trans('delete_this_member') }}')">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">
                                                    {{ db_trans('delete') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    {{ db_trans('no_members_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($members, 'links'))
                <div class="mt-3">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection