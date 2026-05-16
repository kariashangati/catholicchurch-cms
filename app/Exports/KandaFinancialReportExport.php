<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class KandaFinancialReportExport implements FromArray, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected array $rows,
        protected string $title
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return mb_substr($this->title ?: 'Report', 0, 31);
    }
}