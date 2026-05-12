<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}

if (($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Akses ditolak. Hanya admin yang dapat mengelola master pegawai.';
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Master Pegawai - Laporan BMKG</title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/form-shared.css" />
</head>
<body class="app-shell">
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark"></div>
        <div>
          <div class="brand-title">Laporan BMKG</div>
          <div class="brand-sub">Pengaturan</div>
        </div>
      </div>
      <nav class="sidebar-nav" data-nav data-static-nav="true"><?php include __DIR__ . '/includes/nav.php'; ?></nav>
      <div class="sidebar-footer">
        <button class="sidebar-logout" data-logout>Keluar</button>
      </div>
    </aside>
    <div class="app-shell-main">
      <header class="app-topbar">
        <div class="page-title">
          <h1>Master Pegawai</h1>
          <div class="subtitle">Import daftar pegawai dari XLSX dan gunakan untuk auto fill petugas pelaksana.</div>
        </div>
        <div class="user-box">
          <div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <div class="user-role">Admin</div>
        </div>
      </header>

      <main class="page-body">
        <div id="global-alert"></div>

        <section class="section-card">
          <div class="section-title">Daftar Pegawai</div>
          <div class="table-wrap">
            <table class="line-table" id="pegawai-table">
              <thead><tr><th style="width:70px;">No</th><th>Nama</th><th>NIP</th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script src="/laporan_bmkg/assets/js/main.js"></script>
  <script>
    function renderPegawaiTable(items) {
      const tbody = document.querySelector('#pegawai-table tbody');
      if (!tbody) return;
      if (!items.length) {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;color:#94a3b8;">Belum ada data pegawai.</td></tr>';
        return;
      }
      tbody.innerHTML = items.map((item, idx) => `
        <tr>
          <td>${idx + 1}</td>
          <td>${item.nama || '-'}</td>
          <td>${item.nip || '-'}</td>
        </tr>
      `).join('');
    }

    function loadPegawai() {
      return apiRequest('/api/reference/petugas.php', { method: 'GET' })
        .then(res => res.data || [])
        .then(list => {
          renderPegawaiTable(list);
          return list;
        })
        .catch(err => {
          console.error(err);
          showGlobalAlert(err.message || 'Gagal memuat master pegawai.', 'error');
          renderPegawaiTable([]);
        });
    }



    loadPegawai();
  </script>
</body>
</html>
