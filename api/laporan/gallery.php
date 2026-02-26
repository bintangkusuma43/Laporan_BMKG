<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = require_role(['admin', 'petugas']);

$pdo = get_pdo();

$sql = 'SELECT f.id, f.file_name, f.caption, f.uploaded_at, l.kode_laporan, l.jenis, l.user_id FROM dokumentasi_foto f INNER JOIN laporan l ON l.id = f.laporan_id';
$params = [];

if ($user['role'] !== 'admin') {
    $sql .= ' WHERE l.user_id = :user_id';
    $params['user_id'] = $user['id'];
}

$sql .= ' ORDER BY f.uploaded_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();

$baseUrl = rtrim(BASE_URL, '/');

$gallery = array_map(static function (array $row) use ($baseUrl): array {
    return [
        'id' => $row['id'],
        'file_name' => $row['file_name'],
        'caption' => $row['caption'],
        'uploaded_at' => $row['uploaded_at'],
        'kode_laporan' => $row['kode_laporan'],
        'jenis' => $row['jenis'],
        'url' => $baseUrl . '/uploads/' . $row['file_name']
    ];
}, $rows);

json_response([
    'success' => true,
    'data' => $gallery
]);
