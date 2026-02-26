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

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    json_response([
        'success' => false,
        'message' => 'ID laporan tidak valid.'
    ], 422);
}

$stasiunId = $_POST['stasiun_id'] ?? null;
$tanggalLaporan = $_POST['tanggal_laporan'] ?? '';
$nomorSurat = $_POST['nomor_surat'] ?? null;
$detailJsonRaw = $_POST['detail_json'] ?? '';
$statusInput = $_POST['status'] ?? 'draft';

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

function column_exists(PDO $pdo, string $table, string $column): bool {
    static $cache = [];
    $key = $table . '::' . $column;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = $pdo->prepare('SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1');
    $stmt->execute(['t' => $table, 'c' => $column]);
    $cache[$key] = (bool)$stmt->fetchColumn();
    return $cache[$key];
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

$pdo = get_pdo();

// Backward-compat: auto-create lampiran table if missing.
$hasLampiranTable = ensure_laporan_lampiran_table($pdo);

$stmt = $pdo->prepare('SELECT id, jenis, user_id, status FROM laporan WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$laporan = $stmt->fetch();

if (!$laporan) {
    json_response([
        'success' => false,
        'message' => 'Laporan tidak ditemukan.'
    ], 404);
}

if ($user['role'] !== 'admin' && (int)$laporan['user_id'] !== (int)$user['id']) {
    json_response([
        'success' => false,
        'message' => 'Anda tidak memiliki akses ke laporan ini.'
    ], 403);
}

// Laporan yang sudah diajukan/selesai tidak boleh diubah lewat endpoint ini.
if ((string)$laporan['status'] !== 'draft') {
    json_response([
        'success' => false,
        'message' => 'Laporan sudah bukan draft dan tidak bisa diubah.'
    ], 409);
}

$jenis = (string)$laporan['jenis'];

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

$pdo->beginTransaction();

try {
    $submittedAt = null;
    if ($status === 'diajukan') {
        $submittedAt = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
    }

    $hasSubmittedAt = column_exists($pdo, 'laporan', 'submitted_at');
    $sqlUpdate = 'UPDATE laporan SET stasiun_id = :stasiun_id, nomor_surat = :nomor_surat, tanggal_laporan = :tanggal_laporan, status = :status' . ($hasSubmittedAt ? ', submitted_at = :submitted_at' : '') . ' WHERE id = :id';
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $paramsUpdate = [
        'stasiun_id' => $stasiunId ?: null,
        'nomor_surat' => $nomorSurat,
        'tanggal_laporan' => $tanggalLaporan,
        'status' => $status,
        'id' => $id
    ];
    if ($hasSubmittedAt) {
        $paramsUpdate['submitted_at'] = $submittedAt;
    }
    $stmtUpdate->execute($paramsUpdate);

    $stmtDetail = $pdo->prepare('SELECT id FROM laporan_detail WHERE laporan_id = :id LIMIT 1');
    $stmtDetail->execute(['id' => $id]);
    $detailRow = $stmtDetail->fetch();

    if ($detailRow) {
        $stmtUpdateDetail = $pdo->prepare('UPDATE laporan_detail SET detail_type = :detail_type, detail_json = :detail_json WHERE laporan_id = :id');
        $stmtUpdateDetail->execute([
            'detail_type' => $jenis,
            'detail_json' => json_encode($detailArray, JSON_UNESCAPED_UNICODE),
            'id' => $id
        ]);
    } else {
        $stmtInsertDetail = $pdo->prepare('INSERT INTO laporan_detail (laporan_id, detail_type, detail_json) VALUES (:id, :detail_type, :detail_json)');
        $stmtInsertDetail->execute([
            'id' => $id,
            'detail_type' => $jenis,
            'detail_json' => json_encode($detailArray, JSON_UNESCAPED_UNICODE)
        ]);
    }

    $uploadDir = __DIR__ . '/../../uploads/' . $id;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Dokumentasi foto: hanya JPG/PNG. Jika user upload baru, tambahkan tanpa menghapus foto lama.
    $hasNewFoto = isset($_FILES['foto']) && is_array($_FILES['foto']['name'] ?? null) && count(array_filter($_FILES['foto']['name'])) > 0;
    if ($hasNewFoto) {
        $savedFiles = handle_uploads('foto', $uploadDir, ['image/jpeg', 'image/png'], ['jpg', 'jpeg', 'png']);
        if ($savedFiles) {
            $stmtFoto = $pdo->prepare('INSERT INTO dokumentasi_foto (laporan_id, file_name, caption) VALUES (:laporan_id, :file_name, :caption)');
            foreach ($savedFiles as $index => $fileName) {
                $caption = $captions[$index] ?? null;
                $stmtFoto->execute([
                    'laporan_id' => $id,
                    'file_name' => $id . '/' . $fileName,
                    'caption' => $caption
                ]);
            }
        }
    }

    // Lampiran PDF: replace per type jika user upload ulang.
    $suratPdf = handle_single_upload('upload_surat', $uploadDir, ['application/pdf'], ['pdf']);
    $checklistPdf = handle_single_upload('upload_checklist', $uploadDir, ['application/pdf'], ['pdf']);

    $existingMap = [];
    if ($hasLampiranTable) {
        $stmtLampiranSelect = $pdo->prepare('SELECT id, lampiran_type, file_name FROM laporan_lampiran WHERE laporan_id = :id');
        $stmtLampiranSelect->execute(['id' => $id]);
        $existingLampiran = $stmtLampiranSelect->fetchAll();

        foreach ($existingLampiran as $row) {
            $existingMap[(string)$row['lampiran_type']] = (string)$row['file_name'];
        }
    }

    if ($status !== 'draft' && in_array($jenis, ['wrs_ng', 'seismograph'], true)) {
        $hasSurat = $suratPdf !== null || ($hasLampiranTable && isset($existingMap['surat_tugas']));
        $hasChecklist = $checklistPdf !== null || ($hasLampiranTable && isset($existingMap['checklist']));
        if (!$hasSurat) {
            throw new RuntimeException('Upload Surat Tugas (PDF) wajib.');
        }
        if (!$hasChecklist) {
            throw new RuntimeException('Upload Checklist (PDF) wajib.');
        }
    }

    if ($hasLampiranTable) {
        $stmtUpsertLampiran = $pdo->prepare('INSERT INTO laporan_lampiran (laporan_id, lampiran_type, file_name) VALUES (:laporan_id, :lampiran_type, :file_name)
            ON DUPLICATE KEY UPDATE file_name = VALUES(file_name), uploaded_at = CURRENT_TIMESTAMP');

        if ($suratPdf !== null) {
            if (isset($existingMap['surat_tugas'])) {
                $base = basename($existingMap['surat_tugas']);
                $path = $uploadDir . '/' . $base;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $stmtUpsertLampiran->execute([
                'laporan_id' => $id,
                'lampiran_type' => 'surat_tugas',
                'file_name' => $id . '/' . $suratPdf,
            ]);
        }

        if ($checklistPdf !== null) {
            if (isset($existingMap['checklist'])) {
                $base = basename($existingMap['checklist']);
                $path = $uploadDir . '/' . $base;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $stmtUpsertLampiran->execute([
                'laporan_id' => $id,
                'lampiran_type' => 'checklist',
                'file_name' => $id . '/' . $checklistPdf,
            ]);
        }
    }

    $pdo->commit();

    json_response([
        'success' => true,
        'message' => $status === 'draft' ? 'Draf berhasil diperbarui.' : 'Laporan berhasil diajukan.',
        'data' => [
            'laporan_id' => $id
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
        'message' => 'Terjadi kesalahan saat memperbarui laporan.',
        'error' => $exception->getMessage()
    ], 500);
}
