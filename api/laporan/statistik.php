<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';

$user = require_role(['admin', 'petugas']);

$pdo = get_pdo();

$params = [];
$condition = '';

if ($user['role'] !== 'admin') {
    $condition = ' WHERE user_id = :user_id';
    $params['user_id'] = $user['id'];
}

$summarySql = 'SELECT jenis, status, COUNT(*) AS total FROM laporan' . $condition . ' GROUP BY jenis, status';
$stmtSummary = $pdo->prepare($summarySql);
$stmtSummary->execute($params);
$rows = $stmtSummary->fetchAll();

$byStatus = [];
$byJenis = [];

foreach ($rows as $row) {
    $jenis = $row['jenis'];
    $status = $row['status'];
    $total = (int)$row['total'];

    if (!isset($byJenis[$jenis])) {
        $byJenis[$jenis] = 0;
    }
    $byJenis[$jenis] += $total;

    if (!isset($byStatus[$status])) {
        $byStatus[$status] = 0;
    }
    $byStatus[$status] += $total;
}

json_response([
    'success' => true,
    'data' => [
        'by_status' => $byStatus,
        'by_jenis' => $byJenis
    ]
]);
