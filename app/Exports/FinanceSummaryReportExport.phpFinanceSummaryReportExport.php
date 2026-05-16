<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Reports\FinanceReportsService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinanceSummaryReportExport implements FromArray, ShouldAutoSize
{
    public function __construct(
        protected FinanceReportsService $service,
        protected User $user,
        protected array $filters = [],
    ) {
    }

    public function array(): array
    {
        return $this->service->financialSummaryExportArray($this->user, $this->filters);
    }
}