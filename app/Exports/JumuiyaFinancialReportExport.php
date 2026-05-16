<?php

namespace App\Exports;

use App\Exports\Sheets\ArrayReportSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class JumuiyaFinancialReportExport implements WithMultipleSheets
{
    public function __construct(protected array $sheets)
    {
    }

    public function sheets(): array
    {
        $items = [];

        foreach ($this->sheets as $title => $rows) {
            $items[] = new ArrayReportSheet($rows, (string) $title);
        }

        return $items;
    }
}