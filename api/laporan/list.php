<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = require_role(['admin', 'petugas']);

$jenis = $_GET['jenis'] ?? null;
$status = $_GET['status'] ?? null;
$tanggalMulai = $_GET['tanggal_mulai'] ?? null;
$tanggalSelesai = $_GET['tanggal_selesai'] ?? null;
$search = $_GET['search'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

$pdo = get_pdo();

$sql = 'SELECT l.id, l.kode_laporan, l.jenis, l.tanggal_laporan, l.status, l.nomor_surat, s.nama AS nama_stasiun, s.kode AS kode_stasiun, u.full_name AS nama_pelapor, l.created_at, ld.detail_json
    FROM laporan l
    INNER JOIN users u ON u.id = l.user_id
    LEFT JOIN stasiun s ON s.id = l.stasiun_id
    LEFT JOIN laporan_detail ld ON ld.laporan_id = l.id
    WHERE 1=1';
$params = [];

if ($jenis) {
    $sql .= ' AND l.jenis = :jenis';
    $params['jenis'] = $jenis;
}

if ($status) {
    $sql .= ' AND l.status = :status';
    $params['status'] = $status;
}

if ($tanggalMulai) {
    $sql .= ' AND l.tanggal_laporan >= :tanggal_mulai';
    $params['tanggal_mulai'] = $tanggalMulai;
}

if ($tanggalSelesai) {
    $sql .= ' AND l.tanggal_laporan <= :tanggal_selesai';
    $params['tanggal_selesai'] = $tanggalSelesai;
}

if ($search) {
    $sql .= ' AND (l.kode_laporan LIKE :search OR l.nomor_surat LIKE :search OR s.nama LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($user['role'] !== 'admin') {
    $sql .= ' AND l.user_id = :user_id';
    $params['user_id'] = $user['id'];
} else {
    // Draft is not considered "submitted to admin". Admin should not see other users' drafts.
    $sql .= ' AND (l.status <> :draft_status OR l.user_id = :admin_user_id)';
    $params['draft_status'] = 'draft';
    $params['admin_user_id'] = $user['id'];
}

$sql .= ' ORDER BY l.created_at DESC';

if ($limit && $limit > 0) {
    $sql .= ' LIMIT ' . (int)$limit;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Sematkan field kegiatan (mis. WRS NG) agar muncul di riwayat
foreach ($rows as &$row) {
    if (!empty($row['detail_json'])) {
        $detail = json_decode($row['detail_json'], true);
        if (is_array($detail)) {
            $row['kegiatan'] = $detail['kegiatan'] ?? null;
            // For seismograph, extract stasiun name if available
            if ($row['jenis'] === 'seismograph') {
                if (isset($detail['stasiun']['nama_stasiun'])) {
                    $row['nama_stasiun'] = $detail['stasiun']['nama_stasiun'];
                }
                if (isset($detail['stasiun']['kode_stasiun'])) {
                    $row['kode_stasiun'] = $detail['stasiun']['kode_stasiun'];
                }
            }
        }
    }
    unset($row['detail_json']);
}
unset($row);

json_response([
    'success' => true,
    'data' => $rows
]);
