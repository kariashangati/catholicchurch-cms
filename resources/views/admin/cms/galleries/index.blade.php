@extends('layouts.admin')

@section('title', db_trans('galleries'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-page-head">
            <div>
                <h2 class="cms-page-title">{{ db_trans('galleries') }}</h2>
                <p class="cms-page-subtitle">{{ db_trans('manage_galleries_and_gallery_images') }}</p>
            </div>

            @can('cms.galleries.create')
                <a href="{{ route('cms.galleries.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ db_trans('create_gallery') }}
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
                                <th>{{ db_trans('event_date') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('featured') }}</th>
                                <th>{{ db_trans('images') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($galleries as $gallery)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $gallery->title }}</div>
                                        <div class="text-muted small">{{ $gallery->slug }}</div>
                                    </td>
                                    <td>
                                        {{ $gallery->event_date ? \Illuminate\Support\Carbon::parse($gallery->event_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $gallery->is_published ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $gallery->is_published ? db_trans('published') : db_trans('draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $gallery->is_featured ? 'bg-warning text-dark' : 'bg-light text-dark' }}">
                                            {{ $gallery->is_featured ? db_trans('yes') : db_trans('no') }}
                                        </span>
                                    </td>
                                    <td>{{ $gallery->images_count ?? 0 }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @can('cms.galleries.update')
                                                <a href="{{ route('cms.galleries.edit', $gallery) }}" class="btn btn-sm btn-outline-primary">
                                                    {{ db_trans('edit') }}
                                                </a>
                                            @endcan

                                            @can('cms.galleries.delete')
                                                <form method="POST" action="{{ route('cms.galleries.destroy', $gallery) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('{{ db_trans('are_you_sure') }}')"
                                                    >
                                                        {{ db_trans('delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        {{ db_trans('no_records_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $galleries->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection