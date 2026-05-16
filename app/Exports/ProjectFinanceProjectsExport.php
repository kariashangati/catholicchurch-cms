<?php

namespace App\Exports;

use App\Services\Finance\ProjectFinanceDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectFinanceProjectsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ProjectFinanceDashboardService $service,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getProjectsExportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('project'),
            db_trans('project_category'),
            db_trans('status'),
            db_trans('budget_amount'),
            db_trans('target_amount'),
            db_trans('income'),
            db_trans('expense'),
            db_trans('balance'),
            db_trans('start_date'),
            db_trans('end_date'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->project ?? '—',
            $row->category ?? '—',
            $row->status ?? '—',
            (float) ($row->budget_amount ?? 0),
            (float) ($row->target_amount ?? 0),
            (float) ($row->income ?? 0),
            (float) ($row->expense ?? 0),
            (float) ($row->balance ?? 0),
            $row->start_date ?? '—',
            $row->end_date ?? '—',
        ];
    }
}