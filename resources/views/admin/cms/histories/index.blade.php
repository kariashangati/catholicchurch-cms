@extends('layouts.admin')

@section('title', db_trans('histories'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-page-head">
            <div>
                <h2 class="cms-page-title">{{ db_trans('histories') }}</h2>
                <p class="cms-page-subtitle">{{ db_trans('manage_history_entries_and_story_content') }}</p>
            </div>

            @can('cms.histories.create')
                <a href="{{ route('cms.histories.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ db_trans('create_history') }}
                </a>
            @endcan
        </div>

        <div class="card cms-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table cms-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ db_trans('title') }}</th>
                                <th>{{ db_trans('slug') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('featured') }}</th>
                                <th>{{ db_trans('display_order') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ $history->title }}</td>
                                    <td>{{ $history->slug }}</td>
                                    <td><span class="badge {{ $history->is_published ? 'bg-success' : 'bg-secondary' }}">{{ $history->is_published ? db_trans('published') : db_trans('draft') }}</span></td>
                                    <td><span class="badge {{ $history->is_featured ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $history->is_featured ? db_trans('yes') : db_trans('no') }}</span></td>
                                    <td>{{ $history->display_order }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @can('cms.histories.update')
                                                <a href="{{ route('cms.histories.edit', $history) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('edit') }}</a>
                                            @endcan
                                            @can('cms.histories.delete')
                                                <form method="POST" action="{{ route('cms.histories.destroy', $history) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ db_trans('are_you_sure') }}')">
                                                        {{ db_trans('delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $histories->links() }}</div>
            </div>
        </div>
    </div>
@endsection