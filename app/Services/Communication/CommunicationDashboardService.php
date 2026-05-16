<?php

namespace App\Services\Communication;

use App\Models\CommunicationBalanceSnapshot;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommunicationDashboardService
{
    public function __construct(protected CommunicationSmsSettingsService $settingsService)
    {
    }

    public function getOverview(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();
        $unitPrice = $this->settingsService->smsUnitPrice();

        $latestBalance = CommunicationBalanceSnapshot::query()->latest('fetched_at')->first();

        $segmentsToday = (int) CommunicationMessage::query()->whereDate('created_at', $today)->sum('segment_count');
        $segmentsWeek = (int) CommunicationMessage::query()->where('created_at', '>=', $weekStart)->sum('segment_count');
        $segmentsMonth = (int) CommunicationMessage::query()->where('created_at', '>=', $monthStart)->sum('segment_count');
        $segmentsYear = (int) CommunicationMessage::query()->where('created_at', '>=', $yearStart)->sum('segment_count');
        $allSegments = (int) CommunicationMessage::query()->sum('segment_count');

        $completionStats = CommunicationCampaign::query()
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "partially_failed" THEN 1 ELSE 0 END) as partial_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_campaigns')
            ->first();

        $recentCampaigns = CommunicationCampaign::query()
            ->where('channel', 'sms')
            ->latest()
            ->take(5)
            ->get();

        $statusBreakdown = CommunicationMessage::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'latest_balance' => $latestBalance,
            'sms_settings' => $this->settingsService->current(),
            'kpis' => [
                'total_campaigns' => (int) ($completionStats->total_campaigns ?? 0),
                'scheduled_count' => (int) ($completionStats->scheduled_campaigns ?? 0),
                'processing_count' => (int) ($completionStats->processing_campaigns ?? 0),
                'completed_campaigns' => (int) ($completionStats->completed_campaigns ?? 0),
                'messages_today' => $segmentsToday,
                'messages_this_week' => $segmentsWeek,
                'messages_this_month' => $segmentsMonth,
                'messages_this_year' => $segmentsYear,
                'total_messages' => $allSegments,
                'estimated_cost_month' => $segmentsMonth * $unitPrice,
                'estimated_cost_year' => $segmentsYear * $unitPrice,
                'balance_units' => (float) ($latestBalance?->balance_units ?? 0),
            ],
            'completion' => [
                'total_campaigns' => (int) ($completionStats->total_campaigns ?? 0),
                'completed_campaigns' => (int) ($completionStats->completed_campaigns ?? 0),
                'partial_campaigns' => (int) ($completionStats->partial_campaigns ?? 0),
                'failed_campaigns' => (int) ($completionStats->failed_campaigns ?? 0),
            ],
            'recent_campaigns' => $recentCampaigns,
            'status_breakdown' => $statusBreakdown,
            'usage_chart' => $this->usageTrendChart(),
        ];
    }

    public function usageTrendChart(): array
    {
        $days = collect(range(13, 0))->map(function (int $offset) {
            $date = today()->subDays($offset);
            return ['date' => $date->toDateString(), 'label' => $date->format('d M')];
        });

        $counts = CommunicationMessage::query()
            ->selectRaw('DATE(created_at) as sms_date, COALESCE(SUM(segment_count), 0) as total')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'sms_date');

        return [
            'labels' => $days->pluck('label')->values(),
            'values' => $days->map(fn ($day) => (int) ($counts[$day['date']] ?? 0))->values(),
        ];
    }

    public function getBalanceSummary(): array
    {
        $latestBalance = CommunicationBalanceSnapshot::query()->latest('fetched_at')->first();
        $snapshots = CommunicationBalanceSnapshot::query()->latest('fetched_at')->paginate(20);
        $transactions = DB::table('communication_balance_transactions')->latest('created_at')->paginate(20, ['*'], 'transactions_page');

        return [
            'latest_balance' => $latestBalance,
            'snapshots' => $snapshots,
            'transactions' => $transactions,
            'sms_settings' => $this->settingsService->current(),
        ];
    }
}
