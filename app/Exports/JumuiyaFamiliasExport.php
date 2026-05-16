<?php

namespace App\Exports;

use App\Models\Jumuiya;
use App\Models\User;
use App\Services\Jumuiya\JumuiyaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JumuiyaFamiliasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected JumuiyaService $jumuiyaService,
        protected Jumuiya $jumuiya,
        protected User $user,
        protected array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->jumuiyaService->getFamiliaExportRows(
            $this->jumuiya,
            $this->user,
            $this->filters
        );
    }

    public function headings(): array
    {
        return [
            db_trans('familia'),
            db_trans('phone'),
            db_trans('members'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->familia ?? '—',
            $row->phone ?? '—',
            (int) ($row->members ?? 0),
        ];
    }
}