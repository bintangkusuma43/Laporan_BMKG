<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/_pdf_helpers.php';

$user = require_role(['admin', 'petugas']);

$idRaw = trim((string)($_GET['id'] ?? ''));
$kodeRaw = trim((string)($_GET['kode'] ?? ($_GET['kode_laporan'] ?? '')));
$id = (int)$idRaw;
if ($id <= 0 && $kodeRaw === '') {
    json_response([
        'success' => false,
        'message' => 'ID laporan tidak valid. Gunakan parameter `?id=123` atau `?kode=KODE_LAPORAN`. '
            . 'Contoh: /api/laporan/pdf.php?id=1',
    ], 422);
}

$pdf_require_vendor_error = null;
pdf_require_vendor_autoload();

$imagesEnabled = extension_loaded('gd');

use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = get_pdo();

$params = [];
$where = '';
if ($id > 0) {
    $where = 'l.id = :id';
    $params['id'] = $id;
} else {
    $where = 'l.kode_laporan = :kode';
    $params['kode'] = $kodeRaw;
}

$sql = 'SELECT l.id, l.kode_laporan, l.jenis, l.user_id, l.petugas_id, l.stasiun_id, l.nomor_surat, l.tanggal_laporan, l.status, l.catatan_admin, l.created_at, l.updated_at, u.full_name AS nama_pelapor, u.role AS role_pelapor, p.nama AS nama_petugas, s.nama AS nama_stasiun, s.kode AS kode_stasiun, s.lokasi, s.tipe FROM laporan l INNER JOIN users u ON u.id = l.user_id LEFT JOIN petugas p ON p.id = l.petugas_id LEFT JOIN stasiun s ON s.id = l.stasiun_id WHERE ' . $where . ' LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetch();

if (!$laporan) {
    json_response(['success' => false, 'message' => 'Laporan tidak ditemukan.'], 404);
}

// If PDF was requested by kode, normalize $id for subsequent detail queries.
$id = (int)($laporan['id'] ?? $id);

if ($user['role'] !== 'admin' && (int)$laporan['user_id'] !== (int)$user['id']) {
    json_response(['success' => false, 'message' => 'Anda tidak memiliki akses ke laporan ini.'], 403);
}

$stmtDetail = $pdo->prepare('SELECT detail_type, detail_json FROM laporan_detail WHERE laporan_id = :id LIMIT 1');
$stmtDetail->execute(['id' => $id]);
$detailRow = $stmtDetail->fetch();
$detailJson = [];
if ($detailRow && isset($detailRow['detail_json'])) {
    $decoded = json_decode($detailRow['detail_json'], true);
    $detailJson = is_array($decoded) ? $decoded : [];
}

$stmtFoto = $pdo->prepare('SELECT id, file_name, caption, uploaded_at FROM dokumentasi_foto WHERE laporan_id = :id ORDER BY uploaded_at ASC');
$stmtFoto->execute(['id' => $id]);
$fotos = $stmtFoto->fetchAll();

$lampiran = [];
try {
    $stmtLampiran = $pdo->prepare('SELECT id, lampiran_type, file_name, uploaded_at FROM laporan_lampiran WHERE laporan_id = :id ORDER BY uploaded_at ASC');
    $stmtLampiran->execute(['id' => $id]);
    $lampiran = $stmtLampiran->fetchAll();
} catch (PDOException $exception) {
    if (($exception->getCode() ?? '') !== '42S02') {
        throw $exception;
    }
}

$payload = [
    'laporan' => $laporan,
    'detail' => $detailJson,
    'dokumentasi' => $fotos,
    'lampiran' => $lampiran,
];

$kopPathPrimary = __DIR__ . '/../../assets/kop/kop_bmkg.jpg';
$kopDataUri = pdf_image_data_uri_from_path($kopPathPrimary);
if (!$kopDataUri) {
    json_response(['success' => false, 'message' => 'Gagal memuat kop surat untuk PDF.'], 500);
}

$jenis = (string)($laporan['jenis'] ?? '');
$html = '';
$filename = 'Laporan-' . ($laporan['kode_laporan'] ?? $id) . '.pdf';

if ($jenis === 'accelerograph') {
    require_once __DIR__ . '/pdf_templates/preventif.php';
    $html = pdf_template_preventif($payload, $kopDataUri, $imagesEnabled);
    $filename = 'Laporan-Preventif-' . ($laporan['kode_laporan'] ?? $id) . '.pdf';
} elseif ($jenis === 'wrs_ng') {
    require_once __DIR__ . '/pdf_templates/wrs_ng.php';
    $html = pdf_template_wrs_ng($payload, $kopDataUri, $imagesEnabled);
    $filename = 'Laporan-WRS-' . ($laporan['kode_laporan'] ?? $id) . '.pdf';
} elseif ($jenis === 'seismograph') {
    require_once __DIR__ . '/pdf_templates/seismograph.php';
    $html = pdf_template_seismograph($payload, $kopDataUri, $imagesEnabled);
    $filename = 'Laporan-Seismograph-' . ($laporan['kode_laporan'] ?? $id) . '.pdf';
} else {
    json_response(['success' => false, 'message' => 'Jenis laporan tidak didukung untuk PDF.'], 422);
}

$dilaporkanPada = pdf_format_datetime_id(new DateTime((string)($laporan['created_at'] ?? 'now'), new DateTimeZone('Asia/Jakarta')));
$diunduhPada = pdf_format_datetime_id(new DateTime('now', new DateTimeZone('Asia/Jakarta')));

// Audit footer (watermark). For WRS NG, match the legacy Python output: use report date and omit page numbers.
if ($jenis === 'wrs_ng') {
    $wrsTanggal = (string)($laporan['tanggal_laporan'] ?? '');
    $wrsDilaporkanPada = pdf_format_date_id($wrsTanggal, true);
    $footerScript = pdf_build_footer_script($wrsDilaporkanPada, $diunduhPada, false);
} else {
    $footerScript = pdf_build_footer_script($dilaporkanPada, $diunduhPada, true);
}

$headerScript = '';
$pdfScript = trim($headerScript . "\n\n" . $footerScript);
if ($pdfScript !== '') {
    $html = str_replace('</body>', '<script type="text/php">' . "\n" . $pdfScript . "\n" . '</script></body>', $html);
}

$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isPhpEnabled', true);
$options->set('defaultFont', 'Times-Roman');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// (Opsional) merge lampiran (Surat Tugas + Checklist) untuk WRS NG/Seismograph/Accelerograph jika diminta melalui query merge=1.
// Default tidak digabung; admin dapat unduh lampiran terpisah via halaman riwayat.
if ((string)($_GET['merge'] ?? '') === '1'
    && in_array($jenis, ['wrs_ng', 'seismograph', 'accelerograph'], true)
    && class_exists('setasign\\Fpdi\\Fpdi')) {
    try {
        $basePdf = $dompdf->output();
        $tempBase = tempnam(sys_get_temp_dir(), 'simpels_wrs_base_');
        if ($tempBase === false) {
            throw new RuntimeException('Gagal membuat file sementara untuk PDF.');
        }
        $tempBasePdf = $tempBase . '.pdf';
        @unlink($tempBase);
        file_put_contents($tempBasePdf, $basePdf);

        $resolveUploadPdf = static function (string $fileName): ?string {
            $relative = ltrim(str_replace('..', '', $fileName), '/\\');
            if ($relative === '') {
                return null;
            }
            $path = __DIR__ . '/../../uploads/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
            return is_file($path) ? $path : null;
        };

        $appendPaths = [];
        foreach ($lampiran as $row) {
            if (!is_array($row)) continue;
            $type = (string)($row['lampiran_type'] ?? '');
            $fileName = (string)($row['file_name'] ?? '');
            if (!in_array($type, ['surat_tugas', 'checklist'], true)) {
                continue;
            }
            $resolved = $resolveUploadPdf($fileName);
            if ($resolved) {
                $appendPaths[] = $resolved;
            }
        }

        if ($appendPaths) {
            // FPDI merge
            $fpdi = new setasign\Fpdi\Fpdi();
            $fpdi->SetAutoPageBreak(false);

            $sources = array_merge([$tempBasePdf], $appendPaths);
            foreach ($sources as $srcPath) {
                $pageCount = $fpdi->setSourceFile($srcPath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $fpdi->importPage($pageNo);
                    $size = $fpdi->getTemplateSize($tpl);
                    $orientation = $size['orientation'] ?? 'P';
                    $fpdi->AddPage($orientation, [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl);
                }
            }

            $mergedPdf = $fpdi->Output('S');

            if (ob_get_length()) {
                @ob_end_clean();
            }

            // Prevent browser/PDF viewer caching (Chrome can aggressively cache inline PDFs).
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . str_replace('"', '', $filename) . '"');
            header('Content-Length: ' . strlen($mergedPdf));
            echo $mergedPdf;
            @unlink($tempBasePdf);
            exit;
        }

        @unlink($tempBasePdf);
    } catch (Throwable $mergeError) {
        // Fall back to non-merged PDF.
    }
}

if (ob_get_length()) {
    @ob_end_clean();
}

// Prevent browser/PDF viewer caching.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$dompdf->stream($filename, ['Attachment' => false]);
exit;
