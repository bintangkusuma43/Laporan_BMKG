<?php
/**
 * Endpoint untuk mengambil master data petugas dalam format JSON
 * Digunakan oleh JavaScript untuk autofill dua arah
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/_master_petugas_data.php';

// Tidak perlu auth check untuk endpoint ini, data publik
// Cek auth kalau mau ketat
// require_role(['admin', 'petugas']);

// Konversi master data ke format yang mudah untuk JavaScript
$data = array_map(function($item) {
    return [
        'nama' => $item['nama'] ?? '',
        'nip' => $item['nip'] ?? '',
    ];
}, $MASTER_PETUGAS);

// Return JSON langsung bisa digunakan di JavaScript
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
