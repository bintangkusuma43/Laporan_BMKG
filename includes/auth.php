<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Return the logged in user record or null.
 */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;

    if ($cachedUser !== null) {
        return $cachedUser;
    }

    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id, username, full_name, role, petugas_id FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        logout();
        return null;
    }

    $cachedUser = $user;

    return $cachedUser;
}

/**
 * Ensure user is authenticated.
 */
function require_login(): array
{
    $user = current_user();
    if (!$user) {
        json_response([
            'success' => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    return $user;
}

/**
 * Ensure user has admin role.
 */
function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        json_response([
            'success' => false,
            'message' => 'Forbidden',
        ], 403);
    }

    return $user;
}

/**
 * Ensure user has one of the allowed roles.
 */
function require_role(array $allowedRoles): array
{
    $user = require_login();

    if (!in_array($user['role'] ?? null, $allowedRoles, true)) {
        json_response([
            'success' => false,
            'message' => 'Forbidden',
        ], 403);
    }

    return $user;
}

/**
 * Destroy the current session.
 */
function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
