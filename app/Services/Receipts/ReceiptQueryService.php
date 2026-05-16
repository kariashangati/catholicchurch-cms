<?php

namespace App\Services\Receipts;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\ReceiptExceptionLog;
use App\Models\ReceiptIssue;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Finance\ContributionAccessService;
use App\Support\Receipts\ReceiptStatuses;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ReceiptQueryService
{
    public function __construct(protected ?ContributionAccessService $access = null)
    {
    }

    /*
     |--------------------------------------------------------------------------
     | Compatibility methods used by the existing Admin\Finance\ReceiptController
     |--------------------------------------------------------------------------
     */
    public function dashboardSummary(?Authenticatable $user = null): array
    {
        $base = $this->scopedQuery($user);

        return [
            'issued_today' => (clone $base)->whereDate('issued_at', today())->count(),
            'issued_this_month' => (clone $base)
                ->whereBetween('issued_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'pending_count' => (clone $base)->where('status', ReceiptStatuses::PENDING_ISSUE)->count(),
            'downloaded_count' => (clone $base)->whereNotNull('first_downloaded_at')->count(),
            'printed_count' => (clone $base)->whereNotNull('printed_at')->count(),
        ];
    }

    public function recentReceipts(?Authenticatable $user = null, int $limit = 10): EloquentCollection
    {
        return $this->scopedQuery($user)
            ->latest('issued_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function pendingSummary(?Authenticatable $user = null): array
    {
        $base = $this->scopedQuery($user);

        return [
            'pending_issue' => (clone $base)->where('status', ReceiptStatuses::PENDING_ISSUE)->count(),
            'exceptions' => (clone $base)->where('status', ReceiptStatuses::EXCEPTION)->count(),
        ];
    }

    public function issueFormOptions(?Authenticatable $user = null): array
    {
        return [
            'source_types' => [
                'tithe' => db_trans('receipts.types.tithe'),
                'cash_contribution' => db_trans('receipts.types.cash_contribution'),
                'bank_contribution' => db_trans('receipts.types.bank_contribution'),
                'offering' => db_trans('receipts.types.offering'),
                'project_contribution' => db_trans('receipts.types.project_contribution'),
                'group_summary' => db_trans('receipts.types.group_summary'),
            ],
            'members' => Member::query()
                ->orderBy('first_name')
                ->orderBy('middle_name')
                ->orderBy('last_name')
                ->get()
                ->mapWithKeys(fn (Member $member) => [$member->id => $this->memberName($member)])
                ->all(),
            'familias' => Familia::query()->orderBy('name')->pluck('name', 'id')->all(),
            'jumuiyas' => Jumuiya::query()->orderBy('name')->pluck('name', 'id')->all(),
            'kandas' => Kanda::query()->orderBy('name')->pluck('name', 'id')->all(),
            'contribution_types' => ContributionType::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    public function history(array $filters, ?Authenticatable $user = null): LengthAwarePaginatorContract
    {
        return $this->applyHistoryFilters($this->scopedQuery($user), $filters)
            ->latest('issued_at')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function timeline(ReceiptIssue $receipt): array
    {
        return [
            'issued_at' => $receipt->issued_at,
            'printed_at' => $receipt->printed_at,
            'sms_sent_at' => $receipt->sms_sent_at,
            'first_opened_at' => $receipt->first_opened_at,
            'last_opened_at' => $receipt->last_opened_at,
            'first_downloaded_at' => $receipt->first_downloaded_at,
            'last_downloaded_at' => $receipt->last_downloaded_at,
        ];
    }

    public function pending(array $filters, ?Authenticatable $user = null): LengthAwarePaginatorContract
    {
        return $this->scopedQuery($user)
            ->where('status', ReceiptStatuses::PENDING_ISSUE)
            ->latest('created_at')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();
    }

    public function pendingCounts(array $filters, ?Authenticatable $user = null): array
    {
        $base = $this->scopedQuery($user)->where('status', ReceiptStatuses::PENDING_ISSUE);

        return [
            'all' => (clone $base)->count(),
            'tithes' => (clone $base)->whereIn('receipt_type', ['tithe', 'zaka'])->count(),
            'contributions' => (clone $base)->whereIn('receipt_type', ['cash_contribution', 'bank_contribution', 'project_contribution', 'mchango'])->count(),
        ];
    }

    public function exceptions(?Authenticatable $user = null, int $perPage = 20): LengthAwarePaginatorContract
    {
        return ReceiptExceptionLog::query()
            ->with(['receiptIssue'])
            ->latest('happened_at')
            ->latest('id')
            ->paginate($perPage);
    }

    /*
     |--------------------------------------------------------------------------
     | New bulk receipt centre methods
     |--------------------------------------------------------------------------
     */
    public function options(User $user): array
    {
        return [
            'kandas' => Kanda::query()->orderBy('name')->get(),
            'jumuiyas' => Jumuiya::query()->orderBy('name')->get(),
            'members' => Member::query()
                ->with('familia.jumuiya.kanda')
                ->orderBy('first_name')
                ->orderBy('middle_name')
                ->orderBy('last_name')
                ->get(),
            'types' => ContributionType::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    public function pendingPaginator(array $filters, User $user): LengthAwarePaginator
    {
        $rows = $this->sourceRows($filters, $user);
        $status = $filters['status'] ?? 'pending';

        if ($status === 'pending') {
            $rows = $rows->filter(fn (array $row) => empty($row['receipt']));
        } elseif ($status === 'printed') {
            $rows = $rows->filter(fn (array $row) => in_array(data_get($row, 'receipt.status'), [
                ReceiptStatuses::PRINTED,
                ReceiptStatuses::REPRINTED,
                ReceiptStatuses::DOWNLOADED,
                ReceiptStatuses::SMS_SENT,
                ReceiptStatuses::OPENED,
            ], true));
        } elseif ($status !== 'all') {
            $normalized = ReceiptStatuses::normalize($status);
            $rows = $rows->filter(fn (array $row) => data_get($row, 'receipt.status') === $normalized || data_get($row, 'receipt.status') === $status);
        }

        $page = max(1, (int) request('page', 1));
        $perPage = max(5, min(200, (int) ($filters['per_page'] ?? 25)));
        $items = $rows->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function sourceRows(array $filters, User $user): Collection
    {
        $type = $filters['receipt_type'] ?? 'zaka';

        $rows = $type === 'mchango'
            ? $this->contributionRows($filters, $user)
            : $this->titheRows($filters, $user);

        $sourceIds = $rows->pluck('source_id')->filter()->values();

        $receipts = ReceiptIssue::query()
            ->whereIn('source_type', ['zaka', 'tithe', 'mchango_taslimu', 'mchango_benki', 'cash_contribution', 'bank_contribution'])
            ->when($sourceIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('source_id', $sourceIds))
            ->when($sourceIds->isEmpty(), fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->get()
            ->keyBy(fn (ReceiptIssue $receipt) => $receipt->source_type . ':' . $receipt->source_id);

        $groupReceipts = $this->groupReceiptLookup($filters, $type);

        return $rows
            ->map(function (array $row) use ($receipts, $groupReceipts): array {
                $key = $row['source_type'] . ':' . $row['source_id'];
                $receipt = $receipts->get($key);

                if (! $receipt && $row['source_type'] === 'zaka') {
                    $receipt = $receipts->get('tithe:' . $row['source_id']);
                }

                if (! $receipt && $row['source_type'] === 'mchango_taslimu') {
                    $receipt = $receipts->get('cash_contribution:' . $row['source_id']);
                }

                if (! $receipt && $row['source_type'] === 'mchango_benki') {
                    $receipt = $receipts->get('bank_contribution:' . $row['source_id']);
                }

                if (! $receipt) {
                    $receipt = $this->matchingGroupReceipt($groupReceipts, $row);
                }

                $row['receipt'] = $receipt;
                $row['receipt_status'] = $receipt?->status ?? ReceiptStatuses::PENDING_ISSUE;
                $row['source_key'] = $key;

                return $row;
            })
            ->sortByDesc('contribution_date')
            ->values();
    }

    public function groupedRowsForIssue(array $filters, User $user): Collection
    {
        $layout = $filters['receipt_layout'] ?? 'mwanajumuiya';
        $sourceRows = $this->sourceRows($filters, $user)->filter(fn (array $row) => empty($row['receipt']));

        $selected = collect($filters['source_keys'] ?? [])->filter()->values();
        if ($selected->isNotEmpty()) {
            $sourceRows = $sourceRows->whereIn('source_key', $selected->all())->values();
        }

        if ($layout === 'kanda') {
            return $sourceRows
                ->filter(fn (array $row) => ! empty($row['kanda_id']))
                ->groupBy('kanda_id')
                ->map(fn (Collection $rows) => $this->makeGroupPayload($rows, 'kanda'))
                ->values();
        }

        if ($layout === 'jumuiya') {
            return $sourceRows
                ->filter(fn (array $row) => ! empty($row['jumuiya_id']))
                ->groupBy('jumuiya_id')
                ->map(fn (Collection $rows) => $this->makeGroupPayload($rows, 'jumuiya'))
                ->values();
        }

        return $sourceRows
            ->groupBy('member_id')
            ->map(fn (Collection $rows) => $this->makeGroupPayload($rows, 'mwanajumuiya'))
            ->values();
    }

    protected function makeGroupPayload(Collection $rows, string $layout): array
    {
        $first = $rows->first();

        return [
            'receipt_layout' => $layout,
            'receipt_type' => $first['receipt_type'],
            'source_type' => $first['receipt_type'] === 'zaka' ? 'zaka' : 'mchango',
            'member_id' => $layout === 'mwanajumuiya' ? $first['member_id'] : null,
            'familia_id' => $layout === 'mwanajumuiya' ? $first['familia_id'] : null,
            'jumuiya_id' => $layout === 'kanda' ? null : $first['jumuiya_id'],
            'kanda_id' => $first['kanda_id'],
            'contribution_type_id' => $first['contribution_type_id'],
            'amount' => $rows->sum('amount'),
            'contribution_date' => $rows->max('contribution_date'),
            'recipient_name' => match ($layout) {
                'kanda' => $first['kanda_name'] ?: db_trans('kanda'),
                'jumuiya' => $first['jumuiya_name'] ?: db_trans('jumuiya'),
                default => $first['member_name'],
            },
            'phone' => $layout === 'mwanajumuiya' ? $first['phone'] : null,
            'items' => $rows->values()->all(),
        ];
    }


    protected function groupReceiptLookup(array $filters, string $type): Collection
    {
        $query = ReceiptIssue::query()
            ->whereIn('receipt_layout', ['jumuiya', 'kanda'])
            ->where('receipt_type', $type)
            ->where('period_year', (int) ($filters['year'] ?? now()->year));

        if (! empty($filters['month'])) {
            $query->where('period_month', (int) $filters['month']);
        } else {
            $query->whereNull('period_month');
        }

        if ($type === 'mchango' && ! empty($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', (int) $filters['contribution_type_id']);
        }

        return $query->latest('id')->get();
    }

    protected function matchingGroupReceipt(Collection $groupReceipts, array $row): ?ReceiptIssue
    {
        return $groupReceipts->first(function (ReceiptIssue $receipt) use ($row): bool {
            if ((string) $receipt->receipt_type !== (string) $row['receipt_type']) {
                return false;
            }

            if ($row['receipt_type'] === 'mchango' && (int) ($receipt->contribution_type_id ?? 0) !== (int) ($row['contribution_type_id'] ?? 0)) {
                return false;
            }

            if ($receipt->receipt_layout === 'kanda') {
                return ! empty($row['kanda_id']) && (int) $receipt->kanda_id === (int) $row['kanda_id'];
            }

            if ($receipt->receipt_layout === 'jumuiya') {
                return ! empty($row['jumuiya_id']) && (int) $receipt->jumuiya_id === (int) $row['jumuiya_id'];
            }

            return false;
        });
    }

    protected function titheRows(array $filters, User $user): Collection
    {
        $query = Tithe::query()->with(['member.familia.jumuiya.kanda']);

        if (method_exists(Tithe::class, 'jumuiya') || method_exists(new Tithe(), 'jumuiya')) {
            $query->with(['jumuiya.kanda']);
        }

        $this->applyPeriod($query, $filters, new Tithe());
        $this->applyCommonFilters($query, $filters, 'tithes');
        $this->applyApproved($query, 'tithes');

        return $query->get()->map(function ($item): array {
            $member = $item->member;
            $jumuiya = $item->jumuiya ?? $member?->familia?->jumuiya;
            $kanda = $jumuiya?->kanda;

            return [
                'receipt_type' => 'zaka',
                'source_type' => 'zaka',
                'source_id' => $item->id,
                'member_id' => $member?->id,
                'member_name' => $this->memberName($member),
                'phone' => $member?->phone,
                'familia_id' => $member?->familia_id,
                'familia_name' => $member?->familia?->name,
                'jumuiya_id' => $jumuiya?->id,
                'jumuiya_name' => $jumuiya?->name,
                'kanda_id' => $kanda?->id,
                'kanda_name' => $kanda?->name,
                'contribution_type_id' => null,
                'contribution_type_name' => db_trans('tithes'),
                'amount' => (float) ($item->amount ?? $item->kiasi ?? 0),
                'contribution_date' => optional($item->contribution_date ?? $item->tarehe ?? $item->date ?? $item->created_at)->toDateString(),
                'source_reference' => $item->receipt_no ?: $item->reference_no,
            ];
        })->values();
    }

    protected function contributionRows(array $filters, User $user): Collection
    {
        $cash = CashContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType']);
        $this->applyPeriod($cash, $filters, new CashContribution());
        $this->applyCommonFilters($cash, $filters, 'cash_contributions');
        $this->applyContributionType($cash, $filters);
        $this->applyApproved($cash, 'cash_contributions');

        $bank = BankContribution::query()->with(['member.familia.jumuiya.kanda', 'contributionType', 'bankAccount']);
        $this->applyPeriod($bank, $filters, new BankContribution());
        $this->applyCommonFilters($bank, $filters, 'bank_contributions');
        $this->applyContributionType($bank, $filters);
        $this->applyApproved($bank, 'bank_contributions');

        return $cash->get()
            ->map(fn ($item) => $this->mapContributionRow($item, 'mchango_taslimu'))
            ->merge($bank->get()->map(fn ($item) => $this->mapContributionRow($item, 'mchango_benki')))
            ->values();
    }

    protected function mapContributionRow($item, string $sourceType): array
    {
        $member = $item->member;
        $jumuiya = $member?->familia?->jumuiya;
        $kanda = $jumuiya?->kanda;

        if (! $jumuiya && ! empty($item->jumuiya_id)) {
            $jumuiya = Jumuiya::with('kanda')->find($item->jumuiya_id);
            $kanda = $jumuiya?->kanda;
        }

        return [
            'receipt_type' => 'mchango',
            'source_type' => $sourceType,
            'source_id' => $item->id,
            'member_id' => $member?->id,
            'member_name' => $this->memberName($member),
            'phone' => $member?->phone,
            'familia_id' => $member?->familia_id,
            'familia_name' => $member?->familia?->name,
            'jumuiya_id' => $jumuiya?->id,
            'jumuiya_name' => $jumuiya?->name,
            'kanda_id' => $kanda?->id,
            'kanda_name' => $kanda?->name,
            'contribution_type_id' => $item->contribution_type_id,
            'contribution_type_name' => $item->contributionType?->name ?? db_trans('contribution'),
            'amount' => (float) $item->amount,
            'contribution_date' => optional($item->contribution_date)->toDateString(),
            'source_reference' => $item->receipt_no ?: $item->reference_no,
        ];
    }

    protected function scopedQuery(?Authenticatable $user = null): Builder
    {
        return ReceiptIssue::query()->with(['member', 'familia', 'jumuiya', 'kanda', 'contributionType', 'issuedBy']);
    }

    protected function applyHistoryFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['receipt_no'] ?? null, fn (Builder $q, $value) => $q->where('receipt_no', 'like', "%{$value}%"))
            ->when($filters['status'] ?? null, fn (Builder $q, $value) => $q->where('status', ReceiptStatuses::normalize($value)))
            ->when($filters['receipt_type'] ?? null, fn (Builder $q, $value) => $q->where('receipt_type', $value))
            ->when($filters['member_id'] ?? null, fn (Builder $q, $value) => $q->where('member_id', (int) $value))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $value) => $q->whereDate('issued_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $value) => $q->whereDate('issued_at', '<=', $value));
    }

    protected function applyPeriod(Builder $query, array $filters, object $model): void
    {
        $table = $model->getTable();

        $dateColumn = match (true) {
            Schema::hasColumn($table, 'contribution_date') => 'contribution_date',
            Schema::hasColumn($table, 'tarehe') => 'tarehe',
            Schema::hasColumn($table, 'date') => 'date',
            default => 'created_at',
        };

        $query->whereYear($dateColumn, (int) ($filters['year'] ?? now()->year));

        if (! empty($filters['month'])) {
            $query->whereMonth($dateColumn, (int) $filters['month']);
        }
    }

    protected function applyCommonFilters(Builder $query, array $filters, string $table): void
    {
        if (! empty($filters['member_id'])) {
            $query->where('member_id', (int) $filters['member_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            if (Schema::hasColumn($table, 'jumuiya_id')) {
                $query->where(function (Builder $q) use ($filters): void {
                    $q->where('jumuiya_id', (int) $filters['jumuiya_id'])
                        ->orWhereHas('member.familia', fn (Builder $memberQuery) => $memberQuery->where('jumuiya_id', (int) $filters['jumuiya_id']));
                });
            } else {
                $query->whereHas('member.familia', fn (Builder $memberQuery) => $memberQuery->where('jumuiya_id', (int) $filters['jumuiya_id']));
            }
        }

        if (! empty($filters['kanda_id'])) {
            if (Schema::hasColumn($table, 'kanda_id')) {
                $query->where(function (Builder $q) use ($filters): void {
                    $q->where('kanda_id', (int) $filters['kanda_id'])
                        ->orWhereHas('member.familia.jumuiya', fn (Builder $memberQuery) => $memberQuery->where('kanda_id', (int) $filters['kanda_id']));
                });
            } else {
                $query->whereHas('member.familia.jumuiya', fn (Builder $memberQuery) => $memberQuery->where('kanda_id', (int) $filters['kanda_id']));
            }
        }
    }

    protected function applyContributionType(Builder $query, array $filters): void
    {
        if (! empty($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', (int) $filters['contribution_type_id']);
        }
    }

    protected function applyApproved(Builder $query, string $table): void
    {
        if (! Schema::hasColumn($table, 'status')) {
            return;
        }

        $query->whereNotIn('status', ['rejected', 'imekataliwa']);
    }

    protected function memberName(?Member $member): string
    {
        if (! $member) {
            return '—';
        }

        return $member->full_name
            ?? $member->name
            ?? trim(($member->first_name ?? '') . ' ' . ($member->middle_name ?? '') . ' ' . ($member->last_name ?? ''))
            ?: ('#' . $member->id);
    }
}
