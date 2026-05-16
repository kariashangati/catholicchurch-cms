<?php

namespace App\Exports;

use App\Services\ApostolicGroup\ApostolicGroupService;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApostolicGroupsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected ApostolicGroupService $service,
        protected User $user
    ) {
    }

    public function headings(): array
    {
        return [
            db_trans('group'),
            db_trans('leader'),
            db_trans('membership_rule'),
            db_trans('meeting'),
            db_trans('members'),
            db_trans('status'),
        ];
    }

    public function array(): array
    {
        return $this->service->getExportRows($this->user)
            ->map(fn ($row) => [
                $row->name,
                $row->leader_name,
                $row->membership_rule_label,
                $row->meeting_label,
                $row->members_count,
                $row->status_label,
            ])
            ->toArray();
    }
}