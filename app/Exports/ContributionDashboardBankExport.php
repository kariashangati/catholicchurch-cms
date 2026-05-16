<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\ContributionDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContributionDashboardBankExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ContributionDashboardService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getBankExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('contribution_type'),
            db_trans('bank_account'),
            db_trans('amount'),
            db_trans('reference_no'),
            db_trans('status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->member ?? '—',
            $row->contribution_type ?? '—',
            $row->bank_account ?? '—',
            (float) ($row->amount ?? 0),
            $row->reference_no ?? '—',
            $row->status ?? '—',
        ];
    }
}