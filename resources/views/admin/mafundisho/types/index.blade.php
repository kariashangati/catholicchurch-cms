@extends('layouts.admin')

@section('title', db_trans('mafundisho_teaching_types'))

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ db_trans('mafundisho_teaching_types') }}</h1>
            <p class="text-muted mb-0">{{ db_trans('mafundisho_teaching_types_subtitle') }}</p>
        </div>

        @can('mafundisho-types.create')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeachingTypeModal">
                <i class="fas fa-plus me-1"></i> {{ db_trans('mafundisho_add_teaching_type') }}
            </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>{{ db_trans('fix_errors') }}</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ db_trans('search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ db_trans('mafundisho_search_teaching_types') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ db_trans('status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ db_trans('all') }}</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ db_trans('active') }}</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ db_trans('inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-outline-primary flex-fill" type="submit">{{ db_trans('filter') }}</button>
                    <a href="{{ route('mafundisho.types.index') }}" class="btn btn-outline-secondary">{{ db_trans('reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ db_trans('name') }}</th>
                        <th>{{ db_trans('slug') }}</th>
                        <th>{{ db_trans('mafundisho_sacrament_link') }}</th>
                        <th>{{ db_trans('mafundisho_eligibility_rule') }}</th>
                        <th>{{ db_trans('students') }}</th>
                        <th>{{ db_trans('status') }}</th>
                        <th class="text-end">{{ db_trans('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachingTypes as $teachingType)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $teachingType->name }}</div>
                                @if($teachingType->is_system)
                                    <span class="badge bg-info-subtle text-info">{{ db_trans('system') }}</span>
                                @endif
                            </td>
                            <td><code>{{ $teachingType->slug }}</code></td>
                            <td>{{ $sacramentOptions[$teachingType->sacrament_key] ?? db_trans('none') }}</td>
                            <td>{{ $eligibilityOptions[$teachingType->eligibility_rule] ?? db_trans('mafundisho_eligibility_all') }}</td>
                            <td>{{ number_format($teachingType->enrollments_count) }}</td>
                            <td>
                                <span class="badge {{ $teachingType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $teachingType->is_active ? db_trans('active') : db_trans('inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('mafundisho-types.update')
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTeachingType{{ $teachingType->id }}">
                                        {{ db_trans('edit') }}
                                    </button>
                                @endcan
                                @can('mafundisho-types.delete')
                                    <form method="POST" action="{{ route('mafundisho.types.destroy', $teachingType) }}" class="d-inline" onsubmit="return confirm('{{ db_trans('mafundisho_confirm_delete_teaching_type') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" @disabled($teachingType->is_system)>{{ db_trans('delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>

                        @can('mafundisho-types.update')
                            <div class="modal fade" id="editTeachingType{{ $teachingType->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('mafundisho.types.update', $teachingType) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ db_trans('mafundisho_edit_teaching_type') }}: {{ $teachingType->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                @include('admin.mafundisho.types.partials.form-fields', ['teachingType' => $teachingType])
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ db_trans('save_changes') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endcan
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">{{ db_trans('mafundisho_no_teaching_types_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($teachingTypes, 'links'))
            <div class="card-footer bg-white">
                {{ $teachingTypes->links() }}
            </div>
        @endif
    </div>
</div>

@can('mafundisho-types.create')
    <div class="modal fade" id="createTeachingTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('mafundisho.types.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ db_trans('mafundisho_add_teaching_type') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('admin.mafundisho.types.partials.form-fields', ['teachingType' => new \App\Models\TeachingType()])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ db_trans('cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ db_trans('save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
