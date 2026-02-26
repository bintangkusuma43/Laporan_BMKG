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

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$fullName = trim($input['full_name'] ?? '');
$role = $input['role'] ?? 'petugas';
$petugasId = $input['petugas_id'] ?? null;

if ($username === '' || $password === '' || $fullName === '') {
    json_response([
        'success' => false,
        'message' => 'Nama lengkap, username, dan password wajib diisi.'
    ], 422);
}

if (!preg_match('/^[A-Za-z0-9_.-]{4,50}$/', $username)) {
    json_response([
        'success' => false,
        'message' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan minimal 4 karakter.'
    ], 422);
}

if (strlen($password) < 6) {
    json_response([
        'success' => false,
        'message' => 'Password minimal 6 karakter.'
    ], 422);
}

if (!in_array($role, ['admin', 'petugas'], true)) {
    json_response([
        'success' => false,
        'message' => 'Role tidak valid.'
    ], 422);
}

$pdo = get_pdo();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
$stmt->execute(['username' => $username]);

if ($stmt->fetchColumn() > 0) {
    json_response([
        'success' => false,
        'message' => 'Username sudah digunakan.'
    ], 409);
}

if ($petugasId !== null) {
    $stmtPetugas = $pdo->prepare('SELECT id FROM petugas WHERE id = :id');
    $stmtPetugas->execute(['id' => $petugasId]);
    if (!$stmtPetugas->fetch()) {
        json_response([
            'success' => false,
            'message' => 'Petugas tidak ditemukan.'
        ], 422);
    }
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmtInsert = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role, petugas_id) VALUES (:username, :password_hash, :full_name, :role, :petugas_id)');
$stmtInsert->execute([
    'username' => $username,
    'password_hash' => $hash,
    'full_name' => $fullName,
    'role' => $role,
    'petugas_id' => $petugasId ?: null,
]);

json_response([
    'success' => true,
    'message' => 'Akun berhasil dibuat.'
], 201);
