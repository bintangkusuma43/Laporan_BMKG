<?php
if (!isset($view)) {
    $view = $_GET['view'] ?? 'laporan';
}

require_once __DIR__ . '/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}

$isSpareMode = $view === 'suku_cadang';
$isGalleryMode = $view === 'galeri';

$pageTitle = 'Daftar Laporan - Laporan BMKG';
$pageHeading = 'Daftar Laporan';
$pageSubtitle = 'Filter dan unduh laporan';
if ($isSpareMode) {
    $pageTitle = 'Riwayat Suku Cadang - Laporan BMKG';
    $pageHeading = 'Riwayat Suku Cadang';
    $pageSubtitle = 'Catatan penggantian suku cadang';
}
if ($isGalleryMode) {
    $pageTitle = 'Galeri Foto - Laporan BMKG';
    $pageHeading = 'Galeri Foto';
    $pageSubtitle = 'Foto dokumentasi yang diunggah';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <style>
    /* Global topbar heading style */
    .app-topbar {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 24px;
      background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6);
      color: #eaf2ff;
      box-shadow: 0 4px 24px rgba(37, 99, 235, 0.22);
    }
    .app-topbar .page-title h1 { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; margin: 0; }
    .app-topbar .page-title .subtitle { color: #bfdbfe; margin-top: 3px; font-size: 13px; }
    .app-topbar .user-box { margin-left: auto; background: rgba(255,255,255,0.13); border: 1px solid rgba(255,255,255,0.26); border-radius: 999px !important; box-shadow: none !important; min-width: 0 !important; padding: 7px 14px !important; }
    .app-topbar .user-box > div[data-user-info] { color: #fff !important; font-size: 13px; font-weight: 700; }
    .app-topbar .user-box .user-role { color: #bfdbfe !important; font-size: 11px !important; }

    /* Shared hero heading (currently hidden; topbar used instead) */
    .page-hero {
      display: none;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #ffffff;
      border-radius: 16px;
      padding: 16px 18px;
      box-shadow: 0 14px 30px rgba(37, 99, 235, 0.18);
      border: 1px solid rgba(255, 255, 255, 0.14);
      margin-bottom: 14px;
    }
    .page-hero .hero-title { margin: 0 0 4px; font-size: 21px; font-weight: 800; color: #ffffff; }
    .page-hero .hero-sub { margin: 0; font-size: 13px; color: #e0ebff; }

    /* History (default) view */
    body.history-mode { background: radial-gradient(ellipse at 20% 10%, rgba(37,99,235,0.07) 0%, transparent 40%), radial-gradient(ellipse at 80% 80%, rgba(99,102,241,0.06) 0%, transparent 40%), #f1f5f9; }
    body.history-mode .app-topbar { padding: 18px 24px; background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6); color: #eaf2ff; box-shadow: 0 4px 24px rgba(37,99,235,0.22); }
    body.history-mode .page-title h1 { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; }
    body.history-mode .page-title .subtitle { color: #bfdbfe; margin-top: 3px; font-size: 13px; }
    body.history-mode .user-box { background: rgba(255,255,255,0.13) !important; border: 1px solid rgba(255,255,255,0.26) !important; border-radius: 999px !important; box-shadow: none !important; min-width: 0 !important; padding: 7px 14px !important; }

    body.history-mode #filter-section { margin-top: 16px; padding: 20px 22px; border-radius: 16px; background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 24px rgba(15,23,42,0.07); }
    body.history-mode #filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px 14px; align-items: end; }
    body.history-mode #filter-form label { font-size: 11px; color: #64748b; font-weight: 700; margin-bottom: 5px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    body.history-mode #filter-form input,
    body.history-mode #filter-form select { padding: 9px 11px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #f8fafc; font-size: 13px; color: #0f172a; transition: border-color 120ms, box-shadow 120ms; }
    body.history-mode #filter-form input:focus,
    body.history-mode #filter-form select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); outline: none; }
    body.history-mode #filter-form .btn.primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-weight: 700; border: none; border-radius: 10px; padding: 10px 22px; font-size: 13px; box-shadow: 0 6px 20px rgba(37,99,235,0.28); transition: transform 120ms ease, box-shadow 120ms ease; cursor: pointer; white-space: nowrap; }
    body.history-mode #filter-form .btn.primary:hover { transform: translateY(-1px); box-shadow: 0 10px 26px rgba(29,78,216,0.32); }

    body.history-mode #table-section { border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 8px 32px rgba(15,23,42,0.09); margin-top: 16px; }
    body.history-mode .table-wrapper { overflow: auto; }
    body.history-mode table.table { width: 100%; border-collapse: collapse; min-width: 820px; }
    body.history-mode table.table thead th { position: sticky; top: 0; z-index: 2; background: linear-gradient(135deg, #1e40af, #2563eb); color: #dbeafe; text-transform: uppercase; font-size: 10px; letter-spacing: 0.7px; font-weight: 700; padding: 11px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.1); white-space: nowrap; }
    body.history-mode table.table thead th .th-label { display: inline-flex; align-items: center; gap: 5px; }
    body.history-mode table.table thead th .sort-ind { opacity: 0.35; font-size: 11px; }
    body.history-mode table.table tbody tr { transition: background 80ms ease; }
    body.history-mode table.table tbody tr:nth-child(even) td { background: #f8fafc; }
    body.history-mode table.table tbody td { padding: 11px 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; }
    body.history-mode table.table tbody tr:hover td { background: #eff6ff !important; }
    body.history-mode table.table tbody tr:last-child td { border-bottom: none; }

    body.history-mode .hero-tablebar { padding: 12px 16px 11px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e8edf5; background: #f8fafc; }
    body.history-mode .muted { color: #94a3b8; font-size: 11px; }
    body.history-mode .count-badge { display: inline-flex; align-items: center; gap: 5px; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 11px; box-shadow: 0 4px 12px rgba(37,99,235,0.22); }

    /* Jenis pill per type */
    body.history-mode .pill-jenis { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-weight: 700; font-size: 10px; white-space: nowrap; }
    body.history-mode .pill-wrs { background: #e0f2fe; color: #0369a1; }
    body.history-mode .pill-acc { background: #fef3c7; color: #92400e; }
    body.history-mode .pill-seis { background: #ede9fe; color: #6d28d9; }
    body.history-mode .pill-default { background: #f1f5f9; color: #475569; }

    /* Status pills with dot */
    body.history-mode .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-weight: 700; font-size: 11px; }
    body.history-mode .status-pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    body.history-mode .status-draft { background: #f1f5f9; color: #475569; } body.history-mode .status-draft::before { background: #94a3b8; }
    body.history-mode .status-diajukan { background: #dbeafe; color: #1e40af; } body.history-mode .status-diajukan::before { background: #3b82f6; }
    body.history-mode .status-diproses { background: #fff7ed; color: #c2410c; } body.history-mode .status-diproses::before { background: #f97316; }
    body.history-mode .status-selesai { background: #dcfce7; color: #15803d; } body.history-mode .status-selesai::before { background: #22c55e; }
    body.history-mode .status-ditolak { background: #fee2e2; color: #b91c1c; } body.history-mode .status-ditolak::before { background: #ef4444; }
    body.history-mode .status-lain { background: #f1f5f9; color: #475569; } body.history-mode .status-lain::before { background: #94a3b8; }

    /* Kode laporan chip */
    body.history-mode .kode-chip { font-family: 'Courier New', monospace; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #334155; padding: 3px 7px; border-radius: 6px; border: 1px solid #e2e8f0; letter-spacing: 0.2px; white-space: nowrap; }

    /* Row action buttons */
    body.history-mode .row-actions { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    body.history-mode .btn-detail { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 8px; background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; font-size: 11px; font-weight: 700; text-decoration: none; box-shadow: 0 3px 10px rgba(37,99,235,0.22); transition: transform 110ms, box-shadow 110ms; white-space: nowrap; border: none; cursor: pointer; }
    body.history-mode .btn-detail:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.28); }
    body.history-mode .btn-edit-draft { display: inline-flex; align-items: center; gap: 4px; padding: 5px 11px; border-radius: 8px; background: #ffffff; color: #475569; font-size: 11px; font-weight: 700; text-decoration: none; border: 1.5px solid #e2e8f0; transition: border-color 110ms, color 110ms; white-space: nowrap; }
    body.history-mode .btn-edit-draft:hover { border-color: #2563eb; color: #2563eb; }
    body.history-mode .row-actions { display: flex; gap: 6px; flex-wrap: nowrap; align-items: center; }

    /* Spare-parts view: BMKG blue look */
    body.spare-mode { background: radial-gradient(circle at 18% 18%, rgba(37,99,235,0.08), transparent 26%), #eef2ff; }
    body.spare-mode .app-topbar { padding: 18px 24px; background: linear-gradient(135deg, #1e40af, #2563eb 55%, #3b82f6); color: #eaf2ff; box-shadow: 0 4px 24px rgba(37,99,235,0.22); }
    body.spare-mode .page-title h1 { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.3px; }
    body.spare-mode .page-title .subtitle { color: #bfdbfe; margin-top: 3px; font-size: 13px; }
    body.spare-mode .user-box { background: rgba(255,255,255,0.13) !important; border: 1px solid rgba(255,255,255,0.26) !important; border-radius: 999px !important; box-shadow: none !important; min-width: 0 !important; padding: 7px 14px !important; }
    body.spare-mode .count-badge { display:inline-flex; align-items:center; gap:6px; background:#e8f2ff; color:#1d4ed8; padding:6px 10px; border-radius:999px; font-weight:700; font-size:12px; margin-top:8px; }

    body.spare-mode #filter-section { padding: 20px 24px; border-radius: 16px; background: #ffffff; box-shadow: 0 16px 34px rgba(15, 23, 42, 0.08); border: 1px solid #dbe5ff; margin-bottom: 18px; }
    body.spare-mode #filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px 16px; align-items: end; }
    body.spare-mode #filter-form label { font-size: 13px; color: #0f172a; font-weight: 800; margin-bottom: 6px; display: block; }
    body.spare-mode #filter-form input,
    body.spare-mode #filter-form select { padding: 10px 12px; border-radius: 10px; border: 1px solid #d9e3f8; font-size: 13px; background: #f3f7ff; transition: border-color 120ms ease, box-shadow 120ms ease; }
    body.spare-mode #filter-form input:focus,
    body.spare-mode #filter-form select:focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.16); outline: none; }
    body.spare-mode #filter-form .btn.primary { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-weight: 700; border: none; box-shadow: 0 6px 16px rgba(37, 99, 235, 0.22); transition: transform 120ms ease, box-shadow 120ms ease; padding: 10px 24px; font-size: 13px; width: auto; align-self: flex-end; }
    body.spare-mode #filter-form .btn.primary:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(29, 78, 216, 0.26); }

    body.spare-mode #table-section { border-radius: 18px; overflow: hidden; border: 1px solid #dbe5ff; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1); background: #ffffff; }
    body.spare-mode .table-wrapper { overflow: auto; }
    body.spare-mode table.table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 820px; }
    body.spare-mode table.table thead th { position: sticky; top: 0; z-index: 2; background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #eaf2ff; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; padding: 9px 10px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.12); white-space: nowrap; }
    body.spare-mode table.table thead th .th-label { display: inline-flex; align-items: center; gap: 6px; }
    body.spare-mode table.table thead th .sort-ind { opacity: 0.4; font-size: 12px; }
    body.spare-mode table.table tbody td { padding: 8px 10px; font-size: 12px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
    body.spare-mode table.table tbody tr:hover td { background: #e8f2ff; }
    body.spare-mode table.table tbody tr:last-child td { border-bottom: none; }
    body.spare-mode .muted { color: #64748b; font-size: 12px; }
    body.spare-mode .chip { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:999px; background:#e8f2ff; color:#1d4ed8; font-weight:700; font-size:11px; }
    body.spare-mode .circle-badge { min-width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0ea5e9; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; box-shadow: 0 8px 18px rgba(14, 165, 233, 0.18); }
    body.spare-mode .tag-soft { display:inline-flex; align-items:center; padding:4px 8px; border-radius:8px; background:#0f172a; color:#e2e8f0; font-weight:700; font-size:11px; }
    body.spare-mode .btn-outline { padding:6px 12px; border-radius:8px; border:none; background: linear-gradient(135deg, #2563eb, #1d4ed8); color:#ffffff; font-weight:700; font-size:11px; transition: all 120ms ease; display:inline-flex; align-items:center; gap:5px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.22); text-decoration: none; white-space: nowrap; }
    body.spare-mode .btn-outline:hover { box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22); transform: translateY(-1px); }
    body.spare-mode .empty-state { padding: 28px; text-align: center; color: #6b7280; font-size: 13px; }
    body.spare-mode .hero-tablebar { padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e7eb; background: linear-gradient(180deg, #e8f2ff 0%, #dfe8ff 100%); }
    body.spare-mode .page-hero { display: none; }

    /* Gallery view */
    body.gallery-mode .page-body { padding-top: 12px; }
    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
    }
    .gallery-item {
      display: flex;
      flex-direction: column;
      gap: 8px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }
    .gallery-thumb {
      width: 100%;
      aspect-ratio: 4 / 3;
      object-fit: cover;
      background: #f8fafc;
    }
    .gallery-meta {
      padding: 10px 12px 12px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      font-size: 12px;
      color: #475569;
    }
    .gallery-title {
      font-weight: 700;
      color: #0f172a;
      font-size: 13px;
    }
    .pill-soft {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #eef2ff;
      color: #4338ca;
      padding: 4px 8px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 11px;
    }
    .gallery-empty {
      padding: 24px;
      text-align: center;
      color: #64748b;
      font-size: 13px;
    }
  </style>
</head>
<body class="app-shell<?= $isSpareMode ? ' spare-mode' : ''; ?><?= $isGalleryMode ? ' gallery-mode' : ''; ?><?= (!$isSpareMode && !$isGalleryMode) ? ' history-mode' : ''; ?>">
  <div class="app-layout">
    <aside class="app-sidebar">
      <div class="sidebar-brand">
        <div class="brand-mark"></div>
        <div>
          <div class="brand-title">Laporan BMKG</div>
          <div class="brand-sub">Pelaporan</div>
        </div>
      </div>
      <nav class="sidebar-nav" data-nav data-static-nav="true"><?php include __DIR__ . '/nav.php'; ?></nav>
      <div class="sidebar-footer">
        <button class="sidebar-logout" data-logout>Keluar</button>
      </div>
    </aside>
    <div class="app-shell-main">
      <header class="app-topbar">
        <div class="page-title">
          <h1 id="page-heading"><?= htmlspecialchars($pageHeading); ?></h1>
          <div class="subtitle" id="page-subtitle"><?= htmlspecialchars($pageSubtitle); ?></div>
        </div>
        <div class="user-box">
          <div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
        </div>
      </header>
      <main class="page-body">
        <div id="global-alert"></div>
        <section class="page-hero" id="page-hero">
          <div class="hero-title" id="hero-title-text"><?= htmlspecialchars($pageHeading); ?></div>
          <div class="hero-sub" id="hero-subtitle-text"><?= htmlspecialchars($pageSubtitle); ?></div>
        </section>
        <section class="card" id="filter-section">
          <form id="filter-form" class="form-grid">
            <div>
              <label>Jenis</label>
              <select id="filter-jenis">
                <option value="">Semua</option>
                <option value="wrs_ng">WRS NG</option>
                <option value="accelerograph">Accelerograph / Intensitymeter</option>
                <option value="seismograph">Seismograph</option>
              </select>
            </div>
            <div>
              <label>Status</label>
              <select id="filter-status">
                <option value="">Semua</option>
                <option value="draft">Draft</option>
                <option value="diajukan">Diajukan</option>
                <option value="diproses">Diproses</option>
                <option value="selesai">Selesai</option>
                <option value="ditolak">Ditolak</option>
              </select>
            </div>
            <div>
              <label>Tanggal mulai</label>
              <input type="date" id="filter-mulai" />
            </div>
            <div>
              <label>Tanggal selesai</label>
              <input type="date" id="filter-selesai" />
            </div>
            <div>
              <label>Cari</label>
              <input type="text" id="filter-search" placeholder="Cari" />
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
              <button type="submit" class="btn primary">Terapkan</button>
            </div>
          </form>
        </section>

        <section class="card" id="table-section" style="padding:0;overflow:hidden;">
          <div class="hero-tablebar">
            <div style="display:flex;align-items:center;gap:10px;">
              <strong>Hasil</strong>
              <span id="table-count" class="count-badge" style="display:none;"></span>
            </div>
            <div id="table-hint" class="muted" style="color:#64748b;font-size:13px;">Memuat...</div>
          </div>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr id="table-head-row" style="background:#f8fafc;"></tr>
              </thead>
              <tbody id="table-body"></tbody>
            </table>
          </div>
        </section>

        <section class="card" id="gallery-card" style="padding:16px; display:none;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div><strong>Galeri Foto</strong></div>
            <div id="gallery-hint" class="muted" style="color:#64748b;font-size:13px;">Memuat...</div>
          </div>
          <div id="gallery-grid" class="gallery-grid"></div>
          <div id="gallery-empty" class="gallery-empty" style="display:none;">Belum ada foto yang diunggah.</div>
        </section>
      </main>
    </div>
  </div>
  <script src="/laporan_bmkg/assets/js/main.js"></script>
  <script>
    const view = "<?= $view; ?>";
    const isSpareMode = view === 'suku_cadang';
    const isGalleryMode = view === 'galeri';
    const presetJenis = !isSpareMode && !isGalleryMode ? (new URLSearchParams(window.location.search).get('jenis')) : null;
    const tbody = document.getElementById('table-body');
    const hint = document.getElementById('table-hint');
    const headingEl = document.getElementById('page-heading');
    const subtitleEl = document.getElementById('page-subtitle');
    const heroTitleEl = document.getElementById('hero-title-text');
    const heroSubtitleEl = document.getElementById('hero-subtitle-text');
    const headRow = document.getElementById('table-head-row');
    const filterSection = document.getElementById('filter-section');
    const tableSection = document.getElementById('table-section');
    const galleryCard = document.getElementById('gallery-card');
    const galleryGrid = document.getElementById('gallery-grid');
    const galleryHint = document.getElementById('gallery-hint');
    const galleryEmpty = document.getElementById('gallery-empty');
    const jenisSelect = document.getElementById('filter-jenis');
    const statusField = document.getElementById('filter-status').closest('div');
    const mulaiField = document.getElementById('filter-mulai').closest('div');
    const selesaiField = document.getElementById('filter-selesai').closest('div');
    const searchInput = document.getElementById('filter-search');

    const labelMap = {
      wrs_ng: 'Laporan WRS NG',
      accelerograph: 'Laporan Accelerograph / Intensitymeter',
      seismograph: 'Laporan Seismograph'
    };
    const subMap = {
      wrs_ng: 'Riwayat laporan WRS NG',
      accelerograph: 'Riwayat laporan Accelerograph / Intensitymeter',
      seismograph: 'Riwayat laporan Seismograph'
    };

    if (presetJenis) {
      jenisSelect.value = presetJenis;
      if (headingEl) headingEl.textContent = labelMap[presetJenis] || 'Daftar Laporan';
      if (subtitleEl) subtitleEl.textContent = subMap[presetJenis] || 'Filter dan unduh laporan';
      if (heroTitleEl) heroTitleEl.textContent = labelMap[presetJenis] || 'Daftar Laporan';
      if (heroSubtitleEl) heroSubtitleEl.textContent = subMap[presetJenis] || 'Filter dan unduh laporan';
    }

    if (isGalleryMode) {
      if (filterSection) filterSection.style.display = 'none';
      if (tableSection) tableSection.style.display = 'none';
      if (galleryCard) galleryCard.style.display = 'block';
    }

    if (isSpareMode) {
      searchInput.placeholder = 'Cari site/alat/SN/kode laporan';
      statusField.style.display = 'none';
      mulaiField.style.display = 'none';
      selesaiField.style.display = 'none';
    }

    const spareColumns = [
      { key: 'kode_laporan', label: 'Kode' },
      { key: 'jenis', label: 'Jenis', format: formatJenis },
      { key: 'tanggal_laporan', label: 'Tanggal', format: formatDate },
      { key: 'nama_site', label: 'Site / Kegiatan' },
      { key: 'kode_site', label: 'Kode Site' },
      { key: 'lokasi', label: 'Lokasi' },
      { key: 'nama_alat', label: 'Nama Alat' },
      { key: 'merk_type', label: 'Merk/Type' },
      { key: 'sn_baru', label: 'S/N Baru' },
      { key: 'sn_lama', label: 'S/N Lama' },
      { key: 'jumlah', label: 'Jumlah' },
      { key: 'keterangan', label: 'Keterangan' },
      { key: 'aksi', label: 'Aksi' },
    ];

    const defaultColumns = [
      { key: 'kode_laporan', label: 'Kode' },
      { key: 'jenis', label: 'Jenis', format: formatJenis },
      { key: 'tanggal_laporan', label: 'Tanggal', format: formatDate },
      { key: 'status', label: 'Status', format: statusBadge },
      { key: 'nama_stasiun', label: 'Stasiun' },
      { key: 'nama_pelapor', label: 'Pelapor' },
      { key: 'aksi', label: 'Aksi' },
    ];

    function renderHead(columns) {
      headRow.innerHTML = columns.map(col => `<th><span class="th-label">${col.label}<span class="sort-ind">⇅</span></span></th>`).join('');
    }

    function statusBadge(status) {
      const label = formatStatus(status);
      const cls = `status-pill status-${status || 'lain'}`;
      return `<span class="${cls}">${label}</span>`;
    }

    async function loadGallery() {
      if (!galleryGrid || !galleryHint || !galleryEmpty) return;
      galleryHint.textContent = 'Memuat...';
      galleryGrid.innerHTML = '';
      galleryEmpty.style.display = 'none';

      try {
        const res = await apiRequest('/api/laporan/gallery.php');
        const items = res.data || [];
        galleryHint.textContent = items.length + ' foto';
        if (!items.length) {
          galleryEmpty.style.display = 'block';
          return;
        }

        galleryGrid.innerHTML = items.map(item => {
          const tanggal = formatDate(item.uploaded_at);
          const jenis = formatJenis(item.jenis);
          const kode = item.kode_laporan || '-';
          const caption = item.caption || 'Tanpa keterangan';
          const url = item.url;
          return `<div class="gallery-item">
            <img class="gallery-thumb" src="${url}" alt="Foto ${kode}" loading="lazy" />
            <div class="gallery-meta">
              <div class="gallery-title">${caption}</div>
              <div>${kode} &bull; ${jenis}</div>
              <div class="pill-soft">${tanggal}</div>
            </div>
          </div>`;
        }).join('');
      } catch (error) {
        console.error(error);
        galleryHint.textContent = 'Gagal memuat';
        galleryEmpty.style.display = 'block';
        galleryEmpty.textContent = error.message || 'Gagal memuat galeri';
        showGlobalAlert(error.message || 'Gagal memuat galeri', 'error');
      }
    }

    async function loadData() {
      const columns = isSpareMode ? spareColumns : defaultColumns;
      renderHead(columns);
      const colSpan = columns.length;
      tbody.innerHTML = `<tr><td colspan="${colSpan}" style="padding:12px;">Memuat...</td></tr>`;
      hint.textContent = 'Memuat...';

      const params = new URLSearchParams();
      const jenis = jenisSelect.value;
      const status = document.getElementById('filter-status').value;
      const mulai = document.getElementById('filter-mulai').value;
      const selesai = document.getElementById('filter-selesai').value;
      const search = searchInput.value.trim();
      if (jenis) params.append('jenis', jenis);
      if (!isSpareMode) {
        if (status) params.append('status', status);
        if (mulai) params.append('tanggal_mulai', mulai);
        if (selesai) params.append('tanggal_selesai', selesai);
      }
      if (search) params.append('search', search);
      if (!isSpareMode) params.append('limit', '200');

      const endpoint = isSpareMode ? '/api/laporan/suku_cadang.php' : '/api/laporan/list.php';

      try {
        const res = await apiRequest(endpoint + '?' + params.toString());
        const rows = res.data || [];
        const totalText = rows.length + (isSpareMode ? ' catatan' : ' laporan');
        hint.textContent = totalText;
        const countEl = document.getElementById('table-count');
        if (countEl) {
          countEl.textContent = totalText;
          countEl.style.display = 'inline-flex';
        }
        if (!rows.length) {
          tbody.innerHTML = `<tr><td colspan="${colSpan}" style="padding:12px;"><div class="empty-state">Tidak ada data</div></td></tr>`;
          return;
        }

        tbody.innerHTML = rows.map(row => {
          if (isSpareMode) {
            const detailUrl = `${APP_BASE_PATH}/laporan_detail.php?id=${row.laporan_id}`;
            const siteName = row.nama_site || '-';
            const kodeSite = row.kode_site || '-';
            const lokasi = row.lokasi || '-';
            const merk = row.merk_type || '-';
            const jumlah = row.jumlah ?? '-';
            const snBaru = row.sn_baru || '-';
            const snLama = row.sn_lama || '-';
            const ket = row.keterangan || '-';
            return `<tr>
              <td>${row.kode_laporan}</td>
              <td>${formatJenis(row.jenis)}</td>
              <td>${formatDate(row.tanggal_laporan)}</td>
              <td>
                <div style="font-weight:700;color:#0f172a;">${siteName}</div>
                <div class="muted">${row.jenis ? formatJenis(row.jenis) : ''}</div>
              </td>
              <td>${kodeSite !== '-' ? `<span class="tag-soft">${kodeSite}</span>` : '-'}</td>
              <td>${lokasi}</td>
              <td>
                <div style="font-weight:700;color:#0f172a;">${row.nama_alat || '-'}</div>
                <div class="muted">${merk}</div>
              </td>
              <td>${merk}</td>
              <td>${snBaru}</td>
              <td>${snLama}</td>
              <td style="text-align:center;">${jumlah === '-' ? '-' : `<span class="circle-badge">${jumlah}</span>`}</td>
              <td>${ket}</td>
              <td><div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;"><a class="btn-outline" href="${detailUrl}">Detail</a></div></td>
            </tr>`;
          }

          const detailUrl = `${APP_BASE_PATH}/laporan_detail.php?id=${row.id}`;
          let editUrl = '';
          if (row.jenis === 'wrs_ng') {
            editUrl = `${APP_BASE_PATH}/laporan_edit_wrs.php?id=${row.id}`;
          } else if (row.jenis === 'accelerograph') {
            editUrl = `${APP_BASE_PATH}/laporan_edit_accelerograph.php?id=${row.id}`;
          } else if (row.jenis === 'seismograph') {
            editUrl = `${APP_BASE_PATH}/laporan_edit_seismograph.php?id=${row.id}`;
          }
          const editBtn = row.status === 'draft' && editUrl
            ? `<a class="btn-edit-draft" href="${editUrl}">✏ Edit</a>`
            : '';

          const kegiatan = row.jenis === 'wrs_ng' ? (row.kegiatan || '-') : '';
          const stasiunCell = row.jenis === 'wrs_ng'
            ? `<div style="font-weight:700;color:#0f172a;">${row.nama_stasiun || '-'}</div><div class="muted">Kegiatan: ${kegiatan}</div>`
            : `<div style="font-weight:700;color:#0f172a;">${row.nama_stasiun || '-'}</div>`;

          const jenisPillClass = row.jenis === 'wrs_ng' ? 'pill-wrs' : row.jenis === 'accelerograph' ? 'pill-acc' : row.jenis === 'seismograph' ? 'pill-seis' : 'pill-default';

          return `<tr>
            <td><span class="kode-chip">${row.kode_laporan}</span></td>
            <td><span class="pill-jenis ${jenisPillClass}">${formatJenis(row.jenis)}</span></td>
            <td style="white-space:nowrap;color:#475569;font-size:12px;">${formatDate(row.tanggal_laporan)}</td>
            <td>${statusBadge(row.status)}</td>
            <td>${stasiunCell}</td>
            <td style="color:#475569;font-size:12px;">${row.nama_pelapor || '-'}</td>
            <td class="row-actions">
              <a class="btn-detail" href="${detailUrl}">Detail</a>
              ${editBtn}
            </td>
          </tr>`;
        }).join('');
      } catch (error) {
        console.error(error);
        hint.textContent = 'Gagal memuat';
        tbody.innerHTML = `<tr><td colspan="${colSpan}" style="padding:12px;">Gagal memuat data</td></tr>`;
        showGlobalAlert(error.message || 'Gagal memuat', 'error');
      }
    }

    if (!isGalleryMode) {
      document.getElementById('filter-form').addEventListener('submit', e => {
        e.preventDefault();
        loadData();
      });
    }

    requireAuth().then(() => {
      if (isGalleryMode) {
        loadGallery();
      } else {
        loadData();
      }
    });
  </script>
</body>
</html>
