<?php

namespace App\Services\Pdf;

use App\Services\SystemConfig\SiteSettingsService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Symfony\Component\Process\Process;

class PdfReportService
{
    public function __construct(
        protected SiteSettingsService $siteSettingsService,
    ) {
    }

    public function exportView(string $viewName, array $data, string $filenamePrefix = 'report'): string
    {
        $locale = $this->resolveLocale($data);

        App::setLocale($locale);

        $payload = array_merge($data, [
            'branding' => $this->getPdfBranding(),
            'locale' => $locale,
        ]);

        return $this->renderWkhtmlPdf(
            viewName: $viewName,
            data: $payload,
            filename: $this->makeFilename($filenamePrefix)
        );
    }

    protected function resolveLocale(array $data): string
    {
        if (!empty($data['locale'])) {
            return $data['locale'];
        }

        if (auth()->check() && !empty(auth()->user()->locale)) {
            return auth()->user()->locale;
        }

        if (session('locale')) {
            return session('locale');
        }

        return $this->siteSettingsService->get('site.default_locale', config('app.locale', 'en'))
            ?? config('app.locale', 'en');
    }

    protected function getPdfBranding(): array
    {
        $siteName = $this->siteSettingsService->get('site.name', config('app.name'));
        $logo = $this->siteSettingsService->get('site.logo');
        $secondaryLogo = $this->siteSettingsService->get('site.secondary_logo');

        return [
            'site_name' => $siteName,
            'diocese_name' => $this->siteSettingsService->get('church.diocese_name'),
            'email' => $this->siteSettingsService->get('church.email'),
            'phone' => $this->siteSettingsService->get('church.phone'),
            'address' => $this->siteSettingsService->get('church.address'),
            'logo' => $this->toPublicPath($logo),
            'left_logo' => $this->toPublicPath($logo),
            'right_logo' => $this->toPublicPath($secondaryLogo ?: $logo),
            'left_logo_label' => null,
            'right_logo_label' => null,
            'footer_text' => $this->siteSettingsService->get('footer.bottom_note', $siteName),
            'footer_quote' => $this->siteSettingsService->get('footer.tagline'),
        ];
    }

    protected function renderWkhtmlPdf(string $viewName, array $data, string $filename): string
    {
        $html = View::make($viewName, $data)->render();

        $directory = storage_path('app/reports/pdf');

        if (!is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $htmlPath = $directory . '/' . pathinfo($filename, PATHINFO_FILENAME) . '.html';
        $pdfPath = $directory . '/' . $filename;

        File::put($htmlPath, $html);

        $binary = $this->resolveWkhtmltopdfBinary();

        $arguments = [
            '--enable-local-file-access',
            '--encoding', 'utf-8',
            '--page-size', 'A4',
            '--margin-top', '10mm',
            '--margin-right', '10mm',
            '--margin-bottom', '12mm',
            '--margin-left', '10mm',
            '--print-media-type',
            $htmlPath,
            $pdfPath,
        ];

        if ($this->isBatchFile($binary)) {
            $command = array_merge(['cmd', '/c', $binary], $arguments);
        } else {
            $command = array_merge([$binary], $arguments);
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "wkhtmltopdf failed.\nCommand: " . implode(' ', $command) .
                "\nError Output: " . $process->getErrorOutput() .
                "\nStandard Output: " . $process->getOutput()
            );
        }

        return $pdfPath;
    }

    protected function resolveWkhtmltopdfBinary(): string
    {
        $configured = config('services.wkhtmltopdf.binary');

        if (!empty($configured)) {
            return str_replace('\\', '/', trim($configured));
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'C:/wkhtmltopdf/wkhtmltopdf.bat';
        }

        return '/usr/bin/wkhtmltopdf';
    }

    protected function isBatchFile(string $path): bool
    {
        $path = strtolower($path);

        return str_ends_with($path, '.bat') || str_ends_with($path, '.cmd');
    }

    protected function toPublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $clean = ltrim(trim($path), '/\\');
        $clean = str_replace('\\', '/', $clean);

        if (
            preg_match('/^[A-Za-z]:[\/\\\\]/', $path) ||
            str_starts_with($path, '\\\\') ||
            str_starts_with($path, '/')
        ) {
            $absolutePath = str_replace('/', DIRECTORY_SEPARATOR, $path);

            if (is_file($absolutePath)) {
                return $absolutePath;
            }
        }

        if (str_starts_with($clean, 'storage/')) {
            $storagePath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $clean));

            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        $publicPath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $clean));

        if (is_file($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    protected function makeFilename(string $prefix): string
    {
        return $prefix . '-' . now()->format('Ymd-His') . '.pdf';
    }
}