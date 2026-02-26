<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = require_role(['admin', 'petugas']);

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    json_response([
        'success' => false,
        'message' => 'ID laporan tidak ditemukan.'
    ], 422);
}

$pdo = get_pdo();

// Backward-compat: auto-create lampiran table if the schema is older.
ensure_laporan_lampiran_table($pdo);

$sql = 'SELECT l.id, l.kode_laporan, l.jenis, l.user_id, l.petugas_id, l.stasiun_id, l.nomor_surat, l.tanggal_laporan, l.status, l.catatan_admin, l.created_at, l.updated_at, u.full_name AS nama_pelapor, u.role AS role_pelapor, p.nama AS nama_petugas, s.nama AS nama_stasiun, s.kode AS kode_stasiun, s.lokasi, s.tipe FROM laporan l INNER JOIN users u ON u.id = l.user_id LEFT JOIN petugas p ON p.id = l.petugas_id LEFT JOIN stasiun s ON s.id = l.stasiun_id WHERE l.id = :id LIMIT 1';
$stmt = $pdo->prepare($sql);
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

// Draft belum terkirim ke admin: admin tidak boleh melihat draft milik user lain.
if ($user['role'] === 'admin' && (int)$laporan['user_id'] !== (int)$user['id'] && (string)$laporan['status'] === 'draft') {
    json_response([
        'success' => false,
        'message' => 'Laporan draft belum diajukan.'
    ], 403);
}

$stmtDetail = $pdo->prepare('SELECT detail_type, detail_json FROM laporan_detail WHERE laporan_id = :id LIMIT 1');
$stmtDetail->execute(['id' => $id]);
$detail = $stmtDetail->fetch();

if ($detail && isset($detail['detail_json'])) {
    $decoded = json_decode($detail['detail_json'], true);
    $detail['detail_json'] = $decoded ?? [];
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
    // Backward-compat: older DB schemas may not have laporan_lampiran yet.
    if (($exception->getCode() ?? '') !== '42S02') {
        throw $exception;
    }
}

json_response([
    'success' => true,
    'data' => [
        'laporan' => $laporan,
        'detail' => $detail,
        'dokumentasi' => $fotos,
        'lampiran' => $lampiran
    ]
]);
