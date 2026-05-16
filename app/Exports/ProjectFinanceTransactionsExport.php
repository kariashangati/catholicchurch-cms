<?php

namespace App\Exports;

use App\Services\Finance\ProjectFinanceDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectFinanceTransactionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ProjectFinanceDashboardService $service,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getTransactionsExportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('date'),
            db_trans('project'),
            db_trans('project_category'),
            db_trans('type'),
            db_trans('amount'),
            db_trans('status'),
            db_trans('payment_method'),
            db_trans('reference_no'),
            db_trans('recorded_by'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->date ?? '—',
            $row->project ?? '—',
            $row->category ?? '—',
            $row->type ?? '—',
            (float) ($row->amount ?? 0),
            $row->status ?? '—',
            $row->payment_method ?? '—',
            $row->reference_no ?? '—',
            $row->recorded_by ?? '—',
        ];
    }
}