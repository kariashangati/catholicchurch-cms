<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Mafundisho\MafundishoEnrollmentService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MafundishoTypeExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected MafundishoEnrollmentService $service,
        protected User $user,
        protected string $type,
        protected int $year,
        protected ?string $status = null,
        protected ?int $month = null
    ) {
    }

    public function headings(): array
    {
        return [
            '#',
            db_trans('member'),
            db_trans('member_code'),
            db_trans('familia'),
            db_trans('jumuiya'),
            db_trans('teaching_type'),
            db_trans('year'),
            db_trans('status'),
            db_trans('started_on'),
            db_trans('ended_on'),
        ];
    }

    public function array(): array
    {
        return $this->service
            ->getTypeExportRows(
                $this->user,
                $this->type,
                $this->year,
                $this->status,
                $this->month
            )
            ->map(fn ($row) => [
                $row->sn,
                $row->member,
                $row->member_code,
                $row->familia,
                $row->jumuiya,
                $row->teaching_type,
                $row->year,
                $row->status,
                $row->started_on,
                $row->ended_on,
            ])
            ->toArray();
    }
}