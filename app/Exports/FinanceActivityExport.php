<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\FinanceDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinanceActivityExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected FinanceDashboardService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getUnifiedActivityExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            db_trans('type'),
            db_trans('description'),
            db_trans('location'),
            db_trans('date'),
            db_trans('status'),
            db_trans('amount'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->type ?? '—',
            $row->description ?? '—',
            $row->location ?? '—',
            $row->date ?? '—',
            $row->status ?? '—',
            (float) ($row->amount ?? 0),
        ];
    }
}