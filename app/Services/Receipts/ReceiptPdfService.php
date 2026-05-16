<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class ReceiptPdfService
{
    public function __construct(
        protected SiteSettingsService $siteSettings,
        protected ReceiptQrCodeService $qrCodeService,
    ) {
    }


    public function downloadIssuedReceipt(ReceiptIssue $receipt, array $extra = []): Response
    {
        $path = $receipt->pdf_path && is_file($receipt->pdf_path)
            ? $receipt->pdf_path
            : $this->renderSingle($receipt);

        $receipt->forceFill([
            'status' => \App\Support\Receipts\ReceiptStatuses::DOWNLOADED,
            'printed_at' => $receipt->printed_at ?: now(),
            'last_printed_at' => now(),
            'first_downloaded_at' => $receipt->first_downloaded_at ?: now(),
            'last_downloaded_at' => now(),
            'downloaded_at' => now(),
            'download_count' => (int) ($receipt->download_count ?? 0) + 1,
            'print_count' => (int) ($receipt->print_count ?? 0) + 1,
            'pdf_path' => $path,
        ])->save();

        return response()->download($path, ($receipt->receipt_no ?: 'receipt') . '.pdf')->deleteFileAfterSend(false);
    }

    public function renderSingle(ReceiptIssue $receipt): string
    {
        return $this->renderBatch(collect([$receipt]), 'receipt-' . $receipt->receipt_no);
    }

    public function renderBatch(Collection $receipts, string $filenamePrefix): string
    {
        $receipts = $receipts->map(fn ($receipt) => $receipt->fresh(['member.familia.jumuiya.kanda', 'jumuiya.kanda', 'kanda', 'contributionType']));

        $qrHtmls = $receipts->mapWithKeys(fn (ReceiptIssue $receipt) => [$receipt->id => $this->qrCodeService->html($receipt)]);
        $branding = $this->branding();

        $html = View::make('receipts.pdf.receipt', compact('receipts', 'qrHtmls', 'branding'))->render();
        $directory = storage_path('app/receipts/pdf');
        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9\-]+/', '-', $filenamePrefix) ?: 'receipts';
        $htmlPath = $directory . '/' . $safeName . '.html';
        $pdfPath = $directory . '/' . $safeName . '.pdf';
        File::put($htmlPath, $html);

        $binary = $this->wkhtmltopdfBinary();
        $arguments = [
            '--enable-local-file-access',
            '--encoding', 'utf-8',
            '--page-width', '80mm',
            '--page-height', '127mm',
            '--margin-top', '4mm',
            '--margin-right', '4mm',
            '--margin-bottom', '4mm',
            '--margin-left', '4mm',
            '--print-media-type',
            $htmlPath,
            $pdfPath,
        ];

        $command = str_ends_with(strtolower($binary), '.bat') || str_ends_with(strtolower($binary), '.cmd')
            ? array_merge(['cmd', '/c', $binary], $arguments)
            : array_merge([$binary], $arguments);

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('wkhtmltopdf failed: ' . $process->getErrorOutput());
        }

        return $pdfPath;
    }

    public function branding(): array
    {
        $siteName = $this->siteSettings->get('site.name', config('app.name', 'ChurchMS'));
        $logo = $this->siteSettings->get('site.logo') ?: $this->siteSettings->get('church.logo');

        return [
            'site_name' => $siteName,
            'diocese_name' => $this->siteSettings->get('church.diocese_name', ''),
            'phone' => $this->siteSettings->get('church.phone', ''),
            'email' => $this->siteSettings->get('church.email', ''),
            'address' => $this->siteSettings->get('church.address', ''),
            'logo' => $this->toPublicPath($logo),
            'footer_text' => $this->siteSettings->get('footer.bottom_note', 'TUMSIFU YESU KRISTO'),
        ];
    }

    protected function wkhtmltopdfBinary(): string
    {
        $configured = config('services.wkhtmltopdf.binary');
        if (! empty($configured)) {
            return str_replace('\\', '/', trim($configured));
        }

        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'C:/wkhtmltopdf/wkhtmltopdf.bat'
            : '/usr/bin/wkhtmltopdf';
    }

    protected function toPublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $clean = str_replace('\\', '/', ltrim(trim($path), '/\\'));
        $public = public_path($clean);
        if (is_file($public)) {
            return $public;
        }

        $storage = public_path('storage/' . preg_replace('#^storage/#', '', $clean));
        return is_file($storage) ? $storage : null;
    }
}
