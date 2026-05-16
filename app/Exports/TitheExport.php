<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\TitheDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TitheExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected TitheDashboardService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getTithesExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            db_trans('date'),
            db_trans('member'),
            db_trans('phone'),
            db_trans('jumuiya'),
            db_trans('amount'),
            db_trans('payment_method'),
            db_trans('status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->date ?? '—',
            $row->member ?? '—',
            $row->phone ?? '—',
            $row->jumuiya ?? '—',
            (float) ($row->amount ?? 0),
            $row->payment_method ?? '—',
            $row->status ?? '—',
        ];
    }
}