<div class="receipt-card card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ db_trans('receipts.dashboard.analytics') }}</span>
        <span class="text-muted small">{{ db_trans('receipts.dashboard.chart_hint') }}</span>
    </div>

    <div class="card-body">
        <div class="row g-4">
            <div class="col-12">
                <div class="receipt-chart-card">
                    <div class="receipt-chart-title">{{ db_trans('receipts.charts.issued_trend') }}</div>
                    <canvas id="receiptsIssuedTrendChart"
                            data-chart='@json($analytics["issuedTrend"] ?? [])'></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="receipt-chart-card">
                    <div class="receipt-chart-title">{{ db_trans('receipts.charts.status_breakdown') }}</div>
                    <canvas id="receiptsStatusBreakdownChart"
                            data-chart='@json($analytics["statusBreakdown"] ?? [])'></canvas>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="receipt-chart-card">
                    <div class="receipt-chart-title">{{ db_trans('receipts.charts.source_breakdown') }}</div>
                    <canvas id="receiptsSourceBreakdownChart"
                            data-chart='@json($analytics["sourceBreakdown"] ?? [])'></canvas>
                </div>
            </div>

            <div class="col-12">
                <div class="receipt-chart-card">
                    <div class="receipt-chart-title">{{ db_trans('receipts.charts.pending_trend') }}</div>
                    <canvas id="receiptsPendingTrendChart"
                            data-chart='@json($analytics["pendingTrend"] ?? [])'></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
