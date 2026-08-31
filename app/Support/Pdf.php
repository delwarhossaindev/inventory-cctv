<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Thin wrapper around mPDF: render a Blade view to a PDF response.
 */
class Pdf
{
    /**
     * Render a Blade view and return it as a PDF HTTP response.
     */
    public static function render(string $view, array $data, string $filename, bool $download = false, array $config = [])
    {
        $html = view($view, $data)->render();

        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf(array_merge([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'margin_top' => 6,
            'margin_bottom' => 30,
            'margin_left' => 9,
            'margin_right' => 9,
            'default_font' => 'dejavusans',
        ], $config));

        $mpdf->WriteHTML($html);

        $content = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
        ]);
    }

    /**
     * Convert a number to words (Indian numbering: thousand / lakh / crore) for Taka.
     */
    public static function amountInWords($number): string
    {
        $number = (int) round((float) $number);
        if ($number === 0) {
            return 'Zero Taka Only';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $twoDigits = function ($n) use ($ones, $tens) {
            if ($n < 20) {
                return $ones[$n];
            }
            return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
        };

        $threeDigits = function ($n) use ($ones, $twoDigits) {
            $str = '';
            if ($n >= 100) {
                $str .= $ones[intdiv($n, 100)] . ' Hundred';
                $n %= 100;
                if ($n) {
                    $str .= ' ';
                }
            }
            if ($n) {
                $str .= $twoDigits($n);
            }
            return $str;
        };

        $parts = [];
        $crore = intdiv($number, 10000000);
        $number %= 10000000;
        $lakh = intdiv($number, 100000);
        $number %= 100000;
        $thousand = intdiv($number, 1000);
        $number %= 1000;
        $hundred = $number;

        if ($crore) {
            $parts[] = $threeDigits($crore) . ' Crore';
        }
        if ($lakh) {
            $parts[] = $threeDigits($lakh) . ' Lakh';
        }
        if ($thousand) {
            $parts[] = $threeDigits($thousand) . ' Thousand';
        }
        if ($hundred) {
            $parts[] = $threeDigits($hundred);
        }

        return strtoupper(trim(implode(' ', $parts))) . ' TAKA ONLY';
    }
}
