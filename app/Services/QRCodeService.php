<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a pool
     *
     * @param string $poolNumber
     * @param string $poolName
     * @return string URL to QR code image
     */
    public function generatePoolQRCode($poolNumber, $poolName)
    {
        // Create the join URL
        $joinUrl = config('app.url') . "/squares/join?pool={$poolNumber}";

        // Generate QR code using Google Charts API (free, no dependencies needed)
        // Alternative: Use endroid/qr-code package if you want local generation
        $qrCodeUrl = $this->generateGoogleChartsQRCode($joinUrl);

        return $qrCodeUrl;
    }

    /**
     * Generate QR code using Google Charts API
     */
    protected function generateGoogleChartsQRCode($data, $size = 300)
    {
        $encodedData = urlencode($data);
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encodedData}&choe=UTF-8";
    }

    /**
     * Alternative: Generate QR code locally using endroid/qr-code package
     * Requires: composer require endroid/qr-code
     */
    /*
    protected function generateLocalQRCode($data, $poolNumber)
    {
        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $qrCode = \Endroid\QrCode\QrCode::create($data)
            ->setSize(300)
            ->setMargin(10);

        $result = $writer->write($qrCode);

        // Save to storage
        $filename = "qr-codes/pool-{$poolNumber}.png";
        Storage::disk('public')->put($filename, $result->getString());

        return Storage::url($filename);
    }
    */
}
