<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\TitheDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TitheActivityDateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected TitheDashboardService $service,
        protected User $user,
        protected string $date,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getTitheActivityDateExportRows(
            $this->user,
            $this->date,
            $this->filters
        );
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('phone'),
            db_trans('kanda'),
            db_trans('jumuiya'),
            db_trans('recorded_by'),
            db_trans('amount'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->member ?? '—',
            $row->phone ?? '—',
            $row->kanda ?? '—',
            $row->jumuiya ?? '—',
            $row->recorded_by ?? '—',
            (float) ($row->amount ?? 0),
        ];
    }
}