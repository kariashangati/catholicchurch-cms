<?php

namespace App\Exports;

use App\Models\Jumuiya;
use App\Models\User;
use App\Services\Sacrament\SacramentReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JumuiyaSacramentMembersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected SacramentReportService $service,
        protected Jumuiya $jumuiya,
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
            db_trans('gender'),
            db_trans('phone'),
            db_trans('baptized'),
            db_trans('communion'),
            db_trans('confirmation'),
            db_trans('married'),
            db_trans('receiving_eucharist'),
        ];
    }

    public function collection(): Collection
    {
        return $this->service
            ->getJumuiyaMemberExportRows($this->jumuiya, $this->user, $this->filters)
            ->map(fn ($row) => [
                $row->sn,
                $row->member,
                $row->familia,
                $row->gender,
                $row->phone,
                $row->baptized,
                $row->communion,
                $row->confirmation,
                $row->married,
                $row->eucharist,
            ]);
    }
}