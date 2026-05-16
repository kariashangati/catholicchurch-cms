<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Sacrament\SacramentReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandaSacramentSummaryExport implements FromArray, WithHeadings
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
            db_trans('kanda'),
            db_trans('code'),
            db_trans('jumuiyas'),
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
            ->getKandaExportRows($this->user, $this->filters)
            ->map(fn ($row) => [
                $row->sn,
                $row->name,
                $row->code,
                $row->jumuiyas,
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