@extends('layouts.admin')

@section('title', db_trans('announcements'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-page-head">
            <div>
                <h2 class="cms-page-title">{{ db_trans('announcements') }}</h2>
                <p class="cms-page-subtitle">{{ db_trans('manage_announcements_and_public_notices') }}</p>
            </div>

            @can('cms.announcements.create')
                <a href="{{ route('cms.announcements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ db_trans('create_announcement') }}
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
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('featured') }}</th>
                                <th>{{ db_trans('show_on_homepage') }}</th>
                                <th>{{ db_trans('display_order') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $announcement)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $announcement->title }}</div>
                                        <div class="text-muted small">{{ $announcement->slug }}</div>
                                    </td>
                                    <td><span class="badge {{ $announcement->is_published ? 'bg-success' : 'bg-secondary' }}">{{ $announcement->is_published ? db_trans('published') : db_trans('draft') }}</span></td>
                                    <td><span class="badge {{ $announcement->is_featured ? 'bg-warning text-dark' : 'bg-light text-dark' }}">{{ $announcement->is_featured ? db_trans('yes') : db_trans('no') }}</span></td>
                                    <td><span class="badge {{ $announcement->show_on_homepage ? 'bg-info text-dark' : 'bg-light text-dark' }}">{{ $announcement->show_on_homepage ? db_trans('yes') : db_trans('no') }}</span></td>
                                    <td>{{ $announcement->display_order }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @can('cms.announcements.update')
                                                <a href="{{ route('cms.announcements.edit', $announcement) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('edit') }}</a>
                                            @endcan
                                            @can('cms.announcements.delete')
                                                <form method="POST" action="{{ route('cms.announcements.destroy', $announcement) }}">
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

                <div class="mt-3">{{ $announcements->links() }}</div>
            </div>
        </div>
    </div>
@endsection