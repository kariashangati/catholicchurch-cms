<?php

namespace App\Exports;

use App\Models\Kanda;
use App\Models\User;
use App\Services\Kanda\KandaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KandaRecentMembersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected KandaService $service,
        protected Kanda $kanda,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getRecentMembersExportRows($this->kanda, $this->user, $this->filters);
    }

    public function headings(): array
    {
        return [
            db_trans('member'),
            db_trans('jumuiya'),
            db_trans('gender'),
            db_trans('phone'),
            db_trans('joined'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->member,
            $row->jumuiya,
            $row->gender,
            $row->phone,
            $row->joined,
        ];
    }
}