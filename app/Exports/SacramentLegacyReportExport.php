<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Reports\SacramentLegacyReportsService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SacramentLegacyReportExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected SacramentLegacyReportsService $service,
        protected User $user,
        protected array $filters = [],
    ) {
    }

    public function array(): array
    {
        $data = $this->service->exportData($this->user, $this->filters);
        $rows = $this->service->exportRows($this->user, $this->filters);

        $summary = $data['summary'] ?? [];

        $output = [
            [$data['reportTitle']],
            [db_trans('generated_on'), $data['issuedAtText']],
            [db_trans('filters'), $data['filterLabel']],
            [],
            [db_trans('summary')],
            [db_trans('members'), $summary['members_total'] ?? 0],
            [db_trans('baptized'), $summary['baptized'] ?? 0],
            [db_trans('communion'), $summary['communion'] ?? 0],
            [db_trans('confirmation'), $summary['confirmation'] ?? 0],
            [db_trans('married'), $summary['married'] ?? 0],
            [db_trans('receiving_eucharist'), $summary['receives_eucharist'] ?? 0],
            [],
        ];

        foreach ($rows as $row) {
            $output[] = $row;
        }

        return $output;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A5')->getFont()->setBold(true);

        return [
            13 => ['font' => ['bold' => true]],
        ];
    }
}