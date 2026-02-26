<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
require_once __DIR__ . '/../includes/response.php';

// Public endpoint to verify which code version is being served by Apache.
// Does not reveal sensitive info.
json_response([
    'success' => true,
    'app' => 'laporan_bmkg',
    'version' => '2026-01-14-wrs-pdf-enabled',
    'gd_enabled' => extension_loaded('gd'),
    'kop_primary_exists' => is_file(__DIR__ . '/../assets/kop/kop_bmkg.jpg'),
    'kop_fallback_exists' => false,
    'server_time' => date('c'),
]);
