<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/_master_stasiun_data.php';

require_role(['admin', 'petugas']);

$pdo = get_pdo();

$seedError = null;
try {
    seed_master_stasiun($pdo);
} catch (Throwable $exception) {
    $seedError = $exception->getMessage();
}

$query = 'SELECT id, kode, nama, lokasi, tipe FROM stasiun ORDER BY nama ASC';

try {
    $stmt = $pdo->query($query);
    $rows = $stmt->fetchAll();
} catch (Throwable $exception) {
    $rows = [];
    $seedError = $seedError ?: $exception->getMessage();
}

if (!$rows) {
    $fallback = build_fallback_from_master();
    json_response([
        'success' => true,
        'data' => $fallback,
        'source' => 'fallback_master',
        'warning' => $seedError ?: 'Stasiun master belum ada di database; menggunakan data statis.'
    ]);
}

json_response([
    'success' => true,
    'data' => $rows,
    'source' => 'database'
]);

function seed_master_stasiun(PDO $pdo): void
{
    global $MASTER_STASIUN_LENGKAP, $MASTER_STASIUN_BASIC;

    $all = array_merge($MASTER_STASIUN_LENGKAP, $MASTER_STASIUN_BASIC);
    if (!$all) {
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO stasiun (kode, nama, lokasi, tipe) '
        . 'VALUES (:kode, :nama, :lokasi, :tipe) '
        . 'ON DUPLICATE KEY UPDATE nama = VALUES(nama), lokasi = VALUES(lokasi), tipe = VALUES(tipe)'
    );

    foreach ($all as $item) {
        $lokasi = trim((string)($item['alamat'] ?? $item['upt'] ?? $item['nama'] ?? $item['kode']));
        if ($lokasi === '') {
            $lokasi = 'Lokasi tidak tersedia';
        }

        $stmt->execute([
            'kode' => $item['kode'],
            'nama' => $item['nama'],
            'lokasi' => $lokasi,
            'tipe' => $item['tipe'],
        ]);
    }
}

function build_fallback_from_master(): array
{
    global $MASTER_STASIUN_LENGKAP, $MASTER_STASIUN_BASIC;
    $merged = array_merge($MASTER_STASIUN_LENGKAP, $MASTER_STASIUN_BASIC);
    return array_map(function ($item) {
        return [
            'id' => $item['kode'], // gunakan kode sebagai id sintetis agar dropdown tetap berfungsi
            'kode' => $item['kode'],
            'nama' => $item['nama'],
            'lokasi' => trim((string)($item['alamat'] ?? $item['upt'] ?? $item['nama'] ?? '')),
            'tipe' => $item['tipe'],
        ];
    }, $merged);
}
