<?php

namespace App\Exports;

use App\Models\Familia;
use App\Models\User;
use App\Services\Familia\FamiliaService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FamiliaMembersExport implements FromArray, WithHeadings
{
    public function __construct(
        protected FamiliaService $service,
        protected Familia $familia,
        protected User $user
    ) {}

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('gender'),
            db_trans('family_role'),
            db_trans('phone'),
            db_trans('sacraments'),
            db_trans('status'),
        ];
    }

    public function array(): array
    {
        return $this->service
            ->getMembersExportRows($this->familia, $this->user)
            ->map(fn($r) => [
                $r->sn,
                $r->member,
                $r->gender,
                $r->family_role,
                $r->phone,
                $r->sacraments,
                $r->status,
            ])->toArray();
    }
}