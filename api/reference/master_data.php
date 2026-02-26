<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/_master_stasiun_data.php';

// This endpoint serves static master data for station/site references and WRS locations.
// Source data mirrors code/services/initial_data.py to keep the web app in sync with seed data.

require_role(['admin', 'petugas']);

$stasiunLengkap = $MASTER_STASIUN_LENGKAP;
$stasiunBasic = $MASTER_STASIUN_BASIC;
$masterLokasi = $MASTER_LOKASI_DATA;

$kind = strtolower($_GET['kind'] ?? 'stasiun');
$tipeFilter = strtolower($_GET['tipe'] ?? '');
$q = strtolower(trim($_GET['q'] ?? ''));

$allStasiun = array_merge($stasiunLengkap, $stasiunBasic);

if ($kind === 'lokasi') {
    json_response([
        'success' => true,
        'data' => $masterLokasi,
        'source' => 'master_lokasi'
    ]);
}

$filtered = array_values(array_filter($allStasiun, function ($item) use ($tipeFilter, $q) {
    $matchesTipe = !$tipeFilter || strtolower($item['tipe']) === $tipeFilter;
    $matchesSearch = !$q || str_contains(strtolower($item['kode']), $q) || str_contains(strtolower($item['nama']), $q);
    return $matchesTipe && $matchesSearch;
}));

json_response([
    'success' => true,
    'data' => $filtered,
    'source' => 'master_stasiun'
]);
