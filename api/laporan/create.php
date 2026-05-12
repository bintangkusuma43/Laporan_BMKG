<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/upload.php';

$user = require_role(['admin', 'petugas']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

$jenisInput = $_POST['jenis'] ?? '';
// Normalisasi: intensitymeter disimpan sebagai accelerograph (schema ENUM tidak punya intensitymeter)
$jenis = $jenisInput === 'intensitymeter' ? 'accelerograph' : $jenisInput;
$stasiunId = $_POST['stasiun_id'] ?? null;
$tanggalLaporan = $_POST['tanggal_laporan'] ?? '';
$nomorSurat = $_POST['nomor_surat'] ?? null;
$detailJsonRaw = $_POST['detail_json'] ?? '';
$statusInput = $_POST['status'] ?? 'submitted';

$statusMap = [
    'draft' => 'draft',
    'submitted' => 'diajukan'
];

if (!isset($statusMap[$statusInput])) {
    json_response([
        'success' => false,
        'message' => 'Status laporan tidak valid.'
    ], 422);
}

$status = $statusMap[$statusInput];

if (!in_array($jenis, ['wrs_ng', 'accelerograph', 'seismograph'], true)) {
    json_response([
        'success' => false,
        'message' => 'Jenis laporan tidak valid.'
    ], 422);
}

if ($status === 'draft' && $tanggalLaporan === '') {
    $tanggalLaporan = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
}

if ($tanggalLaporan === '') {
    json_response([
        'success' => false,
        'message' => 'Tanggal laporan wajib diisi.'
    ], 422);
}

$detailArray = json_decode($detailJsonRaw, true);
if (!is_array($detailArray)) {
    json_response([
        'success' => false,
        'message' => 'Format detail laporan tidak valid.'
    ], 422);
}

if ($status !== 'draft') {
    if ($nomorSurat === null || trim((string)$nomorSurat) === '') {
        json_response(['success' => false, 'message' => 'Nomor surat wajib diisi.'], 422);
    }

    if ($jenis === 'wrs_ng') {
        $kegiatan = trim((string)($detailArray['kegiatan'] ?? ''));
        $tempat = trim((string)($detailArray['tempat'] ?? ''));
        if ($kegiatan === '') {
            json_response(['success' => false, 'message' => 'Kegiatan wajib diisi.'], 422);
        }
        if ($tempat === '') {
            json_response(['success' => false, 'message' => 'Tempat wajib diisi.'], 422);
        }
    } elseif ($jenis === 'accelerograph') {
        $kodeSite = trim((string)($detailArray['kode_site'] ?? ''));
        $namaSite = trim((string)($detailArray['nama_site'] ?? ''));
        $statusAlat = trim((string)($detailArray['status_alat'] ?? ''));
        if ($kodeSite === '' || $namaSite === '') {
            json_response(['success' => false, 'message' => 'Informasi site wajib diisi.'], 422);
        }
        if ($statusAlat === '') {
            json_response(['success' => false, 'message' => 'Status alat wajib dipilih.'], 422);
        }
    } elseif ($jenis === 'seismograph') {
        $tugas = is_array($detailArray['tugas'] ?? null) ? $detailArray['tugas'] : [];
        $nomorTugas = trim((string)($tugas['nomor_surat_tugas'] ?? ''));
        $tanggalPemeliharaan = trim((string)($tugas['tanggal_pemeliharaan'] ?? ''));
        if ($nomorTugas === '') {
            json_response(['success' => false, 'message' => 'Nomor surat tugas wajib diisi.'], 422);
        }
        if ($tanggalPemeliharaan === '') {
            json_response(['success' => false, 'message' => 'Tanggal pemeliharaan wajib diisi.'], 422);
        }
    }
}

$captions = $_POST['foto_caption'] ?? [];
if (!is_array($captions)) {
    $captions = [];
}

$pdo = get_pdo();

// Backward-compat: auto-create lampiran table if missing.
$hasLampiranTable = ensure_laporan_lampiran_table($pdo);
$pdo->beginTransaction();

try {
    $kodeLaporan = generate_report_code($pdo, $jenis);

    $stmt = $pdo->prepare('INSERT INTO laporan (kode_laporan, jenis, user_id, petugas_id, stasiun_id, nomor_surat, tanggal_laporan, status) VALUES (:kode, :jenis, :user_id, :petugas_id, :stasiun_id, :nomor_surat, :tanggal, :status)');
    $stmt->execute([
        'kode' => $kodeLaporan,
        'jenis' => $jenis,
        'user_id' => $user['id'],
        'petugas_id' => $user['petugas_id'] ?? null,
        'stasiun_id' => $stasiunId ?: null,
        'nomor_surat' => $nomorSurat,
        'tanggal' => $tanggalLaporan,
        'status' => $status
    ]);

    $laporanId = (int)$pdo->lastInsertId();

    $stmtDetail = $pdo->prepare('INSERT INTO laporan_detail (laporan_id, detail_type, detail_json) VALUES (:laporan_id, :detail_type, :detail_json)');
    $stmtDetail->execute([
        'laporan_id' => $laporanId,
        'detail_type' => $jenis,
        'detail_json' => json_encode($detailArray, JSON_UNESCAPED_UNICODE)
    ]);

    $uploadDir = __DIR__ . '/../../uploads/' . $laporanId;

    // Dokumentasi foto: hanya JPG/PNG
    $savedFiles = handle_uploads('foto', $uploadDir, ['image/jpeg', 'image/png'], ['jpg', 'jpeg', 'png']);

    if ($savedFiles) {
        $stmtFoto = $pdo->prepare('INSERT INTO dokumentasi_foto (laporan_id, file_name, caption) VALUES (:laporan_id, :file_name, :caption)');
        foreach ($savedFiles as $index => $fileName) {
            $caption = $captions[$index] ?? null;
            $stmtFoto->execute([
                'laporan_id' => $laporanId,
                'file_name' => $laporanId . '/' . $fileName,
                'caption' => $caption
            ]);
        }
    }

    // Lampiran PDF (Surat Tugas & Checklist)
    $lampiranDir = $uploadDir;
    $suratPdf = handle_single_upload('upload_surat', $lampiranDir, ['application/pdf'], ['pdf']);
    $checklistPdf = handle_single_upload('upload_checklist', $lampiranDir, ['application/pdf'], ['pdf']);

    if ($status !== 'draft' && in_array($jenis, ['wrs_ng', 'seismograph'], true)) {
        if ($suratPdf === null) {
            throw new RuntimeException('Upload Surat Tugas (PDF) wajib.');
        }
        if ($checklistPdf === null) {
            throw new RuntimeException('Upload Checklist (PDF) wajib.');
        }
    }

    if ($hasLampiranTable && ($suratPdf !== null || $checklistPdf !== null)) {
        $stmtLampiran = $pdo->prepare('INSERT INTO laporan_lampiran (laporan_id, lampiran_type, file_name) VALUES (:laporan_id, :lampiran_type, :file_name)');
        if ($suratPdf !== null) {
            $stmtLampiran->execute([
                'laporan_id' => $laporanId,
                'lampiran_type' => 'surat_tugas',
                'file_name' => $laporanId . '/' . $suratPdf,
            ]);
        }
        if ($checklistPdf !== null) {
            $stmtLampiran->execute([
                'laporan_id' => $laporanId,
                'lampiran_type' => 'checklist',
                'file_name' => $laporanId . '/' . $checklistPdf,
            ]);
        }
    }

    $pdo->commit();

    json_response([
        'success' => true,
        'message' => $status === 'draft' ? 'Draf berhasil disimpan.' : 'Laporan berhasil disimpan.',
        'data' => [
            'laporan_id' => $laporanId,
            'kode_laporan' => $kodeLaporan
        ]
    ]);
} catch (RuntimeException $exception) {
    $pdo->rollBack();
    json_response([
        'success' => false,
        'message' => $exception->getMessage(),
    ], 422);
} catch (Throwable $exception) {
    $pdo->rollBack();
    json_response([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpan laporan.',
        'error' => $exception->getMessage()
    ], 500);
}

function generate_report_code(PDO $pdo, string $jenis): string
{
    $prefixMap = [
        'wrs_ng' => 'WRS',
        'accelerograph' => 'ACC',
        'seismograph' => 'SEIS'
    ];

    $prefix = $prefixMap[$jenis] ?? 'LPR';
    $timezone = new DateTimeZone('Asia/Jakarta');
    $now = new DateTime('now', $timezone);
    $datePart = $now->format('Ymd');
    $prefixWithDate = $prefix . '-' . $datePart;

    $stmt = $pdo->prepare('SELECT kode_laporan FROM laporan WHERE kode_laporan LIKE :pattern ORDER BY kode_laporan DESC LIMIT 1');
    $stmt->execute(['pattern' => $prefixWithDate . '-%']);
    $lastCode = $stmt->fetchColumn();

    $sequence = 1;
    if ($lastCode) {
        $segments = explode('-', $lastCode);
        $lastSegment = end($segments);
        if ($lastSegment !== false && ctype_digit($lastSegment)) {
            $sequence = (int)$lastSegment + 1;
        }
    }

    $sequencePart = str_pad((string)$sequence, 3, '0', STR_PAD_LEFT);
    return $prefixWithDate . '-' . $sequencePart;
}
