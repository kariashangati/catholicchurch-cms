<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\TitheDashboardService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TitheActivityLogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected TitheDashboardService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getTitheActivityLogExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            db_trans('id'),
            db_trans('activity_date'),
            db_trans('number_of_records'),
            db_trans('recorders'),
            db_trans('bulk_batches'),
            db_trans('amount'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->sn ?? '',
            $row->activity_date ?? '—',
            (int) ($row->records_count ?? 0),
            (int) ($row->recorders_count ?? 0),
            (int) ($row->batches_count ?? 0),
            (float) ($row->amount ?? 0),
        ];
    }
}