<?php

namespace App\Exports;

use App\Models\ApostolicGroup;
use App\Models\User;
use App\Services\ApostolicGroup\ApostolicGroupService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApostolicGroupMembersExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ApostolicGroupService $service,
        protected ApostolicGroup $group,
        protected User $user
    ) {
    }

    public function collection(): Collection
    {
        return $this->service->getMembersExportRows($this->group, $this->user)
            ->map(function ($row) {
                return [
                    db_trans('member') => $row->member_name,
                    db_trans('familia') => $row->familia_name,
                    db_trans('jumuiya') => $row->jumuiya_name,
                    db_trans('role') => $row->role_label,
                    db_trans('status') => $row->status_label,
                    db_trans('joined') => $row->joined_at_label,
                ];
            });
    }

    public function headings(): array
    {
        return [
            db_trans('member'),
            db_trans('familia'),
            db_trans('jumuiya'),
            db_trans('role'),
            db_trans('status'),
            db_trans('joined'),
        ];
    }
}