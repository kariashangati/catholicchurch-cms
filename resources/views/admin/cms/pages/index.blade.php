@extends('layouts.admin')

@section('title', db_trans('pages'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-page-head">
            <div>
                <h2 class="cms-page-title">{{ db_trans('pages') }}</h2>
                <p class="cms-page-subtitle">{{ db_trans('manage_static_pages_and_menu_footer_visibility') }}</p>
            </div>

            @can('cms.pages.create')
                <a href="{{ route('cms.pages.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ db_trans('create_page') }}
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
                                <th>{{ db_trans('show_in_menu') }}</th>
                                <th>{{ db_trans('show_in_footer') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pages as $page)
                                <tr>
                                    <td>{{ $page->title }}</td>
                                    <td>{{ $page->slug }}</td>
                                    <td><span class="badge {{ $page->is_published ? 'bg-success' : 'bg-secondary' }}">{{ $page->is_published ? db_trans('published') : db_trans('draft') }}</span></td>
                                    <td><span class="badge {{ $page->show_in_menu ? 'bg-info text-dark' : 'bg-light text-dark' }}">{{ $page->show_in_menu ? db_trans('yes') : db_trans('no') }}</span></td>
                                    <td><span class="badge {{ $page->show_in_footer ? 'bg-info text-dark' : 'bg-light text-dark' }}">{{ $page->show_in_footer ? db_trans('yes') : db_trans('no') }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @can('cms.pages.update')
                                                <a href="{{ route('cms.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('edit') }}</a>
                                            @endcan
                                            @can('cms.pages.delete')
                                                <form method="POST" action="{{ route('cms.pages.destroy', $page) }}">
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

                <div class="mt-3">{{ $pages->links() }}</div>
            </div>
        </div>
    </div>
@endsection