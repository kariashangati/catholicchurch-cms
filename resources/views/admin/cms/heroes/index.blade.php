@extends('layouts.admin')

@section('title', db_trans('hero_banners'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/cms.css') }}">
@endpush

@section('content')
    <div class="cms-shell">
        <div class="cms-page-head">
            <div>
                <h2 class="cms-page-title">{{ db_trans('hero_banners') }}</h2>
                <p class="cms-page-subtitle">{{ db_trans('manage_hero_banners_and_calls_to_action') }}</p>
            </div>

            @can('cms.heroes.create')
                <a href="{{ route('cms.heroes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>{{ db_trans('create_hero_banner') }}
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
                                <th>{{ db_trans('background_type') }}</th>
                                <th>{{ db_trans('status') }}</th>
                                <th>{{ db_trans('display_order') }}</th>
                                <th>{{ db_trans('created_at') }}</th>
                                <th class="text-end">{{ db_trans('actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($heroBanners as $heroBanner)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $heroBanner->title }}</div>
                                        <div class="text-muted small">{{ $heroBanner->subtitle }}</div>
                                    </td>
                                    <td>{{ ucfirst($heroBanner->background_type) }}</td>
                                    <td>
                                        <span class="badge {{ $heroBanner->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $heroBanner->is_active ? db_trans('active') : db_trans('inactive') }}
                                        </span>
                                    </td>
                                    <td>{{ $heroBanner->display_order }}</td>
                                    <td>{{ optional($heroBanner->created_at)->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            @can('cms.heroes.update')
                                                <a href="{{ route('cms.heroes.edit', $heroBanner) }}" class="btn btn-sm btn-outline-primary">{{ db_trans('edit') }}</a>
                                            @endcan
                                            @can('cms.heroes.delete')
                                                <form method="POST" action="{{ route('cms.heroes.destroy', $heroBanner) }}">
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
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $heroBanners->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection