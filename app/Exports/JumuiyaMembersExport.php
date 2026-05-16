<?php

namespace App\Exports;

use App\Models\Jumuiya;
use App\Models\User;
use App\Services\Jumuiya\JumuiyaService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JumuiyaMembersExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected JumuiyaService $jumuiyaService,
        protected Jumuiya $jumuiya,
        protected User $user,
    ) {}

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('member_code'),
            db_trans('gender'),
            db_trans('phone'),
            db_trans('familia'),
            db_trans('family_role'),
            db_trans('joined'),
        ];
    }

    public function array(): array
    {
        return $this->jumuiyaService
            ->getMembersExportRows($this->jumuiya, $this->user)
            ->map(fn ($row) => [
                $row['sn'],
                $row['member'],
                $row['member_code'],
                $row['gender'],
                $row['phone'],
                $row['familia'],
                $row['family_role'],
                $row['joined'],
            ])
            ->toArray();
    }
}