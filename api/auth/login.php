<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'success' => false,
        'message' => 'Method not allowed'
    ], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if ($username === '' || $password === '') {
    json_response([
        'success' => false,
        'message' => 'Username dan password wajib diisi.'
    ], 422);
}

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT id, username, password_hash, full_name, role, petugas_id FROM users WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response([
        'success' => false,
        'message' => 'Kredensial tidak valid.'
    ], 401);
}

// Prevent session fixation.
session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

json_response([
    'success' => true,
    'data' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
        'petugas_id' => $user['petugas_id'],
    ]
]);
