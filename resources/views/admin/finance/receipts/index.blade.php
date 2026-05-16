<div class="card table-panel border-0">
    <div class="card-header table-panel-header border-0 p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h5 class="panel-title mb-1">{{ db_trans('kanda_and_jumuiya_breakdown') }}</h5>
            </div>

            <form action="{{ route('pdf.kanda-reports.breakdown') }}"
                  method="GET"
                  target="_blank"
                  class="d-flex flex-column flex-md-row align-items-md-end gap-2">
                <div>
                    <label for="breakdown_from_date" class="form-label mb-1 small">
                        {{ db_trans('from_date') }}
                    </label>
                    <input
                        type="date"
                        id="breakdown_from_date"
                        name="from_date"
                        class="form-control form-control-sm"
                        value="{{ request('from_date') }}">
                </div>

                <div>
                    <label for="breakdown_to_date" class="form-label mb-1 small">
                        {{ db_trans('to_date') }}
                    </label>
                    <input
                        type="date"
                        id="breakdown_to_date"
                        name="to_date"
                        class="form-control form-control-sm"
                        value="{{ request('to_date') }}">
                </div>

                <div>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                    </button>
                </div>
            </form>
        </div>
    </div>