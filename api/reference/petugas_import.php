<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

if (!class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
    json_response([
        'success' => false,
        'message' => 'PhpSpreadsheet belum terpasang. Jalankan composer require phpoffice/phpspreadsheet.'
    ], 500);
}

$file = $_FILES['file'] ?? null;
if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    json_response([
        'success' => false,
        'message' => 'File XLSX wajib diunggah.'
    ], 422);
}

$ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
if ($ext !== 'xlsx') {
    json_response([
        'success' => false,
        'message' => 'Format file harus XLSX.'
    ], 422);
}

$pdo = get_pdo();
if (!ensure_petugas_nip_column($pdo)) {
    json_response([
        'success' => false,
        'message' => 'Gagal menyiapkan kolom NIP pada tabel petugas.'
    ], 500);
}

try {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
} catch (Throwable $error) {
    json_response([
        'success' => false,
        'message' => 'Gagal membaca file XLSX: ' . $error->getMessage()
    ], 422);
}

$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);
if (!$rows || count($rows) < 2) {
    json_response([
        'success' => false,
        'message' => 'File XLSX kosong atau tidak memiliki data.'
    ], 422);
}

$header = $rows[1] ?? [];
$namaCol = null;
$nipCol = null;

foreach ($header as $col => $value) {
    $label = strtolower(trim((string) $value));
    if ($label === '') {
        continue;
    }
    if ($namaCol === null && preg_match('/\bnama\b/', $label)) {
        $namaCol = $col;
        continue;
    }
    if ($nipCol === null && preg_match('/\bnip\b/', $label)) {
        $nipCol = $col;
    }
}

if ($namaCol === null) {
    $namaCol = 'A';
}
if ($nipCol === null) {
    $nipCol = 'B';
}

$inserted = 0;
$skippedNoNama = 0;
$skippedNoNip = 0;

$stmt = $pdo->prepare(
    'INSERT INTO petugas (nama, nip) VALUES (:nama, :nip) '
    . 'ON DUPLICATE KEY UPDATE nama = VALUES(nama), updated_at = CURRENT_TIMESTAMP'
);

$pdo->beginTransaction();
try {
    foreach ($rows as $index => $row) {
        if ($index === 1) {
            continue;
        }
        $nama = trim((string) ($row[$namaCol] ?? ''));
        $nip = trim((string) ($row[$nipCol] ?? ''));

        if ($nama === '') {
            $skippedNoNama++;
            continue;
        }
        if ($nip === '') {
            $skippedNoNip++;
            continue;
        }

        $stmt->execute([
            'nama' => $nama,
            'nip' => $nip,
        ]);
        $inserted++;
    }
    $pdo->commit();
} catch (Throwable $error) {
    $pdo->rollBack();
    json_response([
        'success' => false,
        'message' => 'Gagal menyimpan data: ' . $error->getMessage()
    ], 500);
}

json_response([
    'success' => true,
    'message' => 'Import master pegawai selesai.',
    'data' => [
        'total' => count($rows) - 1,
        'diimpor' => $inserted,
        'skip_tanpa_nama' => $skippedNoNama,
        'skip_tanpa_nip' => $skippedNoNip,
    ]
]);
