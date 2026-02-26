<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /laporan_bmkg/laporan_list.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Laporan - Laporan BMKG</title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <style>
    /* ── Topbar ── */
    .app-shell .app-topbar {
      padding: 18px 24px !important;
      background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6) !important;
      box-shadow: 0 4px 24px rgba(37,99,235,0.22) !important;
      display: flex !important; align-items: center !important; gap: 14px !important;
    }
    .app-shell .app-topbar .page-title { display: flex; flex-direction: column; gap: 3px; }
    .app-shell .app-topbar .page-title h1 { font-size: 22px !important; font-weight: 800 !important; color: #fff !important; letter-spacing: -0.3px; margin: 0 !important; }
    .app-shell .app-topbar .page-title .subtitle { color: #bfdbfe !important; font-size: 13px !important; margin: 0 !important; }
    .app-shell .app-topbar .user-box { margin-left: auto; min-width: 0 !important; background: rgba(255,255,255,0.13) !important; border: 1px solid rgba(255,255,255,0.26) !important; box-shadow: none !important; padding: 7px 14px !important; border-radius: 999px !important; }
    .app-shell .app-topbar .user-box > div[data-user-info] { color: #fff !important; font-size: 13px; font-weight: 700; }
    .app-shell .app-topbar .user-box .user-role { color: #bfdbfe !important; font-size: 11px !important; }

    /* ── Page body ── */
    .app-shell .page-body { padding: 20px 24px 48px; display: flex; flex-direction: column; gap: 16px; }

    /* ── Cards ── */
    .detail-card {
      background: #fff;
      border: 1px solid #e1e7f0;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(15,23,42,0.07);
      overflow: hidden;
      animation: fadeIn 0.3s ease;
    }
    .card-head {
      display: flex; align-items: center; gap: 12px;
      padding: 16px 20px;
      border-bottom: 1px solid #f1f5f9;
    }
    .card-icon {
      width: 34px; height: 34px; border-radius: 10px;
      background: linear-gradient(135deg, #1e40af, #2563eb);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .card-icon svg { width: 16px; height: 16px; color: #fff; }
    .card-head h2 { margin: 0; font-size: 15px; font-weight: 800; color: #0f172a; letter-spacing: -0.2px; }
    .card-body { padding: 20px; }

    /* ── Hero status row ── */
    .hero-row {
      display: flex; align-items: flex-start; justify-content: space-between;
      gap: 16px; flex-wrap: wrap;
    }
    .hero-meta { display: flex; flex-direction: column; gap: 8px; }
    .hero-kode {
      font-size: 12px; font-weight: 700; color: #64748b;
      text-transform: uppercase; letter-spacing: 0.5px;
    }
    .hero-title-text { font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px; margin: 0; }
    .hero-actions { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }

    /* Status badge */
    .status-badge {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 12px; border-radius: 999px;
      font-weight: 700; font-size: 12px;
      text-transform: uppercase; letter-spacing: 0.4px;
    }
    .status-badge::before { content:''; width:7px; height:7px; border-radius:50%; flex-shrink:0; }
    .status-draft    { background:#f1f5f9; color:#475569; } .status-draft::before    { background:#94a3b8; }
    .status-diajukan { background:#dbeafe; color:#1e40af; } .status-diajukan::before { background:#3b82f6; }
    .status-diproses { background:#fff7ed; color:#c2410c; } .status-diproses::before { background:#f97316; }
    .status-selesai  { background:#dcfce7; color:#15803d; } .status-selesai::before  { background:#22c55e; }
    .status-ditolak  { background:#fee2e2; color:#b91c1c; } .status-ditolak::before  { background:#ef4444; }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 9px 16px; border-radius: 10px;
      font-family: inherit; font-size: 13px; font-weight: 700;
      cursor: pointer; border: none; text-decoration: none;
      transition: transform 120ms ease, box-shadow 120ms ease;
      white-space: nowrap;
    }
    .btn svg { width: 14px; height: 14px; }
    .btn:hover { transform: translateY(-1px); }
    .btn.primary { background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6); color: #fff; box-shadow: 0 6px 18px rgba(37,99,235,0.28); }
    .btn.primary:hover { box-shadow: 0 10px 26px rgba(37,99,235,0.36); }
    .btn.outline { background: #fff; color: #2563eb; border: 1.5px solid #93c5fd; }
    .btn.outline:hover { background: #eff6ff; }
    .btn.warning { background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; box-shadow: 0 6px 16px rgba(217,119,6,0.25); }

    /* Admin status form */
    .status-update-bar {
      display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
      margin-top: 16px; padding-top: 16px;
      border-top: 1px solid #f1f5f9;
    }
    .status-update-bar label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .status-update-bar select,
    .status-update-bar input[type="text"] {
      padding: 9px 12px; border: 1.5px solid #e2e8f0; border-radius: 10px;
      background: #f8fafc; font-family: inherit; font-size: 13px; font-weight: 500;
      color: #0f172a; outline: none;
      transition: border-color 150ms, box-shadow 150ms;
    }
    .status-update-bar select:focus,
    .status-update-bar input[type="text"]:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); background: #fff; }
    .status-update-bar input[type="text"] { flex: 1; min-width: 200px; }

    /* Info grid */
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 12px; }
    .info-field {
      padding: 12px 14px; border: 1px solid #e8edf5; border-radius: 12px;
      background: #f8fafc;
    }
    .info-label { font-size: 10.5px; font-weight: 700; color: #94a3b8; letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 5px; }
    .info-value { font-size: 14px; font-weight: 700; color: #0f172a; word-break: break-word; }

    /* Detail sub-grid */
    .detail-sub { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 10px; margin-top: 10px; }

    /* Sub-section titles within detail card */
    .sub-title {
      font-size: 12px; font-weight: 800; color: #64748b;
      text-transform: uppercase; letter-spacing: 0.7px;
      margin: 18px 0 10px; display: flex; align-items: center; gap: 8px;
    }
    .sub-title::after { content:''; flex:1; height:1px; background:#e8edf5; }
    .sub-title:first-child { margin-top: 0; }

    /* Tables inside detail */
    .detail-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid #e2e8f0; margin-top: 10px; }
    .detail-table { width: 100%; border-collapse: collapse; min-width: 400px; font-size: 13px; }
    .detail-table thead th {
      background: linear-gradient(135deg, #1e40af, #2563eb);
      color: #dbeafe; text-align: left; padding: 9px 12px;
      font-size: 10.5px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 0.5px; white-space: nowrap;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .detail-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; color: #1e293b; vertical-align: middle; }
    .detail-table tbody tr:last-child td { border-bottom: none; }
    .detail-table tbody tr:hover td { background: #eff6ff; }
    .detail-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .detail-table tbody tr:nth-child(even):hover td { background: #eff6ff; }

    /* Gallery */
    .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 12px; }
    .gallery-card {
      background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
      overflow: hidden; box-shadow: 0 4px 14px rgba(15,23,42,0.06);
      transition: transform 140ms ease, box-shadow 140ms ease;
    }
    .gallery-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,23,42,0.11); }
    .gallery-card img { width: 100%; height: 160px; object-fit: cover; display: block; }
    .gallery-caption { padding: 8px 10px; font-size: 12px; color: #475569; font-weight: 500; border-top: 1px solid #f1f5f9; }

    /* Lampiran */
    .lampiran-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 8px; }
    .lampiran-list li a {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 14px; border-radius: 10px; border: 1.5px solid #e2e8f0;
      background: #f8fafc; font-size: 13px; font-weight: 600; color: #2563eb;
      text-decoration: none; transition: border-color 120ms, background 120ms;
    }
    .lampiran-list li a:hover { background: #eff6ff; border-color: #93c5fd; }
    .lampiran-list li a svg { width: 15px; height: 15px; flex-shrink: 0; }

    /* Global alert */
    #global-alert:empty { display: none; }
    .alert { padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 4px; }
    .alert.error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    .alert.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

    /* Style inline tables generated by renderDetailHTML JS */
    #detail { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 12px; }
    #detail > div { padding: 12px 14px; border: 1px solid #e8edf5; border-radius: 12px; background: #f8fafc; font-size: 14px; color: #0f172a; }
    #detail > div > strong { display: block; font-size: 10.5px; font-weight: 700; color: #94a3b8; letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 5px; }
    #detail > div[style*="grid-column"] { grid-column: 1 / -1; background: transparent; border: none; padding: 4px 0; }
    #detail table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; border-radius: 10px; overflow: hidden; }
    #detail table thead tr { background: linear-gradient(135deg, #1e40af, #2563eb) !important; }
    #detail table thead th {
      color: #dbeafe !important; text-align: left !important; padding: 9px 12px !important;
      font-size: 10.5px !important; font-weight: 700 !important; text-transform: uppercase !important;
      letter-spacing: 0.5px !important; white-space: nowrap;
      background: transparent !important;
      border: none !important;
    }
    #detail table tbody td { padding: 9px 12px !important; border-bottom: 1px solid #f1f5f9 !important; border-left: none !important; border-right: none !important; border-top: none !important; color: #1e293b; vertical-align: middle; }
    #detail table tbody tr:last-child td { border-bottom: none !important; }
    #detail table tbody tr:nth-child(even) td { background: #f8fafc; }
    #detail table tbody tr:hover td { background: #eff6ff !important; }
    #detail table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; border-radius: 10px; overflow: hidden; }
    #detail table thead tr { background: linear-gradient(135deg, #1e40af, #2563eb) !important; }
    #detail table thead th {
      color: #dbeafe !important; text-align: left !important; padding: 9px 12px !important;
      font-size: 10.5px !important; font-weight: 700 !important; text-transform: uppercase !important;
      letter-spacing: 0.5px !important; white-space: nowrap;
      background: transparent !important;
      border: none !important;
    }
    #detail table tbody td { padding: 9px 12px !important; border-bottom: 1px solid #f1f5f9 !important; border-left: none !important; border-right: none !important; border-top: none !important; color: #1e293b; vertical-align: middle; }
    #detail table tbody tr:last-child td { border-bottom: none !important; }
    #detail table tbody tr:nth-child(even) td { background: #f8fafc; }
    #detail table tbody tr:hover td { background: #eff6ff !important; }
    #detail .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 10px; margin-top: 8px; }

    .muted { color: #94a3b8; font-size: 13px; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

    @media (max-width: 640px) {
      .app-shell .page-body { padding: 14px 14px 40px; }
      .hero-row { flex-direction: column; }
      .card-body { padding: 14px; }
    }
  </style>
</head>
<body class="app-shell">
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark"></div>
        <div>
          <div class="brand-title">Laporan BMKG</div>
          <div class="brand-sub">Pelaporan</div>
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
          <h1>Detail Laporan</h1>
          <div class="subtitle">ID #<?php echo htmlspecialchars($id); ?></div>
        </div>
        <div class="user-box">
          <div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
        </div>
      </header>
      <main class="page-body">
        <div id="global-alert"></div>
        <div class="detail-shell" style="display:flex;flex-direction:column;gap:16px;">

          <!-- Hero card: kode, status, actions, admin form -->
          <section class="detail-card">
            <div class="card-body">
              <div class="hero-row">
                <div class="hero-meta">
                  <div class="hero-kode" id="hero-kode">Memuat…</div>
                  <h1 class="hero-title-text" id="hero-jenis">Detail Laporan</h1>
                  <div id="status-pill" class="status-badge status-draft" style="width:fit-content;margin-top:4px;"></div>
                  <div id="catatan-admin-note"></div>
                </div>
                <div class="hero-actions">
                  <a class="btn outline" href="/laporan_bmkg/laporan_list.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali
                  </a>
                  <a class="btn primary" id="pdf-link" target="_blank">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Unduh PDF
                  </a>
                  <a class="btn warning" id="edit-btn" style="display:none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Draft
                  </a>
                </div>
              </div>
              <?php if ($user['role'] === 'admin'): ?>
              <form id="status-form" class="status-update-bar">
                <label>Ubah Status:</label>
                <select id="status-select">
                  <option value="diajukan">Diajukan</option>
                  <option value="diproses">Diproses</option>
                  <option value="selesai">Selesai</option>
                  <option value="ditolak">Ditolak</option>
                </select>
                <input type="text" id="catatan-admin" placeholder="Catatan admin (opsional)" />
                <button class="btn primary" type="submit">Simpan</button>
              </form>
              <?php endif; ?>
            </div>
          </section>

          <!-- Info laporan -->
          <section class="detail-card">
            <div class="card-head">
              <div class="card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
              <h2>Info Laporan</h2>
            </div>
            <div class="card-body">
              <div id="info" class="info-grid"></div>
            </div>
          </section>

          <!-- Detail laporan -->
          <section class="detail-card">
            <div class="card-head">
              <div class="card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              </div>
              <h2>Detail Laporan</h2>
            </div>
            <div class="card-body">
              <div id="detail"></div>
            </div>
          </section>

          <!-- Dokumentasi -->
          <section class="detail-card">
            <div class="card-head">
              <div class="card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </div>
              <h2>Dokumentasi Foto</h2>
            </div>
            <div class="card-body">
              <div id="fotos" class="gallery-grid"></div>
            </div>
          </section>

          <!-- Lampiran -->
          <section class="detail-card">
            <div class="card-head">
              <div class="card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
              </div>
              <h2>Lampiran</h2>
            </div>
            <div class="card-body">
              <ul id="lampiran" class="lampiran-list"></ul>
            </div>
          </section>

        </div>
      </main>
    </div>
  </div>
  <script src="/laporan_bmkg/assets/js/main.js"></script>
  <script>
    const laporanId = <?php echo (int)$id; ?>;
    const infoEl = document.getElementById('info');
    const detailEl = document.getElementById('detail');
    const fotosEl = document.getElementById('fotos');
    const lampiranEl = document.getElementById('lampiran');
    const pdfLink = document.getElementById('pdf-link');
    const editBtn = document.getElementById('edit-btn');
    const statusForm = document.getElementById('status-form');
    const statusSelect = document.getElementById('status-select');
    const catatanInput = document.getElementById('catatan-admin');
    const statusDisplay = document.getElementById('status-display');
    const statusPill = document.getElementById('status-pill');

    pdfLink.href = `${APP_BASE_PATH}/api/laporan/pdf.php?id=${laporanId}`;

    function renderStatusBadge(status) {
      const clsMap = {
        draft: 'status-draft',
        diajukan: 'status-diajukan',
        diproses: 'status-diproses',
        selesai: 'status-selesai',
        ditolak: 'status-ditolak'
      };
      const label = formatStatus(status) || '-';
      const cls = clsMap[status] || 'status-draft';
      return { label, cls };
    }

    function renderDetailHTML(laporan, detail) {
      const jenis = laporan.jenis;
      const detailJson = detail?.detail_json || detail || {};
      let html = '';

      if (jenis === 'wrs_ng') {
        html = `
          <div><strong>Nomor Surat</strong><br>${detailJson.nomor_surat || '-'}</div>
          <div><strong>Kegiatan</strong><br>${detailJson.kegiatan || '-'}</div>
          <div><strong>Tempat</strong><br>${detailJson.tempat || '-'}</div>
          <div><strong>Tanggal Kegiatan</strong><br>${detailJson.tanggal_kegiatan ? formatDate(detailJson.tanggal_kegiatan) : '-'}</div>
          <div><strong>Stasiun</strong><br>${detailJson.stasiun || '-'}</div>
        `;
        
        if (detailJson.petugas_pelaksana && detailJson.petugas_pelaksana.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Petugas Pelaksana</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Nama</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">NIP</th>
              </tr></thead>
              <tbody>
                ${detailJson.petugas_pelaksana.map(p => `<tr style="border-bottom:1px solid #cbd5e1;">
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nama || '-'}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nip || '-'}</td>
                </tr>`).join('')}
              </tbody>
            </table>
          </div>`;
        }

        if (detailJson.kegiatan_pemeliharaan && detailJson.kegiatan_pemeliharaan.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Kegiatan Pemeliharaan</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Uraian</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Keterangan</th>
              </tr></thead>
              <tbody>
                ${detailJson.kegiatan_pemeliharaan.map(k => {
                  const judul = k.uraian || k.judul || k.title || '-';
                  const ket = k.keterangan || k.ket || k.description || '-';
                  return `<tr style="border-bottom:1px solid #cbd5e1;">
                    <td style="padding:8px;border:1px solid #cbd5e1;">${judul}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${ket}</td>
                  </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>`;
        }

        if (detailJson.catatan_penggantian && detailJson.catatan_penggantian.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Catatan Penggantian</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Nama Alat</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Tipe Merk</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Jumlah</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">S/N Baru</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">S/N Lama</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Keterangan</th>
              </tr></thead>
              <tbody>
                ${detailJson.catatan_penggantian.map(c => {
                  const nama = (c.nama_alat || '').trim();
                  const merk = (c.merk_type || '').trim();
                  const jumlah = (c.jumlah || '').toString().trim();
                  const snBaru = (c.sn_baru || '').trim();
                  const snLama = (c.sn_lama || '').trim();
                  const ket = (c.keterangan || c.ket || c.deskripsi || c.description || c.catatan || c.notes || '').trim();
                  const dash = '-';
                  return `<tr style="border-bottom:1px solid #cbd5e1;">
                    <td style="padding:8px;border:1px solid #cbd5e1;">${nama || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${merk || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;">${jumlah || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${snBaru || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${snLama || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${ket || dash}</td>
                  </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>`;
        }
      } else if (jenis === 'accelerograph' || jenis === 'intensitymeter') {
        html = `
          <div><strong>Kode Site</strong><br>${detailJson.kode_site || '-'}</div>
          <div><strong>Nama Site</strong><br>${detailJson.nama_site || '-'}</div>
          <div><strong>Kerusakan</strong><br>${detailJson.deskripsi_kerusakan || '-'}</div>
          <div><strong>Rekomendasi</strong><br>${detailJson.rekomendasi || '-'}</div>
          <div><strong>Status Alat</strong><br>${detailJson.status_alat ? detailJson.status_alat.toUpperCase() : '-'}</div>
        `;

        if (detailJson.petugas_pelaksana && detailJson.petugas_pelaksana.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Petugas Pelaksana</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Nama</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">NIP</th>
              </tr></thead>
              <tbody>
                ${detailJson.petugas_pelaksana.map(p => `<tr style="border-bottom:1px solid #cbd5e1;">
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nama || '-'}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nip || '-'}</td>
                </tr>`).join('')}
              </tbody>
            </table>
          </div>`;
        }

        if (detailJson.catatan_penggantian && detailJson.catatan_penggantian.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Catatan Penggantian</strong>
            <table class="table-block" style="margin-top:8px;">
              <thead><tr>
                <th>Nama Alat</th>
                <th>Merk/Type</th>
                <th>Jumlah</th>
                <th>S/N Baru</th>
                <th>S/N Lama</th>
                <th>Keterangan</th>
              </tr></thead>
              <tbody>
                ${detailJson.catatan_penggantian.map(c => {
                  const nama = (c.nama_alat || '').trim();
                  const merk = (c.merk_type || '').trim();
                  const jumlah = (c.jumlah || '').toString().trim();
                  const snBaru = (c.sn_baru || '').trim();
                  const snLama = (c.sn_lama || '').trim();
                  const ket = (c.keterangan || c.ket || c.deskripsi || c.description || c.catatan || c.notes || '').trim();
                  const dash = '-';
                  return `<tr>
                    <td>${nama || dash}</td>
                    <td>${merk || dash}</td>
                    <td>${jumlah || dash}</td>
                    <td>${snBaru || dash}</td>
                    <td>${snLama || dash}</td>
                    <td>${ket || dash}</td>
                  </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>`;
        }

      } else if (jenis === 'seismograph') {
        const tugas = detailJson.tugas || {};
        const stasiun = detailJson.stasiun || {};
        const kontak = stasiun.kontak || {};
        const pembersihan = detailJson.pembersihan || {};
        const pengukuranPower = detailJson.pengukuran_power || {};
        const digitizer = detailJson.digitizer_sensor || {};
        const fmt = (val) => (val === undefined || val === null || val === '' ? '-' : val);

        const nomorSurat = detailJson.nomor_surat || tugas.nomor_surat_tugas || '-';
        const tanggalPemeliharaan = tugas.tanggal_pemeliharaan || detailJson.tanggal_pemeliharaan;

        html = `
          <div><strong>Nomor Surat</strong><br>${fmt(nomorSurat)}</div>
          <div><strong>Tanggal Pemeliharaan</strong><br>${tanggalPemeliharaan ? formatDate(tanggalPemeliharaan) : '-'}</div>
          <div><strong>Nama Stasiun</strong><br>${fmt(stasiun.nama_stasiun)}</div>
          <div><strong>Kode Stasiun</strong><br>${fmt(stasiun.kode_stasiun)}</div>
        `;

        html += `
          <div><strong>Nama UPT</strong><br>${fmt(stasiun.nama_upt)}</div>
          <div><strong>Nama Stasiun</strong><br>${fmt(stasiun.nama_stasiun)}</div>
          <div><strong>Kode Stasiun</strong><br>${fmt(stasiun.kode_stasiun)}</div>
          <div><strong>Status Stasiun</strong><br>${fmt(stasiun.status)}</div>
          <div><strong>Tanggal Instalasi</strong><br>${stasiun.tanggal_instalasi ? formatDate(stasiun.tanggal_instalasi) : '-'}</div>
          <div><strong>Koordinat</strong><br>${fmt(stasiun.koordinat)}</div>
          <div style="grid-column:1/-1;"><strong>Alamat</strong><br>${fmt(stasiun.alamat)}</div>
          <div><strong>Kontak Person</strong><br>${fmt(kontak.nama)}</div>
          <div><strong>No HP Kontak</strong><br>${fmt(kontak.hp)}</div>
        `;

        if (detailJson.petugas_pelaksana && detailJson.petugas_pelaksana.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Petugas Pelaksana</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Nama</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">NIP</th>
              </tr></thead>
              <tbody>
                ${detailJson.petugas_pelaksana.map(p => `<tr style="border-bottom:1px solid #cbd5e1;">
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nama || '-'}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${p.nip || p.peran || '-'}</td>
                </tr>`).join('')}
              </tbody>
            </table>
          </div>`;
        }

        html += `
          <div style="grid-column:1/-1;"><strong>Pembersihan</strong>
            <div class="detail-grid" style="margin-top:8px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
              <div><div class="info-label">Rumput & Dahan</div><div class="info-value">${pembersihan.rumput || '-'}</div></div>
              <div><div class="info-label">Sarang Laba-laba</div><div class="info-value">${pembersihan.sarang || '-'}</div></div>
              <div><div class="info-label">Parabola & Solar Cell</div><div class="info-value">${pembersihan.panel || '-'}</div></div>
              <div><div class="info-label">Solar Regulator</div><div class="info-value">${pembersihan.regulator || '-'}</div></div>
              <div><div class="info-label">Baterai</div><div class="info-value">${pembersihan.baterai || '-'}</div></div>
            </div>
          </div>
        `;

        html += `
          <div style="grid-column:1/-1;"><strong>Pengukuran Power</strong>
            <div class="detail-grid" style="margin-top:8px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
              <div><div class="info-label">Tegangan PLN</div><div class="info-value">${fmt(pengukuranPower.tegangan_pln)}</div></div>
              <div><div class="info-label">Sisa Token</div><div class="info-value">${fmt(pengukuranPower.sisa_token)}</div></div>
            </div>
          </div>
        `;

        const regulators = Array.isArray(pengukuranPower.regulator) ? pengukuranPower.regulator : [];
        if (regulators.length) {
          html += `<div style="grid-column:1/-1;">
            <strong>Regulator</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;border:1px solid #cbd5e1;">Nama</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">Tegangan</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">Catatan</th>
              </tr></thead>
              <tbody>
                ${regulators.map(r => `<tr>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(r.nama)}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(r.tegangan)}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(r.catatan)}</td>
                </tr>`).join('')}
              </tbody>
            </table>
          </div>`;
        }

        const baterai = Array.isArray(pengukuranPower.baterai) ? pengukuranPower.baterai : [];
        if (baterai.length) {
          html += `<div style="grid-column:1/-1;">
            <strong>Baterai</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;border:1px solid #cbd5e1;">Posisi</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">Tegangan</th>
                <th style="padding:8px;border:1px solid #cbd5e1;">Kondisi</th>
              </tr></thead>
              <tbody>
                ${baterai.map(b => `<tr>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(b.posisi)}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(b.tegangan)}</td>
                  <td style="padding:8px;border:1px solid #cbd5e1;">${fmt(b.kondisi)}</td>
                </tr>`).join('')}
              </tbody>
            </table>
          </div>`;
        }

        html += `
          <div style="grid-column:1/-1;"><strong>Pengecekan Digitizer & Sensor</strong>
            <div class="detail-grid" style="margin-top:8px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
              <div><div class="info-label">Centering Broadband</div><div class="info-value">${digitizer.centering_broadband || '-'}</div></div>
              <div><div class="info-label">Leveling Broadband</div><div class="info-value">${digitizer.leveling_broadband || '-'}</div></div>
              <div><div class="info-label">Leveling Accelerometer</div><div class="info-value">${digitizer.leveling_accelerometer || '-'}</div></div>
              <div><div class="info-label">Kondisi Kabel</div><div class="info-value">${digitizer.kondisi_kabel || '-'}</div></div>
            </div>
          </div>
        `;

        html += `
          <div><strong>Rekomendasi</strong><br>${detailJson.rekomendasi || '-'}</div>
          <div><strong>Catatan</strong><br>${detailJson.catatan || '-'}</div>
        `;

        const catatanPenggantian = Array.isArray(detailJson.catatan_penggantian) ? detailJson.catatan_penggantian : [];
        if (catatanPenggantian.length > 0) {
          html += `<div style="grid-column:1/-1;">
            <strong>Catatan Penggantian</strong>
            <table style="width:100%;border-collapse:collapse;margin-top:8px;">
              <thead><tr style="background:#f1f5f9;border-bottom:1px solid #cbd5e1;">
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Nama Alat</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Merk/Type</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Jumlah</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">S/N Baru</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">S/N Lama</th>
                <th style="padding:8px;text-align:left;border:1px solid #cbd5e1;">Keterangan</th>
              </tr></thead>
              <tbody>
                ${catatanPenggantian.map(c => {
                  const nama = (c.nama_alat || '').trim();
                  const merk = (c.merk_type || '').trim();
                  const jumlah = (c.jumlah || '').toString().trim();
                  const snBaru = (c.sn_baru || '').trim();
                  const snLama = (c.sn_lama || '').trim();
                  const ket = (c.keterangan || c.ket || c.deskripsi || c.description || c.catatan || c.notes || '').trim();
                  const dash = '-';
                  return `<tr style="border-bottom:1px solid #cbd5e1;">
                    <td style="padding:8px;border:1px solid #cbd5e1;">${nama || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${merk || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;text-align:center;">${jumlah || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${snBaru || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${snLama || dash}</td>
                    <td style="padding:8px;border:1px solid #cbd5e1;">${ket || dash}</td>
                  </tr>`;
                }).join('')}
              </tbody>
            </table>
          </div>`;
        }
      }

      return html || `<pre style="white-space:pre-wrap;background:#0f172a;color:#e2e8f0;padding:12px;border-radius:8px;overflow:auto;">${JSON.stringify(detailJson, null, 2)}</pre>`;
    }

    async function loadDetail() {
      try {
        const res = await apiRequest(`/api/laporan/detail.php?id=${laporanId}`);
        const { laporan, detail, dokumentasi, lampiran } = res.data;

        infoEl.innerHTML = `
          <div class="info-field">
            <div class="info-label">Kode</div>
            <div class="info-value">${laporan.kode_laporan}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Jenis</div>
            <div class="info-value">${formatJenis(laporan.jenis)}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Tanggal</div>
            <div class="info-value">${formatDate(laporan.tanggal_laporan)}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Status</div>
            <div class="info-value">${formatStatus(laporan.status)}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Stasiun</div>
            <div class="info-value">${laporan.nama_stasiun || '-'}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Pelapor</div>
            <div class="info-value">${laporan.nama_pelapor || '-'}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Surat Tugas</div>
            <div class="info-value">${laporan.nomor_surat || '-'}</div>
          </div>
          <div class="info-field">
            <div class="info-label">Dibuat</div>
            <div class="info-value">${formatDate(laporan.created_at)}</div>
          </div>`;


        // Render detail utama
        detailEl.innerHTML = renderDetailHTML(laporan, detail);

        // Tampilkan catatan admin di bawah badge status
        const catatanAdminNote = document.getElementById('catatan-admin-note');
        if (catatanAdminNote) {
          if (laporan.catatan_admin && laporan.catatan_admin.trim() !== '') {
            catatanAdminNote.innerHTML = `<div class="section-note" style="background:#fef9c3;border:1.5px solid #fde047;padding:10px 14px;border-radius:10px;margin-top:10px;max-width:420px;">
              <strong style="color:#b45309;font-size:13px;">Catatan Admin:</strong><br>
              <span style="color:#92400e;font-size:14px;">${laporan.catatan_admin.replace(/\n/g, '<br>')}</span>
            </div>`;
          } else {
            catatanAdminNote.innerHTML = '';
          }
        }

        // Show edit button for draft status
        if (laporan.status === 'draft') {
          const editUrl = {
            'wrs_ng': '/laporan_bmkg/laporan_edit_wrs.php',
            'accelerograph': '/laporan_bmkg/laporan_edit_accelerograph.php',
            'intensitymeter': '/laporan_bmkg/laporan_edit_accelerograph.php',
            'seismograph': '/laporan_bmkg/laporan_edit_seismograph.php'
          }[laporan.jenis];
          
          if (editUrl) {
            editBtn.href = `${editUrl}?id=${laporanId}`;
            editBtn.style.display = 'block';
          }
        }

        const fotos = Array.isArray(dokumentasi) ? dokumentasi : [];
        if (!fotos.length) {
          fotosEl.innerHTML = '<div class="muted">Tidak ada foto</div>';
        } else {
          fotosEl.innerHTML = fotos.map(f => {
            const src = `${APP_BASE_PATH}/uploads/${f.file_name}`;
            const cap = f.caption || '';
            return `<div class="gallery-card">
              <img src="${src}" alt="foto" loading="lazy" />
              <div class="gallery-caption">${cap}</div>
            </div>`;
          }).join('');
        }

        const lamps = Array.isArray(lampiran) ? lampiran : [];
        if (!lamps.length) {
          lampiranEl.innerHTML = '<li class="muted">Tidak ada lampiran</li>';
        } else {
          lampiranEl.innerHTML = lamps.map(l => `<li><a href="${APP_BASE_PATH}/uploads/${l.file_name}" target="_blank">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            ${l.lampiran_type || 'File'} &mdash; ${l.file_name}
          </a></li>`).join('');
        }

        // Set status display
        // Hero header
        const heroKode = document.getElementById('hero-kode');
        const heroJenis = document.getElementById('hero-jenis');
        const jenisLabel = {
          wrs_ng: 'Laporan WRS-NG',
          seismograph: 'Laporan Seismograph',
          accelerograph: 'Laporan Accelerograph',
          intensitymeter: 'Laporan Intensitymeter'
        }[laporan.jenis] || 'Detail Laporan';
        if (heroKode) heroKode.textContent = laporan.kode_laporan || `#${laporanId}`;
        if (heroJenis) heroJenis.textContent = jenisLabel;

        if (statusSelect) statusSelect.value = laporan.status;
        const badge = renderStatusBadge(laporan.status);
        if (statusPill) {
          statusPill.textContent = badge.label;
          statusPill.className = `status-badge ${badge.cls}`;
        }
        if (statusDisplay) statusDisplay.textContent = badge.label;
      } catch (error) {
        console.error(error);
        showGlobalAlert(error.message || 'Gagal memuat detail', 'error');
      }
    }

    if (statusForm) {
      statusForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
          const payload = {
            id: laporanId,
            status: statusSelect.value,
            catatan: catatanInput.value
          };
          await apiRequest('/api/laporan/update_status.php', { method: 'POST', body: JSON.stringify(payload) });
          showGlobalAlert('Status diperbarui', 'success');
          loadDetail();
        } catch (error) {
          showGlobalAlert(error.message || 'Gagal update status', 'error');
        }
      });
    }

    requireAuth().then(loadDetail);
  </script>
</body>
</html>
