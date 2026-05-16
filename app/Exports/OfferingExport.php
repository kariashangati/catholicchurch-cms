<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\FinanceDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OfferingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected FinanceDashboardService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getOfferingsExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('offering_type'),
            db_trans('mass_type'),
            db_trans('collection_scope'),
            db_trans('location'),
            db_trans('amount'),
            db_trans('payment_method'),
            db_trans('collection_date'),
            db_trans('status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->offering_type ?? '—',
            $row->mass_type ?? '—',
            $row->collection_scope ?? '—',
            $row->location ?? '—',
            (float) ($row->amount ?? 0),
            $row->payment_method ?? '—',
            $row->collection_date ?? '—',
            $row->status ?? '—',
        ];
    }
}