<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/_master_petugas_data.php';

require_role(['admin', 'petugas']);

$q = strtolower(trim($_GET['q'] ?? ''));
$list = $MASTER_PETUGAS;

$filtered = array_values(array_filter($list, function ($item) use ($q) {
    if ($q === '') {
        return true;
    }
    $nama = strtolower((string)($item['nama'] ?? ''));
    $nip = strtolower((string)($item['nip'] ?? ''));
    return str_contains($nama, $q) || str_contains($nip, $q);
}));

$data = array_map(function ($item, $idx) {
    return [
        'id' => $idx + 1,
        'nama' => $item['nama'] ?? '',
        'nip' => $item['nip'] ?? '',
        'upt' => null,
        'jabatan' => null,
        'kontak' => null,
        'email' => null,
    ];
}, $filtered, array_keys($filtered));

json_response([
    'success' => true,
    'data' => $data,
    'source' => 'master_petugas'
]);
