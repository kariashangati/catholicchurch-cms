<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReceiptAnalyticsService
{
    public function resolveDashboardDateRange(Request $request): array
    {
        $start = $request->date('start_date')?->startOfDay() ?? now()->startOfMonth();
        $end = $request->date('end_date')?->endOfDay() ?? now()->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return ['start' => $start, 'end' => $end];
    }

    public function issuedTrend(?User $user, array $dateRange): array
    {
        $rows = $this->baseQuery($user, $dateRange)
            ->selectRaw('DATE(issued_at) as label, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(issued_at)'))
            ->orderBy('label')
            ->get();

        return $this->normalizeTrend($rows, 'label', 'total');
    }

    public function statusBreakdown(?User $user, array $dateRange): array
    {
        return $this->baseQuery($user, $dateRange)
            ->selectRaw("COALESCE(status, 'issued') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])->all();
    }

    public function sourceBreakdown(?User $user, array $dateRange): array
    {
        return $this->baseQuery($user, $dateRange)
            ->selectRaw("COALESCE(source_type, receipt_type, 'general') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'total' => (int) $row->total,
            ])->all();
    }

    public function deliveryFunnel(?User $user, array $dateRange): array
    {
        $query = $this->baseQuery($user, $dateRange);

        return [
            ['label' => 'issued', 'total' => (clone $query)->count()],
            ['label' => 'sms_sent', 'total' => (clone $query)->whereNotNull('sms_sent_at')->count()],
            ['label' => 'opened', 'total' => (clone $query)->whereNotNull('first_opened_at')->count()],
            ['label' => 'downloaded', 'total' => (clone $query)->where('download_count', '>', 0)->count()],
        ];
    }

    public function pendingTrend(?User $user, array $dateRange): array
    {
        $rows = $this->baseQuery($user, $dateRange)
            ->whereIn('status', ['pending', 'pending_issue'])
            ->selectRaw('DATE(COALESCE(issued_at, created_at)) as label, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(COALESCE(issued_at, created_at))'))
            ->orderBy('label')
            ->get();

        return $this->normalizeTrend($rows, 'label', 'total');
    }

    public function reprintExceptionTrend(?User $user, array $dateRange): array
    {
        $base = $this->baseQuery($user, $dateRange);

        $reprints = (clone $base)
            ->selectRaw('DATE(COALESCE(last_reprinted_at, updated_at)) as label, COUNT(*) as total')
            ->whereNotNull('last_reprinted_at')
            ->groupBy(DB::raw('DATE(COALESCE(last_reprinted_at, updated_at))'))
            ->orderBy('label')
            ->get();

        $exceptions = (clone $base)
            ->selectRaw('DATE(updated_at) as label, COUNT(*) as total')
            ->whereIn('status', ['exception', 'failed'])
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('label')
            ->get();

        return [
            'reprints' => $this->normalizeTrend($reprints, 'label', 'total'),
            'exceptions' => $this->normalizeTrend($exceptions, 'label', 'total'),
        ];
    }

    protected function baseQuery(?User $user, array $dateRange)
    {
        return ReceiptIssue::query()
            ->when($dateRange['start'] ?? null, fn ($query, $start) => $query->whereDate(DB::raw('COALESCE(issued_at, created_at)'), '>=', Carbon::parse($start)->toDateString()))
            ->when($dateRange['end'] ?? null, fn ($query, $end) => $query->whereDate(DB::raw('COALESCE(issued_at, created_at)'), '<=', Carbon::parse($end)->toDateString()));
    }

    protected function normalizeTrend(Collection $rows, string $labelKey, string $valueKey): array
    {
        return $rows->map(fn ($row) => [
            'label' => (string) $row->{$labelKey},
            'total' => (int) $row->{$valueKey},
        ])->all();
    }
}
