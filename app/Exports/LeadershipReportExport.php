<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadershipReportExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected array $sheets,
        protected string $title = 'Leadership Report'
    ) {
    }

    public function sheets(): array
    {
        $worksheets = [];

        foreach ($this->sheets as $title => $rows) {
            $worksheets[] = new LeadershipReportSheet((string) $title, $rows);
        }

        return $worksheets;
    }
}

class LeadershipReportSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected string $title,
        protected array $rows
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        $title = preg_replace('/[\\\/\?\*\[\]\:]/', ' ', $this->title) ?: 'Report';
        return mb_substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $highestColumn . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEAF2FF');

        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
            ->getColor()->setARGB('FFD9E7F4');

        $sheet->freezePane('A2');

        return [];
    }
}
