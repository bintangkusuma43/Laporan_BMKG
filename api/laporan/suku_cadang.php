<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = require_role(['admin', 'petugas']);

$jenisFilter = $_GET['jenis'] ?? '';
$search = $_GET['search'] ?? '';

$pdo = get_pdo();

$sql = 'SELECT l.id AS laporan_id, l.kode_laporan, l.jenis, l.tanggal_laporan, l.nomor_surat, s.nama AS nama_stasiun, s.kode AS kode_stasiun, s.lokasi AS lokasi_stasiun, ld.detail_json
        FROM laporan l
        INNER JOIN laporan_detail ld ON ld.laporan_id = l.id
        LEFT JOIN stasiun s ON s.id = l.stasiun_id
        WHERE 1=1';
$params = [];

if ($jenisFilter) {
    $sql .= ' AND l.jenis = :jenis';
    $params['jenis'] = $jenisFilter;
}

if ($user['role'] !== 'admin') {
    $sql .= ' AND l.user_id = :user_id';
    $params['user_id'] = $user['id'];
}

$sql .= ' ORDER BY l.tanggal_laporan DESC, l.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$results = [];

foreach ($rows as $row) {
    $detail = json_decode($row['detail_json'], true);
    if (!is_array($detail)) {
        continue;
    }

    $replacements = $detail['catatan_penggantian'] ?? [];
    if (!is_array($replacements) || !count($replacements)) {
        continue;
    }

    // Fallback untuk site/kegiatan dan lokasi agar semua kolom riwayat terisi
    $siteName = $detail['nama_site']
        ?? ($detail['stasiun']['label'] ?? null)
        ?? ($detail['stasiun_label'] ?? null)
        ?? ($detail['tempat'] ?? null)
        ?? $row['nama_stasiun']
        ?? '-';

    $siteCode = $detail['kode_site']
        ?? ($detail['stasiun']['kode'] ?? null)
        ?? $row['kode_stasiun']
        ?? null;

    $siteLocation = $detail['lokasi']
        ?? ($detail['stasiun']['lokasi'] ?? null)
        ?? ($detail['tempat'] ?? null)
        ?? $row['lokasi_stasiun']
        ?? null;

    foreach ($replacements as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $results[] = [
            'laporan_id' => (int) $row['laporan_id'],
            'kode_laporan' => $row['kode_laporan'],
            'jenis' => $row['jenis'],
            'tanggal_laporan' => $row['tanggal_laporan'],
            'nama_site' => $entry['nama_site'] ?? $siteName ?? '-',
            'kode_site' => $entry['kode_site'] ?? $siteCode,
            'lokasi' => $entry['lokasi'] ?? $siteLocation,
            'nama_alat' => $entry['nama_alat'] ?? '-',
            'merk_type' => $entry['merk_type'] ?? ($entry['merk'] ?? null),
            'jumlah' => isset($entry['jumlah']) && $entry['jumlah'] !== '' ? (int) $entry['jumlah'] : null,
            'sn_baru' => $entry['sn_baru'] ?? null,
            'sn_lama' => $entry['sn_lama'] ?? null,
            'keterangan' => $entry['keterangan'] ?? '-',
        ];
    }
}

if ($search) {
    $needle = mb_strtolower($search);
    $results = array_values(array_filter($results, function ($item) use ($needle) {
        $haystack = [
            $item['nama_site'] ?? '',
            $item['kode_site'] ?? '',
            $item['lokasi'] ?? '',
            $item['nama_alat'] ?? '',
            $item['merk_type'] ?? '',
            $item['sn_baru'] ?? '',
            $item['sn_lama'] ?? '',
            $item['kode_laporan'] ?? '',
        ];
        foreach ($haystack as $value) {
            if ($value !== null && mb_stripos((string) $value, $needle) !== false) {
                return true;
            }
        }
        return false;
    }));
}

json_response([
    'success' => true,
    'data' => $results,
]);
