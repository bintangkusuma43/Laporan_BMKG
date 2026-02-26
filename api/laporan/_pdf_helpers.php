<?php

function pdf_require_vendor_autoload(): void
{
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (!is_file($autoload)) {
        json_response([
            'success' => false,
            'message' => 'PDF engine belum terpasang. Jalankan `composer install` di root proyek (laporan_bmkg).'
        ], 500);
    }

    require_once $autoload;
}

function pdf_try_read_file(string $path): ?string
{
    if ($path === '' || !is_file($path)) {
        return null;
    }
    $content = @file_get_contents($path);
    return $content === false ? null : $content;
}

function pdf_image_data_uri_from_path(string $path): ?string
{
    $raw = pdf_try_read_file($path);
    if ($raw === null) {
        return null;
    }

    $mime = function_exists('mime_content_type') ? @mime_content_type($path) : null;
    if (!$mime) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream'
        };
    }

    return 'data:' . $mime . ';base64,' . base64_encode($raw);
}

function pdf_image_data_uri_from_upload(string $fileName): ?string
{
    // fileName stored like "<id>/<file>"
    $relative = ltrim(str_replace('..', '', $fileName), '/\\');
    $path = __DIR__ . '/../../uploads/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    return pdf_image_data_uri_from_path($path);
}

function pdf_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pdf_format_date_id(?string $value, bool $withDay = true): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '-';
    }

    $value = substr($value, 0, 10);
    $dt = null;

    // Try YYYY-MM-DD
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if (!$dt) {
        // Try DD-MM-YYYY
        $dt = DateTime::createFromFormat('d-m-Y', $value);
    }
    if (!$dt) {
        return $value;
    }

    if (class_exists('IntlDateFormatter')) {
        $pattern = $withDay ? 'EEEE, dd MMMM yyyy' : 'dd MMMM yyyy';
        $fmt = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Asia/Jakarta', IntlDateFormatter::GREGORIAN, $pattern);
        $formatted = $fmt->format($dt);
        if (is_string($formatted) && $formatted !== '') {
            // Title-case-ish for Indonesian day names
            return mb_convert_case($formatted, MB_CASE_TITLE, 'UTF-8');
        }
    }

    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $dayName = $days[(int)$dt->format('w')] ?? '';
    $monthName = $months[(int)$dt->format('n')] ?? $dt->format('m');
    $base = $dt->format('d') . ' ' . $monthName . ' ' . $dt->format('Y');

    return $withDay ? ($dayName . ', ' . $base) : $base;
}

function pdf_format_datetime_id(DateTimeInterface $dt): string
{
    if (class_exists('IntlDateFormatter')) {
        $fmt = new IntlDateFormatter('id_ID', IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT, 'Asia/Jakarta');
        $formatted = $fmt->format($dt);
        if (is_string($formatted) && $formatted !== '') {
            return $formatted;
        }
    }

    return $dt->format('Y-m-d H:i');
}

function pdf_pt_from_cm(float $cm): float
{
    // 1 inch = 2.54 cm, 1 inch = 72 pt
    return ($cm / 2.54) * 72.0;
}

function pdf_js_string(string $value): string
{
    // Escape a string to be safely embedded inside single quotes in Dompdf's PHP script.
    return str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
}

function pdf_build_header_script(?string $kopDataUri): string
{
    if (!$kopDataUri) {
        return '';
    }

    $kop = pdf_js_string($kopDataUri);

    // Legacy ReportLab draws the kop image full-width at the very top:
    // img.drawOn(canvas, 0, A4[1] - 3.5cm) with width=A4[0], height=3.5cm.
    // Here we add a small top inset, keep the image proportion, and draw a divider below it.
    $script = <<<'PHP'
if (isset($pdf) && isset($fontMetrics)) {
    $w = method_exists($pdf, 'get_width') ? (float)$pdf->get_width() : 595.0;
    $marginTop = 45.0; // ~1.6cm top inset so header sits comfortably below page edge
    $paddingX = 8.0;   // slimmer inset so kop aligns closer to body width
    $kopH = 99.2126;   // 3.5cm in points (kept proportional)

    $imgW = max(0, $w - (2 * $paddingX));
    $imgX = $paddingX;
    $imgY = $marginTop;

    // Draw kop image
    $pdf->image('__KOP__', $imgX, $imgY, $imgW, $kopH);

    // Divider line removed per request (avoid horizontal rule under kop)
}
PHP;

    return str_replace('__KOP__', $kop, $script);
}

function pdf_build_footer_script(string $dilaporkanPada, string $diunduhPada, bool $includePageNumbers = true): string
{
    // Legacy ReportLab uses A4, with left/right margins 2cm, bottom margin 2cm.
    // Footer line is exactly at y=2cm from bottom, page number baseline at 1.5cm.
    // Audit lines baseline positions from bottom: 0.7cm, 1.1cm, 1.5cm.
    $xLeft = 56; // ~2cm
    $xRight = 595 - 56;

    $line1 = pdf_escape('Dilaporkan pada ' . $dilaporkanPada);
    $line2 = pdf_escape('Diunduh pada ' . $diunduhPada);
    $line3 = pdf_escape('Dibuat otomatis oleh SIMPels');

    $includePageNumbersJs = $includePageNumbers ? 'true' : 'false';

    // Precompute point offsets (do not rely on cm conversion at runtime).
    $pt2cm = 56.6929;
    $pt15cm = 42.5197;
    $pt07cm = 19.8425;
    $pt11cm = 31.1811;
    $pt15cm_text = 42.5197;

    $template = <<<'PHP'
if (!isset($pdf) || !isset($fontMetrics)) {
    return;
}

$h = method_exists($pdf, 'get_height') ? (float)$pdf->get_height() : 842.0;
$yLine = $h - {pt2cm}; // 2cm from bottom
$yPage = $h - {pt15cm}; // 1.5cm from bottom
$yText1 = $h - {pt07cm}; // 0.7cm from bottom
$yText2 = $h - {pt11cm}; // 1.1cm from bottom
$yText3 = $h - {pt15cm_text}; // 1.5cm from bottom

$font = $fontMetrics->getFont('Times-Roman', 'normal');

// Divider line above footer (approx. 2cm from bottom)
$pdf->page_line({xLeft}, $yLine, {xRight}, $yLine, [0.85, 0.85, 0.85], 0.5);

// Audit watermark (bottom-left)
$pdf->page_text({xLeft}, $yText1, "{line1}", $font, 8, [0, 0, 0]);
$pdf->page_text({xLeft}, $yText2, "{line2}", $font, 8, [0, 0, 0]);
$pdf->page_text({xLeft}, $yText3, "{line3}", $font, 8, [0, 0, 0]);

// Page number (bottom-right) - optional
if ({includePageNumbers}) {
    $pageText = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
    $textWidth = $fontMetrics->getTextWidth(str_replace(['{PAGE_NUM}','{PAGE_COUNT}'], ['000','000'], $pageText), $font, 9);
    $xPage = {xRight} - $textWidth;
    $pdf->page_text($xPage, $yPage, $pageText, $font, 9, [0, 0, 0]);
}
PHP;

    return strtr($template, [
        '{pt2cm}' => (string)$pt2cm,
        '{pt15cm}' => (string)$pt15cm,
        '{pt07cm}' => (string)$pt07cm,
        '{pt11cm}' => (string)$pt11cm,
        '{pt15cm_text}' => (string)$pt15cm_text,
        '{xLeft}' => (string)$xLeft,
        '{xRight}' => (string)$xRight,
        '{line1}' => $line1,
        '{line2}' => $line2,
        '{line3}' => $line3,
        '{includePageNumbers}' => $includePageNumbersJs,
    ]);
}
