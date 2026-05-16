@extends('layouts.admin')

@section('title', db_trans('finance_dashboard'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/dashboard-v3.css') }}">
    <style>
        .finance-dashboard .filter-panel-card { background:#fff; border:1px solid rgba(226,232,240,.9); border-radius:24px; box-shadow:var(--db-shadow-soft); }
        .finance-dashboard .form-label { font-size:.78rem; font-weight:800; color:var(--db-muted); letter-spacing:.04em; text-transform:uppercase; }
        .finance-dashboard .form-control,.finance-dashboard .form-select { min-height:48px; border-radius:14px; border-color:var(--db-border); box-shadow:none; }
        .finance-dashboard .btn-filter-primary { min-height:48px; border:0; border-radius:14px; font-weight:800; color:#fff; background:linear-gradient(135deg,#7c3aed,#9333ea); }
        .finance-dashboard .finance-card-link { color:inherit; text-decoration:none; display:block; height:100%; }
        .finance-dashboard .finance-card-link:hover .finance-card { transform:translateY(-3px); box-shadow:0 24px 50px rgba(15,23,42,.12); }
        .finance-dashboard .finance-card { transition:.2s ease; }
        .finance-dashboard .chart-link { color:inherit; text-decoration:none; display:block; }
        .finance-dashboard .chart-shell canvas,.finance-dashboard .chart-shell-lg canvas { width:100%!important; height:100%!important; }
        .finance-dashboard .table-tools { display:flex; gap:.75rem; flex-wrap:wrap; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid #e2e8f0; }
        .finance-dashboard .table-tools .form-control,.finance-dashboard .table-tools .form-select { min-height:40px; border-radius:12px; max-width:260px; }
        .finance-dashboard .activity-type-badge,.finance-dashboard .status-pill { display:inline-flex; align-items:center; justify-content:center; min-height:30px; padding:.35rem .7rem; border-radius:999px; font-size:.75rem; font-weight:800; white-space:nowrap; }
        .finance-dashboard .activity-type-badge { color:#fff; }
        .finance-dashboard .status-pill { background:#f8fafc; border:1px solid #e2e8f0; color:#475569; text-transform:capitalize; }
        .finance-dashboard .status-approved,.finance-dashboard .status-completed,.finance-dashboard .status-active,.finance-dashboard .status-verified { background:rgba(22,163,74,.10); border-color:rgba(22,163,74,.18); color:#15803d; }
        .finance-dashboard .status-pending { background:rgba(245,158,11,.12); border-color:rgba(245,158,11,.22); color:#b45309; }
        .finance-dashboard .status-rejected,.finance-dashboard .status-cancelled { background:rgba(239,68,68,.10); border-color:rgba(239,68,68,.18); color:#dc2626; }
        .finance-dashboard .pager-btn { border:1px solid #e2e8f0; background:#fff; border-radius:10px; padding:.45rem .75rem; font-weight:700; }
        .finance-dashboard .pager-btn:disabled { opacity:.45; }
        .finance-dashboard .details-list { display:grid; gap:.75rem; }
        .finance-dashboard .details-list div { display:flex; justify-content:space-between; gap:1rem; border-bottom:1px solid #eef2f7; padding-bottom:.6rem; }
        .finance-dashboard .details-list span { color:#64748b; font-weight:700; }
        .finance-dashboard .details-list strong { text-align:right; }
    </style>
@endpush

@section('content')
    @php
        $year = $filters['year'] ?? now()->year;
        $monthValue = $filters['month'] ?? '';
        $sourceToneMap = ['purple'=>'primary','blue'=>'info','green'=>'success','amber'=>'warning','teal'=>'secondary','rose'=>'danger','red'=>'danger','slate'=>'dark','gray'=>'secondary'];
        $activityToneMap = $sourceToneMap + ['warning'=>'warning','danger'=>'danger','success'=>'success','info'=>'info','primary'=>'primary','secondary'=>'secondary','dark'=>'dark'];
    @endphp

    <div class="dashboard-v3 finance-dashboard">
        <div class="dashboard-hero mb-4">
            <div class="hero-pattern"></div>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-xl-8">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="dashboard-hero-badge">{{ db_trans('finance_command_center') }}</span>
                    </div>
                    <h1 class="dashboard-title mb-4">{{ db_trans('finance_dashboard') }}</h1>
                    <div class="hero-meta-wrap">
                        <span class="hero-pill"><i class="fas fa-calendar-alt me-2"></i>{{ db_trans('year') }}: <strong class="ms-1">{{ $hero['current_year'] }}</strong></span>
                        <span class="hero-pill hero-pill-warning"><i class="fas fa-bell me-2"></i>{{ db_trans('pending_finance_actions') }}: <strong class="ms-1">{{ number_format($hero['pending_items']) }}</strong></span>
                        <span class="hero-pill"><i class="fas fa-compass me-2"></i>{{ db_trans('selected_scope') }}: <strong class="ms-1">{{ $hero['selected_scope'] }}</strong></span>
                    </div>
                </div>
                <div class="col-xl-4">
                    <form method="GET" class="filter-panel-card p-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-6"><label class="form-label">{{ db_trans('year') }}</label><input type="number" min="2020" max="2100" class="form-control" name="year" value="{{ $year }}"></div>
                            <div class="col-6"><label class="form-label">{{ db_trans('month') }}</label><select class="form-select" name="month"><option value="">{{ db_trans('all_months') }}</option>@for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected((string)$monthValue===(string)$m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>@endfor</select></div>
                            <div class="col-6"><label class="form-label">{{ db_trans('kanda') }}</label><select class="form-select" name="kanda_id"><option value="">{{ db_trans('all_kandas') }}</option>@foreach($kandas as $kanda)<option value="{{ $kanda->id }}" @selected(($filters['kanda_id'] ?? '') == $kanda->id)>{{ $kanda->name }}</option>@endforeach</select></div>
                            <div class="col-6"><label class="form-label">{{ db_trans('jumuiya') }}</label><select class="form-select" name="jumuiya_id"><option value="">{{ db_trans('all_jumuiyas') }}</option>@foreach($jumuiyas as $jumuiya)<option value="{{ $jumuiya->id }}" @selected(($filters['jumuiya_id'] ?? '') == $jumuiya->id)>{{ $jumuiya->name }}</option>@endforeach</select></div>
                            <div class="col-12"><button class="btn btn-filter-primary w-100" type="submit"><i class="fas fa-filter me-2"></i>{{ db_trans('apply_filters') }}</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach($sourceCards as $source)
                @php $tone = $sourceToneMap[$source['tone']] ?? 'secondary'; @endphp
                <div class="col-md-6 col-xl">
                    <a href="{{ $source['url'] ?? '#' }}" class="finance-card-link">
                        <div class="card finance-card finance-card-{{ $tone }} h-100 border-0">
                            <div class="card-body">
                                <div class="finance-card-top"><div class="finance-card-icon"><i class="{{ $source['icon'] }}"></i></div><span class="finance-chip">{{ db_trans('overview') }}</span></div>
                                <div class="finance-card-title">{{ $source['label'] }}</div>
                                <div class="finance-card-value">{{ number_format($source['year'], 2) }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <a href="{{ Route::has('finance.budgets.dashboard') ? route('finance.budgets.dashboard') : url('/finance/budgets') }}" class="chart-link">
                    <div class="card analytics-panel h-100 border-0">
                        <div class="card-body p-4">
                            <div class="panel-head"><h5 class="panel-title">{{ db_trans('budget_vs_actual') }}</h5><div class="panel-icon"><i class="fas fa-chart-column"></i></div></div>
                            <div class="chart-shell"><canvas id="budgetVsActualChart"></canvas></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-6">
                <div class="card analytics-panel h-100 border-0">
                    <div class="card-body p-4">
                        <div class="panel-head"><h5 class="panel-title">{{ db_trans('kanda_finance_trend') }}</h5><div class="panel-icon"><i class="fas fa-chart-bar"></i></div></div>
                        <div class="chart-shell"><canvas id="kandaFinanceChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card table-panel border-0">
                    <div class="card-header table-panel-header border-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="panel-title mb-0">{{ db_trans('unified_finance_activity') }}</h5>

                        <div class="d-flex flex-wrap gap-2">
                            @if(Route::has('pdf.finance.activity.export'))
                                <a
                                    href="{{ route('pdf.finance.activity.export', request()->query()) }}"
                                    target="_blank"
                                    class="btn btn-sm btn-danger"
                                >
                                    <i class="fas fa-file-pdf me-1"></i>{{ db_trans('export_pdf') }}
                                </a>
                            @endif

                            @if(Route::has('finance.activity.export.excel'))
                                <a
                                    href="{{ route('finance.activity.export.excel', request()->query()) }}"
                                    class="btn btn-sm btn-success"
                                >
                                    <i class="fas fa-file-excel me-1"></i>{{ db_trans('export_excel') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="table-tools">
                        <div class="d-flex align-items-center gap-2"><span class="fw-bold text-muted">{{ db_trans('show') }}</span><select id="financeActivityLength" class="form-select"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select><span class="fw-bold text-muted">{{ db_trans('rows') ?: db_trans('records') }}</span></div>
                        <input id="financeActivitySearch" class="form-control" placeholder="{{ db_trans('search') }}">
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive dashboard-table-wrap">
                            <table id="financeActivityTable" class="table align-middle table-hover mb-0 dashboard-table">
                                <thead>
                                    <tr><th>{{ db_trans('type') }}</th><th>{{ db_trans('description') }}</th><th>{{ db_trans('location') }}</th><th>{{ db_trans('date') }}</th><th>{{ db_trans('status') }}</th><th class="text-end">{{ db_trans('amount') }}</th><th class="text-end">{{ db_trans('actions') }}</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivities as $item)
                                        @php $tone = $activityToneMap[$item['tone']] ?? 'secondary'; @endphp
                                        <tr data-search="{{ strtolower($item['type'].' '.$item['title'].' '.$item['meta'].' '.$item['status']) }}">
                                            <td><span class="activity-type-badge tone-{{ $tone }}">{{ $item['type'] }}</span></td>
                                            <td>{{ $item['title'] }}</td>
                                            <td>{{ $item['meta'] }}</td>
                                            <td>{{ optional($item['date'])->format('M d, Y') ?? '—' }}</td>
                                            <td><span class="status-pill status-{{ strtolower((string)$item['status']) }}">{{ db_trans((string)$item['status']) }}</span></td>
                                            <td class="text-end">{{ number_format($item['amount'], 2) }}</td>
                                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary activity-open" data-bs-toggle="modal" data-bs-target="#financeActivityModal" data-type="{{ e($item['type']) }}" data-title="{{ e($item['title']) }}" data-meta="{{ e($item['meta']) }}" data-date="{{ optional($item['date'])->format('M d, Y') ?? '—' }}" data-status="{{ e(db_trans((string)$item['status'])) }}" data-amount="{{ number_format($item['amount'], 2) }}">{{ db_trans('open') }}</button></td>
                                        </tr>
                                    @empty
                                        <tr class="empty-row"><td colspan="7" class="text-center text-muted py-4">{{ db_trans('no_records_found') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
                        <span id="financeActivityInfo" class="text-muted fw-bold"></span>
                        <div class="d-flex gap-2"><button id="financeActivityPrev" class="pager-btn" type="button">{{ db_trans('previous') }}</button><button id="financeActivityNext" class="pager-btn" type="button">{{ db_trans('next') }}</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="financeActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header"><h5 class="modal-title">{{ db_trans('activity_details') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ db_trans('close') }}"></button></div>
                <div class="modal-body"><div class="details-list" id="financeActivityDetails"></div></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#7c3aed','#3b82f6','#14b8a6','#22c55e','#f59e0b','#f43f5e','#64748b','#0ea5e9'];
    const budgetCtx = document.getElementById('budgetVsActualChart');
    if (budgetCtx) new Chart(budgetCtx,{type:'bar',data:{labels:@json($budgetVsActualChart['labels']),datasets:[{label:@json(db_trans('budget')),data:@json($budgetVsActualChart['budget']),backgroundColor:palette[6],borderRadius:10},{label:@json(db_trans('actual')),data:@json($budgetVsActualChart['actual']),backgroundColor:palette[1],borderRadius:10}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{callback:v=>Number(v).toLocaleString()}}}}});
    const kandaCtx = document.getElementById('kandaFinanceChart');
    if (kandaCtx) new Chart(kandaCtx,{type:'bar',data:{labels:@json($kandaFinanceChart['labels'] ?? []),datasets:(@json($kandaFinanceChart['datasets'] ?? [])).map((d,i)=>({label:d.label,data:d.data,backgroundColor:palette[i%palette.length],borderRadius:10}))},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{x:{stacked:false},y:{beginAtZero:true,ticks:{callback:v=>Number(v).toLocaleString()}}}}});

    const rows = Array.from(document.querySelectorAll('#financeActivityTable tbody tr')).filter(r => !r.classList.contains('empty-row'));
    const search = document.getElementById('financeActivitySearch');
    const length = document.getElementById('financeActivityLength');
    const prev = document.getElementById('financeActivityPrev');
    const next = document.getElementById('financeActivityNext');
    const info = document.getElementById('financeActivityInfo');
    let page = 1;
    function filteredRows(){ const q=(search?.value||'').toLowerCase().trim(); return rows.filter(r => !q || (r.dataset.search||'').includes(q)); }
    function render(){ const per=parseInt(length?.value||10,10); const data=filteredRows(); const pages=Math.max(1,Math.ceil(data.length/per)); page=Math.min(page,pages); rows.forEach(r=>r.style.display='none'); data.slice((page-1)*per,page*per).forEach(r=>r.style.display=''); if(info) info.textContent=data.length ? `${(page-1)*per+1}-${Math.min(page*per,data.length)} / ${data.length}` : '0 / 0'; if(prev) prev.disabled=page<=1; if(next) next.disabled=page>=pages; }
    search?.addEventListener('input',()=>{page=1;render();}); length?.addEventListener('change',()=>{page=1;render();}); prev?.addEventListener('click',()=>{page--;render();}); next?.addEventListener('click',()=>{page++;render();}); render();

    document.querySelectorAll('.activity-open').forEach(btn => btn.addEventListener('click', function(){
        const labels = {type:@json(db_trans('type')), title:@json(db_trans('description')), meta:@json(db_trans('location')), date:@json(db_trans('date')), status:@json(db_trans('status')), amount:@json(db_trans('amount'))};
        const values = {type:this.dataset.type,title:this.dataset.title,meta:this.dataset.meta,date:this.dataset.date,status:this.dataset.status,amount:this.dataset.amount};
        document.getElementById('financeActivityDetails').innerHTML = Object.keys(labels).map(k => `<div><span>${labels[k]}</span><strong>${values[k] || '—'}</strong></div>`).join('');
    }));
});
</script>
@endpush