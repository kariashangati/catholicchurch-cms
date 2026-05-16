<?php

namespace App\Exports;

use App\Models\Kanda;
use App\Models\User;
use App\Services\Kanda\KandaService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KandaBreakdownExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $rows;
    protected Collection $types;

    public function __construct(
        protected KandaService $kandaService,
        protected User $user,
        protected array $filters = [],
        protected ?Kanda $kanda = null,
    ) {
        $data = $this->kanda
            ? $this->kandaService->getKandaBreakdownExportData(
                $this->kanda,
                $this->user,
                $this->filters['from_date'] ?? null,
                $this->filters['to_date'] ?? null,
            )
            : $this->kandaService->getBreakdownExcelData(
                $this->user,
                $this->filters['from_date'] ?? null,
                $this->filters['to_date'] ?? null,
            );

        $this->rows = collect($data['rows'] ?? []);
        $this->types = collect($data['types'] ?? []);
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        $headings = [
            'S/N',
            db_trans('kanda'),
            db_trans('jumuiya'),
            db_trans('familias'),
            db_trans('members'),
            db_trans('tithes'),
            db_trans('offerings'),
        ];

        foreach ($this->types as $type) {
            $headings[] = $type['name'];
        }

        $headings[] = db_trans('grand_total');

        return $headings;
    }

    public function map($row): array
    {
        $mapped = [
            $row['sn'] ?? '',
            $row['kanda_name'] ?? '',
            $row['jumuiya_name'] ?? '',
            (int) ($row['familias_count'] ?? 0),
            (int) ($row['members_count'] ?? 0),
            (float) ($row['tithes'] ?? 0),
            (float) ($row['offerings'] ?? 0),
        ];

        foreach ($this->types as $type) {
            $mapped[] = (float) ($row['contribution_type_totals'][$type['slug']]['amount'] ?? 0);
        }

        $mapped[] = (float) ($row['grand_total'] ?? 0);

        return $mapped;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}