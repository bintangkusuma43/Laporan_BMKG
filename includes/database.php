<?php
require_once __DIR__ . '/config.php';

/**
 * Create a PDO connection using configuration constants.
 */
function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed.',
            'error' => $exception->getMessage(),
        ]);
        exit;
    }

    return $pdo;
}

/**
 * Ensure lampiran table exists for backward compatibility.
 *
 * Older installations may not have the laporan_lampiran table yet.
 * This helper attempts a lightweight probe and creates the table if missing.
 * Returns true when the table exists (or was created), false otherwise.
 */
function ensure_laporan_lampiran_table(PDO $pdo): bool
{
    try {
        $pdo->query('SELECT 1 FROM laporan_lampiran LIMIT 1');
        return true;
    } catch (PDOException $exception) {
        // 42S02: Base table or view not found
        if (($exception->getCode() ?? '') !== '42S02') {
            return false;
        }
    }

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS laporan_lampiran (\n"
            . "    id INT AUTO_INCREMENT PRIMARY KEY,\n"
            . "    laporan_id INT NOT NULL,\n"
            . "    lampiran_type ENUM('surat_tugas', 'checklist') NOT NULL,\n"
            . "    file_name VARCHAR(255) NOT NULL,\n"
            . "    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n"
            . "    CONSTRAINT fk_lampiran_laporan FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE ON UPDATE CASCADE,\n"
            . "    UNIQUE KEY uniq_lampiran (laporan_id, lampiran_type)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        return true;
    } catch (PDOException $exception) {
        return false;
    }
}

/**
 * Ensure petugas table has nip column + unique index.
 */
function ensure_petugas_nip_column(PDO $pdo): bool
{
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM petugas LIKE 'nip'");
        $stmt->execute();
        $hasColumn = (bool) $stmt->fetch();
    } catch (PDOException $exception) {
        return false;
    }

    if (!$hasColumn) {
        try {
            $pdo->exec("ALTER TABLE petugas ADD COLUMN nip VARCHAR(30) NULL AFTER nama");
        } catch (PDOException $exception) {
            return false;
        }
    }

    try {
        $stmt = $pdo->prepare("SHOW INDEX FROM petugas WHERE Key_name = 'uniq_petugas_nip'");
        $stmt->execute();
        $hasIndex = (bool) $stmt->fetch();
    } catch (PDOException $exception) {
        return false;
    }

    if (!$hasIndex) {
        try {
            $pdo->exec("ALTER TABLE petugas ADD UNIQUE KEY uniq_petugas_nip (nip)");
        } catch (PDOException $exception) {
            return false;
        }
    }

    return true;
}
