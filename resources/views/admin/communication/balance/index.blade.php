@extends('layouts.admin')

@section('title', db_trans('communication.balance'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/communication-center.css') }}">
@endpush

@section('content')
<div class="container-fluid communication-center-page">
    <div class="cc-subhero mb-4">
        <div>
            <span class="cc-kicker">{{ db_trans('communication.provider_wallet') }}</span>
            <h1 class="cc-subhero__title">{{ db_trans('communication.balance') }}</h1>
            <p class="cc-subhero__text">{{ db_trans('communication.balance_desc') }}</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="cc-kpi-card cc-kpi-card--emerald">
                <div class="cc-kpi-card__icon"><i class="bi bi-wallet2"></i></div>
                <div class="cc-kpi-card__body">
                    <span class="cc-kpi-card__label">{{ db_trans('communication.current_balance') }}</span>
                    <h3>{{ number_format((float) optional($latest_balance)->balance_units, 0) }}</h3>
                    <small>{{ optional($latest_balance)->fetched_at ? optional($latest_balance)->fetched_at->format('d M Y, H:i') : db_trans('communication.no_balance_snapshot') }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="cc-kpi-card cc-kpi-card--blue">
                <div class="cc-kpi-card__icon"><i class="bi bi-broadcast"></i></div>
                <div class="cc-kpi-card__body">
                    <span class="cc-kpi-card__label">{{ db_trans('communication.provider') }}</span>
                    <h3>{{ strtoupper(optional($latest_balance)->provider ?? 'beem') }}</h3>
                    <small>{{ db_trans('communication.active_provider') }}</small>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="cc-kpi-card cc-kpi-card--amber">
                <div class="cc-kpi-card__icon"><i class="bi bi-clock-history"></i></div>
                <div class="cc-kpi-card__body">
                    <span class="cc-kpi-card__label">{{ db_trans('communication.last_balance_check') }}</span>
                    <h3>{{ optional($latest_balance)->fetched_at ? optional($latest_balance)->fetched_at->diffForHumans() : '—' }}</h3>
                    <small>{{ db_trans('communication.balance_snapshot_time') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-6">
            <div class="cc-panel">
                <div class="cc-panel__header">
                    <div>
                        <h5>{{ db_trans('communication.balance_snapshots') }}</h5>
                        
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table cc-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('communication.provider') }}</th>
                                <th>{{ db_trans('communication.balance') }}</th>
                                <th>{{ db_trans('communication.fetched_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($snapshots as $snapshot)
                                <tr>
                                    <td>{{ strtoupper($snapshot->provider) }}</td>
                                    <td>{{ number_format((float) $snapshot->balance_units, 0) }}</td>
                                    <td>{{ optional($snapshot->fetched_at)->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">{{ db_trans('communication.no_balance_snapshot') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $snapshots->links() }}</div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="cc-panel">
                <div class="cc-panel__header">
                    <div>
                        <h5>{{ db_trans('communication.balance_transactions') }}</h5>
                        
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table cc-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ db_trans('communication.type') }}</th>
                                <th>{{ db_trans('communication.units') }}</th>
                                <th>{{ db_trans('communication.created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td><span class="cc-status cc-status--{{ str_replace('_', '-', $transaction->transaction_type) }}">{{ db_trans('communication.transaction_' . $transaction->transaction_type) }}</span></td>
                                    <td>{{ number_format((float) $transaction->amount_units, 0) }}</td>
                                    <td>{{ optional($transaction->created_at)->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">{{ db_trans('communication.no_balance_transactions') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
