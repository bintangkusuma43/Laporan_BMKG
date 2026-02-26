<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

require_admin();

$pdo = get_pdo();

$sql = 'SELECT u.id, u.username, u.full_name, u.role, u.petugas_id, u.created_at, p.nama AS nama_petugas, p.upt AS upt_petugas FROM users u LEFT JOIN petugas p ON p.id = u.petugas_id ORDER BY u.created_at DESC';
$stmt = $pdo->query($sql);

json_response([
    'success' => true,
    'data' => $stmt->fetchAll()
]);
