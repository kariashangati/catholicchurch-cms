<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Sacrament\SacramentReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JumuiyaSacramentSummaryExport implements FromArray, WithHeadings
{
    public function __construct(
        protected SacramentReportService $service,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('jumuiya'),
            db_trans('kanda'),
            db_trans('familias'),
            db_trans('members'),
            db_trans('baptized'),
            db_trans('communion'),
            db_trans('confirmation'),
            db_trans('married'),
            db_trans('receiving_eucharist'),
        ];
    }

    public function array(): array
    {
        return $this->service
            ->getJumuiyaExportRows($this->user, $this->filters)
            ->map(fn ($row) => [
                $row->sn,
                $row->name,
                $row->kanda_name,
                $row->familias,
                $row->members,
                $row->baptized,
                $row->communion,
                $row->confirmation,
                $row->married,
                $row->eucharist,
            ])
            ->toArray();
    }
}