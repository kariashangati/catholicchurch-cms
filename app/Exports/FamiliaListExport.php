<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Familia\FamiliaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FamiliaListExport implements FromCollection, WithHeadings
{
    protected Collection $rows;

    public function __construct(
        protected FamiliaService $familiaService,
        protected User $user
    ) {
        $this->rows = $this->familiaService->getExportRows($this->user);
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
            return [
                $row->name,
                $row->jumuiya_name,
                $row->phone,
                $row->envelope_no,
                $row->members_count,
                $row->status_label,
            ];
        });
    }

    public function headings(): array
    {
        return [
            db_trans('family'),
            db_trans('jumuiya'),
            db_trans('phone'),
            db_trans('envelope_no'),
            db_trans('members_count'),
            db_trans('status'),
        ];
    }
}