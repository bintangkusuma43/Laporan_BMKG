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

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    json_response([
        'success' => false,
        'message' => 'Body JSON tidak valid.'
    ], 400);
}

$id = (int)($input['id'] ?? 0);
$status = $input['status'] ?? '';
$catatan = trim($input['catatan'] ?? '');

if ($id <= 0) {
    json_response([
        'success' => false,
        'message' => 'ID laporan wajib diisi.'
    ], 422);
}

if (!in_array($status, ['diajukan', 'diproses', 'selesai', 'ditolak'], true)) {
    json_response([
        'success' => false,
        'message' => 'Status laporan tidak valid.'
    ], 422);
}

$pdo = get_pdo();

$stmtCheck = $pdo->prepare('SELECT id, status FROM laporan WHERE id = :id LIMIT 1');
$stmtCheck->execute(['id' => $id]);
$existing = $stmtCheck->fetch();

if (!$existing) {
    json_response([
        'success' => false,
        'message' => 'Laporan tidak ditemukan.'
    ], 404);
}

$stmt = $pdo->prepare('UPDATE laporan SET status = :status, catatan_admin = :catatan WHERE id = :id');
$stmt->execute([
    'status' => $status,
    'catatan' => $catatan !== '' ? $catatan : null,
    'id' => $id
]);

json_response([
    'success' => true,
    'message' => 'Status laporan berhasil diperbarui.'
]);
