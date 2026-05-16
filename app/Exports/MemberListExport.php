<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Member\MemberService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberListExport implements FromArray, WithHeadings
{
    public function __construct(
        protected MemberService $memberService,
        protected User $user,
        protected array $filters = []
    ) {}

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('member_code'),
            db_trans('phone'),
            db_trans('familia'),
            db_trans('jumuiya'),
            db_trans('kanda'),
            db_trans('sacraments'),
            db_trans('status'),
        ];
    }

    public function array(): array
    {
        return $this->memberService
            ->getExportRows($this->user, $this->filters)
            ->map(fn ($row) => [
                $row->sn,
                $row->name,
                $row->member_code,
                $row->phone,
                $row->familia_name,
                $row->jumuiya_name,
                $row->kanda_name,
                $row->sacraments,
                $row->status,
            ])
            ->toArray();
    }
}