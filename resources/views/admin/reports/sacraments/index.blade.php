@extends('layouts.admin')

@section('title', db_trans('sacrament_reports'))
@section('disable_default_alerts')@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/reports-v4-polish.css') }}">
@endpush

@section('content')
@php
    $kandas = collect($filterData['kandas'] ?? []);
    $jumuiyas = collect($filterData['jumuiyas'] ?? []);
    $familias = collect($filterData['familias'] ?? []);
    $memberFilters = $filterData['memberFilters'] ?? [];

    $defaultKandaId = $kandas->count() === 1 ? $kandas->first()->id : '';
    $defaultJumuiyaId = $jumuiyas->count() === 1 ? $jumuiyas->first()->id : '';
@endphp

<div class="admin-ui-v4 sacrament-reports-v4">
    <div class="ui-page-hero">
        <div class="ui-hero-pattern"></div>
        <div class="row g-4 align-items-center position-relative">
            <div class="col-xl-8">
                <span class="ui-page-badge">
                    <i class="fas fa-book-bible"></i>
                    {{ db_trans('sacrament_reports') }}
                </span>

                <h1 class="ui-page-title mt-3 mb-0">{{ $pageTitle }}</h1>
            </div>
        </div>
    </div>

    <div class="card ui-panel border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('reports.sacraments.generate') }}" class="row g-3" id="sacramentReportForm">
                @csrf

                <div class="col-xl-3 col-md-6">
                    <label class="form-label">{{ db_trans('kanda') }}</label>
                    <select class="form-select" name="kanda_id" id="sacramentReportKanda">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($kandas as $kanda)
                            <option value="{{ $kanda->id }}" @selected((string) old('kanda_id', $defaultKandaId) === (string) $kanda->id)>
                                {{ $kanda->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-md-6">
                    <label class="form-label">{{ db_trans('jumuiya') }}</label>
                    <select class="form-select" name="jumuiya_id" id="sacramentReportJumuiya">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($jumuiyas as $jumuiya)
                            <option
                                value="{{ $jumuiya->id }}"
                                data-kanda="{{ $jumuiya->kanda_id }}"
                                @selected((string) old('jumuiya_id', $defaultJumuiyaId) === (string) $jumuiya->id)
                            >
                                {{ $jumuiya->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-md-6">
                    <label class="form-label">{{ db_trans('familia') }}</label>
                    <select class="form-select" name="familia_id" id="sacramentReportFamilia">
                        <option value="">{{ db_trans('all') }}</option>
                        @foreach($familias as $familia)
                            <option
                                value="{{ $familia->id }}"
                                data-jumuiya="{{ $familia->jumuiya_id }}"
                                @selected((string) old('familia_id') === (string) $familia->id)
                            >
                                {{ $familia->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-md-6">
                    <label class="form-label">{{ db_trans('member_filter') }}</label>
                    <select class="form-select" name="member_filter">
                        @foreach($memberFilters as $filter)
                            <option value="{{ $filter }}" @selected(old('member_filter', 'all') === $filter)>
                                {{ db_trans($filter) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-xl-3 col-md-6">
                    <label class="form-label">{{ db_trans('view_mode') }}</label>
                    <select class="form-select" name="view_mode">
                        <option value="summary" @selected(old('view_mode', 'summary') === 'summary')>{{ db_trans('summary') }}</option>
                        <option value="detailed" @selected(old('view_mode') === 'detailed')>{{ db_trans('detailed') }}</option>
                    </select>
                </div>

                <div class="col-xl-3 col-md-6 d-flex align-items-end">
                    <button class="btn ui-btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>
                        {{ db_trans('generate_report') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kandaSelect = document.getElementById('sacramentReportKanda');
    const jumuiyaSelect = document.getElementById('sacramentReportJumuiya');
    const familiaSelect = document.getElementById('sacramentReportFamilia');

    const syncJumuiyas = function () {
        if (!kandaSelect || !jumuiyaSelect) return;

        const kanda = kandaSelect.value;

        Array.from(jumuiyaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !!kanda && option.dataset.kanda !== kanda;
        });

        const selected = jumuiyaSelect.selectedOptions[0];

        if (selected && selected.hidden) {
            jumuiyaSelect.value = '';
        }
    };

    const syncFamilias = function () {
        if (!jumuiyaSelect || !familiaSelect) return;

        const jumuiya = jumuiyaSelect.value;

        Array.from(familiaSelect.options).forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !!jumuiya && option.dataset.jumuiya !== jumuiya;
        });

        const selected = familiaSelect.selectedOptions[0];

        if (selected && selected.hidden) {
            familiaSelect.value = '';
        }
    };

    syncJumuiyas();
    syncFamilias();

    if (kandaSelect) {
        kandaSelect.addEventListener('change', function () {
            syncJumuiyas();
            if (jumuiyaSelect) jumuiyaSelect.value = '';
            syncFamilias();
            if (familiaSelect) familiaSelect.value = '';
        });
    }

    if (jumuiyaSelect) {
        jumuiyaSelect.addEventListener('change', function () {
            syncFamilias();
            if (familiaSelect) familiaSelect.value = '';
        });
    }
});
</script>
@endpush
