<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Method not allowed',
    ], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = isset($input['id']) ? (int) $input['id'] : 0;
$currentUserId = $_SESSION['user_id'] ?? null;

if ($userId < 1) {
    json_response([
        'success' => false,
        'message' => 'ID pengguna tidak valid.',
    ], 422);
}

if ($currentUserId !== null && $userId === (int) $currentUserId) {
    json_response([
        'success' => false,
        'message' => 'Anda tidak dapat menghapus akun Anda sendiri.',
    ], 422);
}

$pdo = get_pdo();

$stmtUser = $pdo->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
$stmtUser->execute(['id' => $userId]);
$user = $stmtUser->fetch();

if (!$user) {
    json_response([
        'success' => false,
        'message' => 'Pengguna tidak ditemukan.',
    ], 404);
}

if (($user['role'] ?? '') === 'admin') {
    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($adminCount <= 1) {
        json_response([
            'success' => false,
            'message' => 'Tidak dapat menghapus admin terakhir.',
        ], 409);
    }
}

$deleteStmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$deleteStmt->execute(['id' => $userId]);

json_response([
    'success' => true,
    'message' => 'Akun berhasil dihapus.',
]);
