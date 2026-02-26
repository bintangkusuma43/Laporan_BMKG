<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

require_admin();

$pdo = get_pdo();

$query = 'SELECT id, nama, upt, jabatan, kontak, email FROM petugas ORDER BY nama ASC';
$stmt = $pdo->query($query);

json_response([
    'success' => true,
    'data' => $stmt->fetchAll()
]);
