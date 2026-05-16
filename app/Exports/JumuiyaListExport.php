<?php

namespace App\Exports;

use App\Services\Jumuiya\JumuiyaService;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JumuiyaListExport implements FromCollection, WithHeadings
{
    protected Collection $rows;

    public function __construct(
        protected JumuiyaService $jumuiyaService,
        protected User $user
    ) {
        $this->rows = $this->jumuiyaService->getExportRows($this->user);
    }

    public function collection(): Collection
    {
        return $this->rows->map(function ($row) {
            return [
                $row->name,
                $row->kanda_name,
                $row->members_count,
                $row->status_label,
                $row->created_at_label,
            ];
        });
    }

    public function headings(): array
    {
        return [
            db_trans('jumuiya'),
            db_trans('kanda'),
            db_trans('members'),
            db_trans('status'),
            db_trans('created_at'),
        ];
    }
}