<?php

namespace App\Exports;

use App\Services\Finance\BudgetEstimateService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BudgetIncomeEstimateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected BudgetEstimateService $service,
        protected int $year,
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getIncomeExportRows($this->year);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('source_type'),
            db_trans('category'),
            db_trans('group'),
            db_trans('amount'),
            db_trans('year'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn,
            $row->source_type,
            $row->category,
            $row->group,
            (float) $row->amount,
            $row->year,
        ];
    }
}