<?php

namespace App\Exports;

use App\Models\Kanda;
use App\Models\User;
use App\Services\Sacrament\SacramentReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandaSacramentMembersExport implements FromArray, WithHeadings
{
    public function __construct(
        protected SacramentReportService $service,
        protected Kanda $kanda,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('familia'),
            db_trans('jumuiya'),
            db_trans('gender'),
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
            ->getKandaMemberExportRows($this->kanda, $this->user, $this->filters)
            ->map(fn ($row) => [
                $row->sn,
                $row->member,
                $row->familia,
                $row->jumuiya,
                $row->gender,
                $row->baptized,
                $row->communion,
                $row->confirmation,
                $row->married,
                $row->eucharist,
            ])
            ->toArray();
    }
}