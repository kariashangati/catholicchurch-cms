<?php

namespace App\Exports;

use App\Services\Finance\ProjectFinanceDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectFinanceTopProjectsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected ProjectFinanceDashboardService $service,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getTopProjectsExportRows($this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('project'),
            db_trans('status'),
            db_trans('income'),
            db_trans('expense'),
            db_trans('balance'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->project ?? '—',
            $row->status ?? '—',
            (float) ($row->income ?? 0),
            (float) ($row->expense ?? 0),
            (float) ($row->balance ?? 0),
        ];
    }
}