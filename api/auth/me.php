<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = current_user();

if (!$user) {
    json_response([
        'success' => false,
        'authenticated' => false,
        'message' => 'NOT_AUTHENTICATED'
    ], 200);
}

$pdo = get_pdo();

$petugas = null;
if (!empty($user['petugas_id'])) {
    $stmt = $pdo->prepare('SELECT id, nama, upt, jabatan, kontak, email FROM petugas WHERE id = :id');
    $stmt->execute(['id' => $user['petugas_id']]);
    $petugas = $stmt->fetch();
}

json_response([
    'success' => true,
    'authenticated' => true,
    'data' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'] ?? $user['username'],
        'role' => $user['role'],
        'petugas' => $petugas
    ]
]);
