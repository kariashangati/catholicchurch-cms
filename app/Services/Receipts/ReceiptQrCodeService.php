<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use Illuminate\Support\Str;

class ReceiptQrCodeService
{
    public function verificationUrl(ReceiptIssue $receipt): string
    {
        return $receipt->verification_url;
    }

    /**
     * Return QR code HTML for Blade/PDF rendering without PNG/Imagick.
     * Preferred backend: milon/barcode DNS2D::getBarcodeHTML().
     */
    public function html(ReceiptIssue $receipt, int $widthFactor = 4, int $heightFactor = 4): string
    {
        $url = $this->verificationUrl($receipt);

        if (class_exists(\Milon\Barcode\Facades\DNS2DFacade::class)) {
            return (string) \Milon\Barcode\Facades\DNS2DFacade::getBarcodeHTML(
                $url,
                'QRCODE',
                $widthFactor,
                $heightFactor
            );
        }

        if (class_exists('DNS2D')) {
            return (string) \DNS2D::getBarcodeHTML(
                $url,
                'QRCODE',
                $widthFactor,
                $heightFactor
            );
        }

        return $this->fallbackSvg($receipt);
    }

    /**
     * Backward-compatible method name used by some existing receipt views.
     */
    public function svg(ReceiptIssue $receipt): string
    {
        return $this->html($receipt);
    }

    /**
     * Backward-compatible method. It intentionally does not create PNG files,
     * because PNG QR generation needs Imagick/GD backends on many hosts.
     */
    public function makePngPath(ReceiptIssue $receipt): ?string
    {
        return null;
    }

    protected function fallbackSvg(ReceiptIssue $receipt): string
    {
        $url = e($this->verificationUrl($receipt));
        $code = e(Str::limit((string) $receipt->verification_code, 18, ''));

        return '<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140">'
            . '<rect width="140" height="140" fill="#fff"/>'
            . '<rect x="10" y="10" width="120" height="120" fill="none" stroke="#111" stroke-width="4"/>'
            . '<text x="70" y="62" text-anchor="middle" font-size="10" fill="#111">QR</text>'
            . '<text x="70" y="80" text-anchor="middle" font-size="8" fill="#111">' . $code . '</text>'
            . '<desc>' . $url . '</desc>'
            . '</svg>';
    }
}
