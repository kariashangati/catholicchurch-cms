<?php

namespace App\Http\Controllers;

use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Services\Kanda\KandaPdfExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KandaReportExportController extends Controller
{
    public function __construct(
        protected KandaPdfExportService $pdfExportService
    ) {
    }

    public function global(Request $request): BinaryFileResponse
    {
        $file = $this->pdfExportService->exportGlobalReport(
            auth()->user(),
            $request->only(['year', 'month'])
        );

        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function kanda(Request $request, Kanda $kanda): BinaryFileResponse
    {
        $file = $this->pdfExportService->exportKandaReport(
            $kanda,
            auth()->user(),
            $request->only(['year', 'month'])
        );

        return response()->download($file)->deleteFileAfterSend(true);
    }

    public function jumuiya(Request $request, Jumuiya $jumuiya): BinaryFileResponse
    {
        $file = $this->pdfExportService->exportJumuiyaReport(
            $jumuiya,
            auth()->user(),
            $request->only(['year', 'month'])
        );

        return response()->download($file)->deleteFileAfterSend(true);
    }
}