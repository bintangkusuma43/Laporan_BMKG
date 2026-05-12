<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - Laporan BMKG</title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: radial-gradient(ellipse at 18% 12%, rgba(37,99,235,0.08) 0%, transparent 38%),
                  radial-gradient(ellipse at 82% 80%, rgba(99,102,241,0.07) 0%, transparent 38%),
                  #f0f4ff;
      font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* ── Page body spacing ───────────────────────────── */
    .app-shell .page-body { padding-top: 22px !important; gap: 20px !important; }

    /* ── Animations ─────────────────────────────────── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .page-body > * { animation: fadeUp 0.45s ease both; }
    .page-body > *:nth-child(1) { animation-delay: 0.08s; }
    .page-body > *:nth-child(2) { animation-delay: 0.16s; }
    .page-body > *:nth-child(3) { animation-delay: 0.24s; }
    .page-body > *:nth-child(4) { animation-delay: 0.32s; }

    /* ── Topbar ──────────────────────────────────────── */
    .dash-topbar {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 24px;
      background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6);
      box-shadow: 0 4px 24px rgba(37,99,235,0.22);
    }
    .dash-topbar .page-title { display: flex; flex-direction: column; gap: 3px; }
    .dash-topbar .page-title h1 { font-size: 22px; font-weight: 800; color: #fff !important; letter-spacing: -0.3px; margin: 0; }
    .dash-topbar .page-title .subtitle { color: #bfdbfe !important; font-size: 13px; margin: 0; }
    .dash-topbar .user-box {
      margin-left: auto;
      min-width: 0 !important;
      background: rgba(255,255,255,0.13) !important;
      border: 1px solid rgba(255,255,255,0.26) !important;
      box-shadow: none !important;
      padding: 7px 14px !important;
      border-radius: 999px !important;
      backdrop-filter: blur(4px);
      display: flex; align-items: center; gap: 10px;
      justify-content: flex-end;
    }
    .dash-topbar .user-avatar {
      width: 34px; height: 34px; border-radius: 50%;
      background: linear-gradient(135deg, #60a5fa, #818cf8);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 800; font-size: 13px; flex-shrink: 0;
    }
    .dash-topbar .user-info { text-align: right; }
    .dash-topbar .user-info-name { font-size: 13px; font-weight: 700; color: #fff !important; }
    .dash-topbar .user-info-role { font-size: 11px; color: #bfdbfe !important; text-transform: capitalize; margin-top: 1px; }

    /* ── Hero ────────────────────────────────────────── */
    .hero-section {
      background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #38bdf8 100%);
      border-radius: 20px;
      padding: 28px 32px;
      color: #fff;
      margin-bottom: 24px;
      box-shadow: 0 12px 40px rgba(37,99,235,0.28);
      position: relative; overflow: hidden;
    }
    .hero-section::before {
      content: '';
      position: absolute; inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
    }
    .hero-section::after {
      content: '';
      position: absolute; top: -60px; right: -60px;
      width: 280px; height: 280px;
      background: rgba(255,255,255,0.07);
      border-radius: 50%; pointer-events: none;
    }
    .hero-content { display: flex; justify-content: space-between; align-items: center; gap: 28px; position: relative; z-index: 1; }
    .hero-left h2 { font-size: 22px; font-weight: 800; margin-bottom: 8px; line-height: 1.3; letter-spacing: -0.3px; }
    .hero-left p { font-size: 13px; color: rgba(255,255,255,0.88); line-height: 1.6; }
    .hero-metrics { display: flex; gap: 12px; flex-wrap: wrap; }
    .metric-card {
      background: rgba(255,255,255,0.14);
      backdrop-filter: blur(12px);
      padding: 14px 18px; border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.22);
      min-width: 110px; text-align: center;
      transition: transform 180ms, background 180ms;
    }
    .metric-card:hover { background: rgba(255,255,255,0.22); transform: translateY(-2px); }
    .metric-label { font-size: 10px; color: rgba(255,255,255,0.75); font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px; }
    .metric-value { font-size: 26px; font-weight: 800; color: #fff; line-height: 1; }
    @media (max-width: 1024px) {
      .hero-content { flex-direction: column; align-items: flex-start; gap: 18px; }
      .hero-metrics { width: 100%; }
    }

    /* ── Section header ──────────────────────────────── */
    .section-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 14px;
    }
    .section-header-left { display: flex; align-items: center; gap: 10px; }
    .section-dot { width: 4px; height: 22px; border-radius: 2px; flex-shrink: 0; }
    .section-header h3 { font-size: 14px; font-weight: 700; color: #1e293b; letter-spacing: -0.1px; }
    .section-header .section-sub { font-size: 12px; color: #94a3b8; margin-top: 1px; }

    /* ── Status cards ────────────────────────────────── */
    .status-section { margin-bottom: 24px; }
    .status-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 12px;
    }
    .status-card {
      background: #fff;
      border-radius: 14px;
      padding: 18px 16px 16px;
      box-shadow: 0 2px 12px rgba(15,23,42,0.06);
      border: 1px solid #e8edf5;
      position: relative; overflow: hidden;
      transition: transform 180ms, box-shadow 180ms;
    }
    .status-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0;
      height: 3px; border-radius: 14px 14px 0 0;
    }
    .status-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(15,23,42,0.1); }
    .status-card[data-s="draft"]::before    { background: #94a3b8; }
    .status-card[data-s="diajukan"]::before { background: #3b82f6; }
    .status-card[data-s="diproses"]::before { background: #f97316; }
    .status-card[data-s="selesai"]::before  { background: #22c55e; }
    .status-card[data-s="ditolak"]::before  { background: #ef4444; }
    .status-card-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; margin-bottom: 12px;
    }
    .status-card-label { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .status-card-value { font-size: 30px; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: 6px; }
    .status-card-sub { font-size: 11px; color: #cbd5e1; font-weight: 600; }
    @media (max-width: 1200px) { .status-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px)  { .status-grid { grid-template-columns: repeat(2, 1fr); } }

    /* ── Jenis cards ─────────────────────────────────── */
    .jenis-section { margin-bottom: 24px; }
    .jenis-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .jenis-card {
      background: #fff; border-radius: 16px;
      padding: 20px 18px;
      box-shadow: 0 2px 12px rgba(15,23,42,0.06);
      border: 1px solid #e8edf5;
      transition: transform 180ms, box-shadow 180ms;
      display: flex; flex-direction: column; gap: 10px;
    }
    .jenis-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(15,23,42,0.1); }
    .jenis-card-top { display: flex; align-items: center; gap: 12px; }
    .jenis-icon-wrap {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; flex-shrink: 0;
    }
    .jenis-card.wrs-ng        .jenis-icon-wrap { background: #dbeafe; }
    .jenis-card.accelerograph .jenis-icon-wrap { background: #fef3c7; }
    .jenis-card.seismograph   .jenis-icon-wrap { background: #ede9fe; }
    .jenis-name { font-size: 12px; color: #64748b; font-weight: 600; line-height: 1.3; }
    .jenis-value { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1; }
    .jenis-progress { height: 5px; background: #e8edf5; border-radius: 3px; overflow: hidden; }
    .jenis-progress-bar { height: 100%; border-radius: 3px; transition: width 0.6s ease; }
    .jenis-card.wrs-ng        .jenis-progress-bar { background: linear-gradient(90deg,#2563eb,#60a5fa); }
    .jenis-card.accelerograph .jenis-progress-bar { background: linear-gradient(90deg,#d97706,#fbbf24); }
    .jenis-card.seismograph   .jenis-progress-bar { background: linear-gradient(90deg,#7c3aed,#a78bfa); }
    .jenis-pct { font-size: 11px; color: #94a3b8; font-weight: 600; }
    @media (max-width: 960px) { .jenis-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 600px) { .jenis-grid { grid-template-columns: 1fr; } }

    /* ── Latest table ────────────────────────────────── */
    .latest-section {
      background: #fff; border-radius: 16px;
      box-shadow: 0 2px 12px rgba(15,23,42,0.06);
      overflow: hidden; border: 1px solid #e8edf5;
      margin-bottom: 24px;
    }
    .latest-header {
      padding: 14px 18px;
      display: flex; justify-content: space-between; align-items: center;
      border-bottom: 1px solid #e8edf5; background: #f8fafc;
    }
    .latest-title { font-size: 14px; font-weight: 700; color: #1e293b; }
    .latest-subtitle { font-size: 12px; color: #94a3b8; margin-top: 2px; }
    .latest-actions { display: flex; gap: 10px; align-items: center; }
    .search-box {
      background: #fff; border: 1.5px solid #e2e8f0;
      padding: 7px 11px; border-radius: 9px;
      font-size: 12px; width: 180px; color: #0f172a;
      transition: border-color 120ms, box-shadow 120ms;
    }
    .search-box:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .table-wrapper { overflow-x: auto; }
    .dashboard-table { width: 100%; border-collapse: collapse; min-width: 680px; }
    .dashboard-table thead tr { background: linear-gradient(135deg,#1e40af,#2563eb); }
    .dashboard-table th {
      padding: 10px 14px; text-align: left;
      font-size: 10px; font-weight: 700; color: #bfdbfe;
      text-transform: uppercase; letter-spacing: 0.7px; white-space: nowrap;
    }
    .dashboard-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 80ms; }
    .dashboard-table tbody tr:nth-child(even) td { background: #f8fafc; }
    .dashboard-table tbody tr:hover td { background: #eff6ff !important; }
    .dashboard-table tbody tr:last-child { border-bottom: none; }
    .dashboard-table td { padding: 11px 14px; font-size: 13px; color: #1e293b; vertical-align: middle; }
    .kode-chip {
      font-family: 'Courier New', monospace; font-size: 11px; font-weight: 700;
      background: #f1f5f9; color: #334155; padding: 3px 7px;
      border-radius: 6px; border: 1px solid #e2e8f0; white-space: nowrap;
    }
    .jenis-pill { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-weight: 700; font-size: 10px; white-space: nowrap; }
    .jenis-pill.wrs { background: #dbeafe; color: #1e40af; }
    .jenis-pill.acc { background: #fef3c7; color: #92400e; }
    .jenis-pill.seis { background: #ede9fe; color: #6d28d9; }
    .status-badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px; border-radius: 999px;
      font-size: 11px; font-weight: 700;
    }
    .status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .status-badge.draft    { background: #f1f5f9; color: #475569; } .status-badge.draft::before    { background: #94a3b8; }
    .status-badge.diajukan { background: #dbeafe; color: #1e40af; } .status-badge.diajukan::before { background: #3b82f6; }
    .status-badge.diproses { background: #fff7ed; color: #c2410c; } .status-badge.diproses::before { background: #f97316; }
    .status-badge.selesai  { background: #dcfce7; color: #15803d; } .status-badge.selesai::before  { background: #22c55e; }
    .status-badge.ditolak  { background: #fee2e2; color: #b91c1c; } .status-badge.ditolak::before  { background: #ef4444; }
    .table-actions { display: flex; gap: 6px; }
    .btn-action {
      display: inline-flex; align-items: center;
      padding: 5px 11px; border-radius: 8px;
      font-size: 11px; font-weight: 700; text-decoration: none;
      transition: transform 110ms, box-shadow 110ms;
      border: none; cursor: pointer; white-space: nowrap;
    }
    .btn-action.detail { background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; box-shadow: 0 3px 10px rgba(37,99,235,0.22); }
    .btn-action.detail:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.28); }
    .btn-action.pdf    { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
    .btn-action.pdf:hover { border-color: #2563eb; color: #2563eb; }
    .no-data { padding: 28px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
  </style>
</head>
<body class="app-shell">
  <div class="mobile-topbar">
    <button id="mobileMenuBtn" class="hamburger-btn" type="button" aria-label="Buka menu">☰</button>
    <div class="mobile-title">SIMPels BMKG</div>
  </div>
  <div id="sidebarOverlay" class="sidebar-overlay"></div>
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark"></div>
        <div>
          <div class="brand-title">Laporan BMKG</div>
          <div class="brand-sub">SIMPels</div>
        </div>
      </div>
      <nav class="sidebar-nav" data-nav data-static-nav="true"><?php include __DIR__ . '/includes/nav.php'; ?></nav>
      <div class="sidebar-footer">
        <button class="sidebar-logout" data-logout>Keluar</button>
      </div>
    </aside>

    <div class="app-shell-main">
      <header class="dash-topbar">
        <div class="page-title">
          <h1>Dashboard</h1>
          <div class="subtitle">Monitor laporan real-time dengan visualisasi data mendalam</div>
        </div>
        <div class="user-box">
          <div class="user-avatar" id="user-avatar">U</div>
          <div class="user-info">
            <div class="user-info-name" id="user-profile-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
            <div class="user-info-role"><?php echo htmlspecialchars($user['role']); ?></div>
          </div>
        </div>
      </header>
      <main class="page-body">

        <!-- Hero Section -->
        <section class="hero-section">
          <div class="hero-content">
            <div class="hero-left">
              <h2>Monitoring Laporan Lapangan</h2>
              <p>Pantau progres pengiriman laporan Accelerograph, Intensitymeter, Seismograph, dan WRS NG secara real-time.</p>
              <div class="hero-actions"></div>
            </div>
            <div class="hero-metrics">
              <div class="metric-card">
                <div class="metric-label">Status Live</div>
                <div class="metric-value" id="metric-live">-</div>
              </div>
              <div class="metric-card">
                <div class="metric-label">Total Laporan</div>
                <div class="metric-value" id="metric-total">-</div>
              </div>
              <div class="metric-card">
                <div class="metric-label">Selesai Hari Ini</div>
                <div class="metric-value" id="metric-selesai">-</div>
              </div>
            </div>
          </div>
        </section>

        <!-- Status Distribution -->
        <section class="status-section">
          <div class="section-header">
            <div class="section-header-left">
              <span class="section-dot" style="background:linear-gradient(180deg,#2563eb,#60a5fa);"></span>
              <div><h3>Distribusi Status Laporan</h3><div class="section-sub">Rekap berdasarkan status</div></div>
            </div>
          </div>
          <div class="status-grid" id="status-cards"></div>
        </section>

        <!-- Jenis Distribution -->
        <section class="jenis-section">
          <div class="section-header">
            <div class="section-header-left">
              <span class="section-dot" style="background:linear-gradient(180deg,#7c3aed,#a78bfa);"></span>
              <div><h3>Distribusi Jenis Laporan</h3><div class="section-sub">Perbandingan per jenis alat</div></div>
            </div>
          </div>
          <div class="jenis-grid" id="jenis-cards"></div>
        </section>

        <!-- Latest Reports -->
        <section class="latest-section">
          <div class="latest-header">
            <div>
              <div class="latest-title">Laporan Terbaru</div>
              <div class="latest-subtitle">10 entri terbaru dari semua laporan</div>
            </div>
            <div class="latest-actions">
              <input type="text" class="search-box" id="search-table" placeholder="Cari laporan...">
              <a class="btn btn-outline" href="/laporan_bmkg/laporan_list.php" style="white-space:nowrap;">Lihat Semua →</a>
            </div>
          </div>
          <div class="table-responsive">
            <table class="dashboard-table">
              <thead>
                <tr>
                  <th>Kode Laporan</th>
                  <th>Jenis</th>
                  <th>Tanggal</th>
                  <th>Stasiun</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="latest-body"></tbody>
            </table>
          </div>
        </section>
      </main>
    </div>
  </div>

  <script src="/laporan_bmkg/assets/js/main.js"></script>
  <script>
    // Initialize dashboard
    const statusCards = document.getElementById('status-cards');
    const jenisCards = document.getElementById('jenis-cards');
    const latestBody = document.getElementById('latest-body');
    const searchTable = document.getElementById('search-table');
    const userAvatar = document.getElementById('user-avatar');

    const statusOrder  = ['draft','diajukan','diproses','selesai','ditolak'];
    const statusLabel  = { draft:'Draft', diajukan:'Diajukan', diproses:'Diproses', selesai:'Selesai', ditolak:'Ditolak' };
    const statusColors = { draft:'#94a3b8', diajukan:'#3b82f6', diproses:'#f97316', selesai:'#22c55e', ditolak:'#ef4444' };
    const statusBg     = { draft:'#f1f5f9', diajukan:'#dbeafe', diproses:'#fff7ed', selesai:'#dcfce7', ditolak:'#fee2e2' };
    const statusIcons  = { draft:'📝', diajukan:'📤', diproses:'⏳', selesai:'✅', ditolak:'❌' };

    const jenisData = {
      wrs_ng: { label: 'WRS NG', icon: '📡', class: 'wrs-ng' },
      accelerograph: { label: 'Accelerograph / Intensitymeter', icon: '📊', class: 'accelerograph' },
      seismograph: { label: 'Seismograph', icon: '🌍', class: 'seismograph' }
    };

    // Set user avatar
    function initUserAvatar() {
      const fullName = document.getElementById('user-profile-name')?.textContent || 'U';
      const initials = fullName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
      userAvatar.textContent = initials || 'U';
    }

    function renderStatus(byStatus) {
      const totalCount = Object.values(byStatus).reduce((a,b)=>a+b,0);
      statusCards.innerHTML = statusOrder.map(key => {
        const val = byStatus[key] || 0;
        const percentage = totalCount > 0 ? Math.round((val / totalCount) * 100) : 0;
        return `<div class="status-card" data-s="${key}">
          <div class="status-card-icon" style="background:${statusBg[key]};">${statusIcons[key]}</div>
          <div class="status-card-label">${statusLabel[key]}</div>
          <div class="status-card-value">${val}</div>
          <div class="status-card-sub">${percentage}% dari total</div>
        </div>`;
      }).join('');

      document.getElementById('metric-total').textContent = totalCount;
      document.getElementById('metric-selesai').textContent = byStatus['selesai'] || 0;
      document.getElementById('metric-live').textContent = 'ON';
    }

    function renderJenis(byJenis) {
      const totalCount = Object.values(byJenis).reduce((a,b)=>a+b,0);
      const keys = Object.keys(byJenis).sort();

      if (!keys.length) {
        jenisCards.innerHTML = '<div class="no-data">Tidak ada data</div>';
        return;
      }

      jenisCards.innerHTML = keys.map(k => {
        const data = jenisData[k] || { label: k, icon: '📋', class: '' };
        const val = byJenis[k] || 0;
        const percentage = totalCount > 0 ? Math.round((val / totalCount) * 100) : 0;
        return `<div class="jenis-card ${data.class}">
          <div class="jenis-card-top">
            <div class="jenis-icon-wrap">${data.icon}</div>
            <div>
              <div class="jenis-name">${data.label}</div>
              <div class="jenis-value">${val}</div>
            </div>
          </div>
          <div class="jenis-progress">
            <div class="jenis-progress-bar" style="width: ${percentage}%"></div>
          </div>
          <div class="jenis-pct">${percentage}% dari total laporan</div>
        </div>`;
      }).join('');
    }

    function statusBadge(status) {
      const label = formatStatus(status);
      return `<span class="status-badge ${status}">${label}</span>`;
    }

    async function loadStats() {
      try {
        const res = await apiRequest('/api/laporan/statistik.php');
        renderStatus(res.data.by_status || {});
        renderJenis(res.data.by_jenis || {});
      } catch (e) {
        console.warn('Gagal memuat statistik', e);
      }
    }

    async function loadLatest() {
      latestBody.innerHTML = '<tr><td colspan="6" class="no-data">Memuat data...</td></tr>';
      try {
        const res = await apiRequest('/api/laporan/list.php?limit=10');
        const rows = res.data || [];

        if (!rows.length) {
          latestBody.innerHTML = '<tr><td colspan="6" class="no-data">Tidak ada laporan</td></tr>';
          return;
        }

        latestBody.innerHTML = rows.map(row => {
          const detailUrl = `${APP_BASE_PATH}/laporan_detail.php?id=${row.id}`;
          const pdfUrl    = `${APP_BASE_PATH}/api/laporan/pdf.php?id=${row.id}`;
          const jenisCls  = row.jenis === 'wrs_ng' ? 'wrs' : row.jenis === 'accelerograph' ? 'acc' : 'seis';
          return `<tr>
            <td><span class="kode-chip">${row.kode_laporan}</span></td>
            <td><span class="jenis-pill ${jenisCls}">${formatJenis(row.jenis)}</span></td>
            <td style="white-space:nowrap;color:#475569;font-size:12px;">${formatDate(row.tanggal_laporan)}</td>
            <td style="font-weight:600;">${row.nama_stasiun || '-'}</td>
            <td>${statusBadge(row.status)}</td>
            <td>
              <div class="table-actions">
                <a class="btn-action detail" href="${detailUrl}">Detail</a>
                <a class="btn-action pdf" href="${pdfUrl}" target="_blank">PDF</a>
              </div>
            </td>
          </tr>`;
        }).join('');

        setupSearchFilter();
      } catch (e) {
        console.error('Gagal memuat laporan:', e);
        latestBody.innerHTML = '<tr><td colspan="6" class="no-data">Gagal memuat data</td></tr>';
      }
    }

    function setupSearchFilter() {
      if (!searchTable) return;

      searchTable.addEventListener('keyup', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const rows = latestBody.querySelectorAll('tr');

        rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
      });
    }

    // Initialize
    initUserAvatar();
    requireAuth().then(() => {
      loadStats();
      loadLatest();
    });
  </script>
</body>
</html>
