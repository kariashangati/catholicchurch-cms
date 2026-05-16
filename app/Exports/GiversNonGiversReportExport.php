<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Finance\GiversNonGiversReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GiversNonGiversReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected string $selectedDataLabel;

    protected bool $showJumuiyaColumn;

    public function __construct(
        protected GiversNonGiversReportService $service,
        protected User $user,
        protected array $filters = [],
    ) {
        $this->selectedDataLabel = $this->service->selectedExportDataLabel($this->filters);
        $this->showJumuiyaColumn = $this->service->shouldShowJumuiyaColumn($this->filters);
    }

    public function collection(): Collection
    {
        return $this->service->getExportRows($this->user, $this->filters);
    }

    public function headings(): array
    {
        $headings = [
            db_trans('sn'),
            db_trans('member'),
            db_trans('phone'),
        ];

        if ($this->showJumuiyaColumn) {
            $headings[] = db_trans('jumuiya');
        }

        $headings[] = db_trans('date');
        $headings[] = $this->selectedDataLabel;
        $headings[] = db_trans('status');

        return $headings;
    }

    public function map($row): array
    {
        $mapped = [
            $row->sn,
            $row->member . ' (' . $row->member_code . ')',
            $row->phone,
        ];

        if ($this->showJumuiyaColumn) {
            $mapped[] = $row->jumuiya;
        }

        $mapped[] = $row->date;
        $mapped[] = (float) $row->amount;
        $mapped[] = $row->status;

        return $mapped;
    }
}