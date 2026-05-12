<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
    header('Location: /laporan_bmkg/login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Buat Laporan - SIMPels</title>
  <link rel="stylesheet" href="/laporan_bmkg/assets/css/styles.css" />
  <style>
    body { background: linear-gradient(135deg, #f5f7fb 0%, #edf2ff 60%, #e9f4ff 100%); }
    .page-body { padding: 18px; }
    .hero { background: linear-gradient(135deg, #2c5fd4 0%, #3b82f6 40%, #0ea5e9 100%); color: #fff; border-radius: 18px; padding: 18px 20px; box-shadow: 0 18px 40px rgba(59,130,246,0.35); margin-bottom: 14px; }
    .hero h1 { margin: 0 0 6px; }
    .hero p { margin: 0; color: #e5edff; }
    .section-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:18px; box-shadow:0 12px 30px rgba(15,23,42,0.08); margin-bottom:10px; }
    .section-title { font-weight:700; margin-bottom:6px; font-size:16px; color:#0f172a; }
    .section-sub { color:#64748b; font-size:13px; margin-bottom:12px; }
    .grid-2 { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:12px; }
    .grid-3 { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:10px; }
    .stack { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    label { font-weight:600; color:#111827; }
    input[type="text"], input[type="date"], input[type="number"], textarea, select {
      border-radius:10px; border:1px solid #e2e8f0; padding:10px 12px; font-size:14px; width:100%; background:#f8fafc;
    }
    textarea { resize:vertical; }
    .form-block { display:none; }
    .form-block.is-active { display:block; }
    .table-wrap { width:100%; overflow:auto; border:1px solid #e2e8f0; border-radius:12px; }
    .line-table { width:100%; border-collapse:collapse; min-width:740px; }
    .line-table th, .line-table td { border-bottom:1px solid #e2e8f0; padding:10px; text-align:left; vertical-align:middle; }
    .line-table th { background:#f8fafc; font-size:13px; }
    .line-table tbody tr:last-child td { border-bottom:none; }
    .line-table .cat td { background:#f1f5f9; font-weight:700; }
    .btn-mini { background:#e2e8f0; color:#0f172a; border:none; border-radius:10px; padding:8px 10px; cursor:pointer; font-weight:600; }
    .actions { display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:linear-gradient(180deg, rgba(255,255,255,0.4) 0%, #fff 60%); padding:12px 0; }
    .btn-save { background:#f59e0b; color:#fff; border:none; border-radius:10px; padding:10px 14px; cursor:pointer; font-weight:700; }
    .btn-gen { background:var(--success); color:#fff; border:none; border-radius:10px; padding:10px 14px; cursor:pointer; font-weight:700; }
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
          <h1 id="heading">Buat Laporan</h1>
          <div class="subtitle" id="subtitle">Pilih jenis laporan lalu isi form sesuai jenis</div>
        </div>
        <div class="user-box">
          <div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
        </div>
      </header>

      <main class="page-body">
        <div class="hero">
          <h1 id="hero-title">Form Laporan</h1>
          <p id="hero-sub">Isi data sesuai jenis laporan</p>
        </div>

        <div id="global-alert"></div>

        <form id="laporan-form" style="display:grid;gap:16px;">
          <section class="section-card">
            <div class="section-title">Jenis Laporan</div>
            <div class="grid-2">
              <div>
                <label for="jenis">Pilih Jenis Laporan</label>
                <select id="jenis" required>
                  <option value="wrs_ng">WRS NG</option>
                  <option value="accelerograph">Accelerograph / Intensitymeter</option>
                  <option value="seismograph">Seismograph</option>
                </select>
              </div>
              <div>
                <label for="tanggal_laporan">Tanggal Laporan *</label>
                <input type="date" id="tanggal_laporan" required />
              </div>
            </div>
          </section>

          <section id="block-wrs" class="form-block section-card">
            <div class="section-title">Form WRS NG</div>
            <div class="grid-2">
              <div>
                <label for="wrs-nomor-surat">Nomor Surat Tugas *</label>
                <input type="text" id="wrs-nomor-surat" />
              </div>
              <div>
                <label for="wrs-kegiatan">Kegiatan *</label>
                <input type="text" id="wrs-kegiatan" placeholder="Pemeliharaan WRS NG" />
              </div>
              <div>
                <label for="wrs-tempat">Tempat *</label>
                <input type="text" id="wrs-tempat" />
              </div>
            </div>

            <div class="section-sub" style="margin-top:12px;">Petugas Pelaksana</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="wrs-add-petugas">Tambah Petugas</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="wrs-petugas-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama</th><th>NIP</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Rincian Kegiatan</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="wrs-add-kegiatan">Tambah Kegiatan</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="wrs-kegiatan-table">
                <thead><tr><th style="width:70px;">No</th><th>Judul</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Catatan Penggantian Alat</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="wrs-add-ganti">Tambah Penggantian</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="wrs-ganti-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama Alat</th><th>Merk/Type</th><th>Jumlah</th><th>S/N Baru</th><th>S/N Lama</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Lampiran PDF (wajib saat Generate)</div>
            <div class="grid-2">
              <div><label>Upload Surat Tugas (PDF)</label><input type="file" id="wrs-upload-surat" accept="application/pdf" /></div>
              <div><label>Upload Checklist (PDF)</label><input type="file" id="wrs-upload-checklist" accept="application/pdf" /></div>
            </div>
          </section>

          <section id="block-acc" class="form-block section-card">
            <div class="section-title">Laporan Preventif Maintenance</div>
            <div class="section-sub">Accelerograph & Intensitymeter</div>

            <div class="grid-2">
              <div>
                <label for="acc-tipe">Pilih Jenis Laporan</label>
                <select id="acc-tipe">
                  <option value="accelerograph" selected>Accelerograph</option>
                  <option value="intensitymeter">Intensitymeter</option>
                </select>
              </div>
              <div>
                <label for="acc-nomor-surat">Nomor Surat Tugas *</label>
                <input type="text" id="acc-nomor-surat" />
              </div>
              <div>
                <label for="acc-kode-site">Kode Site *</label>
                <input type="text" id="acc-kode-site" />
              </div>
              <div>
                <label for="acc-nama-site">Nama Site *</label>
                <input type="text" id="acc-nama-site" />
              </div>
              <div>
                <label for="acc-status">Status</label>
                <select id="acc-status"><option value="ON" selected>ON</option><option value="OFF">OFF</option></select>
              </div>
              <div></div>
              <div>
                <label for="acc-kerusakan">Kerusakan</label>
                <textarea id="acc-kerusakan" rows="3"></textarea>
              </div>
              <div>
                <label for="acc-rekomendasi">Rekomendasi</label>
                <textarea id="acc-rekomendasi" rows="3"></textarea>
              </div>
            </div>

            <div class="section-sub" style="margin-top:12px;">Petugas Pelaksana</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="acc-add-petugas">Tambah Petugas</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="acc-petugas-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama</th><th>NIP</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Checklist Kondisi Peralatan</div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="acc-checklist-table">
                <thead>
                  <tr><th>Deskripsi</th><th>Kondisi Sebelum</th><th>Foto Sebelum</th><th>Kondisi Sesudah</th><th>Foto Sesudah</th></tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Pelaksanaan Maintenance</div>
            <div class="grid-2" style="margin-bottom:10px;">
              <div><label for="acc-pembersihan-lingkungan">Pembersihan Lingkungan</label><select id="acc-pembersihan-lingkungan"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
              <div><label for="acc-pembersihan-peralatan">Pembersihan Peralatan</label><select id="acc-pembersihan-peralatan"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
            </div>
            <div class="grid-3">
              <div><label for="acc-sisa-token">Sisa Token (kWh)</label><input type="number" id="acc-sisa-token" min="0" step="0.01" /></div>
              <div><label for="acc-tegangan-sumber">Tegangan Sumber (Volt)</label><input type="number" id="acc-tegangan-sumber" min="0" step="0.01" /></div>
              <div><label for="acc-tegangan-stabilizer">Tegangan Output Stabilizer (Volt)</label><input type="number" id="acc-tegangan-stabilizer" min="0" step="0.01" /></div>
              <div><label for="acc-tegangan-ups">Tegangan Output UPS (Volt)</label><input type="number" id="acc-tegangan-ups" min="0" step="0.01" /></div>
              <div><label for="acc-tegangan-baterai">Tegangan Baterai (Avg) (Volt)</label><input type="number" id="acc-tegangan-baterai" min="0" step="0.01" /></div>
              <div><label for="acc-jumlah-baterai">Jumlah Baterai</label><input type="number" id="acc-jumlah-baterai" min="0" step="1" /></div>
              <div><label for="acc-ketahanan-ups">Ketahanan UPS</label><select id="acc-ketahanan-ups"><option value="Baik" selected>Baik</option><option value="Cukup">Cukup</option><option value="Buruk">Buruk</option></select></div>
            </div>

            <div class="section-sub" style="margin-top:12px;">Pengecekan Sistem Komunikasi</div>
            <div class="grid-2">
              <div><label for="acc-ping-modem">Ping IP Modem/Gateway</label><select id="acc-ping-modem"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
              <div><label for="acc-ping-digitizer">Ping IP Digitizer</label><select id="acc-ping-digitizer"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
              <div><label for="acc-ping-display">Ping PC Display</label><select id="acc-ping-display"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
              <div><label for="acc-ping-server">Ping IP Server</label><select id="acc-ping-server"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
            </div>

            <div class="section-sub" style="margin-top:12px;">Catatan Penggantian Alat</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="acc-add-ganti">Tambah Penggantian</button></div>
            <div class="table-responsive">
              <table class="line-table" id="acc-ganti-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama Alat</th><th>Merk/Type</th><th>Jumlah</th><th>S/N Baru</th><th>S/N Lama</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </section>

          <section id="block-seis" class="form-block section-card">
            <div class="section-title">Form Seismograph</div>
            <div class="grid-2">
              <div><label for="seis-nomor-surat">Nomor Surat Tugas *</label><input type="text" id="seis-nomor-surat" /></div>
              <div><label for="seis-tanggal-pemeliharaan">Tanggal Pemeliharaan *</label><input type="date" id="seis-tanggal-pemeliharaan" /></div>
            </div>

            <div class="section-sub" style="margin-top:12px;">Data Stasiun</div>
            <div class="grid-3" style="margin-bottom:12px;">
              <div><label for="seis-nama-stasiun">Nama Stasiun</label><input type="text" id="seis-nama-stasiun" /></div>
              <div><label for="seis-kode-stasiun">Kode Stasiun</label><input type="text" id="seis-kode-stasiun" /></div>
              <div><label for="seis-nama-upt">Nama UPT</label><input type="text" id="seis-nama-upt" /></div>
              <div><label for="seis-koordinat">Koordinat</label><input type="text" id="seis-koordinat" /></div>
              <div><label for="seis-status-stasiun">Status Stasiun</label><input type="text" id="seis-status-stasiun" /></div>
              <div><label for="seis-tahun-instalasi">Tanggal Instalasi</label><input type="date" id="seis-tahun-instalasi" /></div>
              <div><label for="seis-kontak-nama">Kontak Person</label><input type="text" id="seis-kontak-nama" /></div>
              <div><label for="seis-kontak-hp">No HP Kontak</label><input type="text" id="seis-kontak-hp" /></div>
              <div style="grid-column:1/-1;"><label for="seis-alamat">Alamat</label><textarea id="seis-alamat" rows="2"></textarea></div>
            </div>

            <div class="section-sub">Petugas Pelaksana</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="seis-add-petugas">Tambah Petugas</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="seis-petugas-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama</th><th>NIP</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Checklist (Sebelum/Sesudah)</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="seis-add-checklist">Tambah Checklist</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="seis-checklist-table">
                <thead><tr><th style="width:70px;">No</th><th>Deskripsi</th><th>Parameter</th><th>Foto Sebelum</th><th>Foto Sesudah</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Kegiatan & Pengukuran</div>
            <div class="grid-3" style="margin-bottom:12px;">
              <div><label for="seis-p-rumput">Pembersihan Rumput</label><input type="text" id="seis-p-rumput" placeholder="Baik / Dilakukan / -" /></div>
              <div><label for="seis-p-sarang">Pembersihan Sarang</label><input type="text" id="seis-p-sarang" placeholder="Baik / Dilakukan / -" /></div>
              <div><label for="seis-p-panel">Pembersihan Parabola/Solar Cell</label><input type="text" id="seis-p-panel" /></div>
              <div><label for="seis-p-regulator">Pembersihan Regulator</label><input type="text" id="seis-p-regulator" /></div>
              <div><label for="seis-p-baterai">Pembersihan Baterai</label><input type="text" id="seis-p-baterai" /></div>
              <div><label for="seis-tegangan-pln">Tegangan PLN (Volt)</label><input type="number" id="seis-tegangan-pln" min="0" step="0.01" /></div>
              <div><label for="seis-sisa-token">Sisa Token (kWh)</label><input type="number" id="seis-sisa-token" min="0" step="0.01" /></div>
              <div><label for="seis-centering">Centering Broadband</label><input type="text" id="seis-centering" /></div>
              <div><label for="seis-leveling-bb">Leveling Broadband</label><input type="text" id="seis-leveling-bb" /></div>
              <div><label for="seis-leveling-acc">Leveling Accelerometer</label><input type="text" id="seis-leveling-acc" /></div>
              <div><label for="seis-kabel">Kondisi Kabel</label><input type="text" id="seis-kabel" /></div>
            </div>

            <div class="section-sub">Catatan / Rekomendasi</div>
            <div class="grid-2" style="margin-bottom:12px;">
              <div><label for="seis-rekomendasi">Rekomendasi</label><textarea id="seis-rekomendasi" rows="3"></textarea></div>
              <div><label for="seis-catatan">Catatan</label><textarea id="seis-catatan" rows="3"></textarea></div>
            </div>

            <div class="section-sub">Catatan Penggantian Alat</div>
            <div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="seis-add-ganti">Tambah Penggantian</button></div>
            <div class="table-responsive" style="margin-bottom:12px;">
              <table class="line-table" id="seis-ganti-table">
                <thead><tr><th style="width:70px;">No</th><th>Nama Alat</th><th>Merk/Type</th><th>Jumlah</th><th>S/N Baru</th><th>S/N Lama</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="section-sub">Lampiran PDF (wajib saat Generate)</div>
            <div class="grid-2">
              <div><label>Upload Surat Tugas (PDF)</label><input type="file" id="seis-upload-surat" accept="application/pdf" /></div>
              <div><label>Upload Checklist (PDF)</label><input type="file" id="seis-upload-checklist" accept="application/pdf" /></div>
            </div>
          </section>

          <section class="section-card">
            <div class="section-title">Dokumentasi Umum</div>
            <div class="grid-2">
              <div><label>Upload Foto (bisa lebih dari satu)</label><input type="file" id="foto-umum" accept="image/*" multiple /></div>
              <div><label>Caption Foto (satu baris per foto)</label><textarea id="caption-umum" rows="4" placeholder="Caption 1&#10;Caption 2"></textarea></div>
            </div>
          </section>

          <div class="actions">
            <button type="button" class="btn-save" id="btn-save">Simpan Draft</button>
            <button type="button" class="btn-gen" id="btn-generate">Generate Laporan</button>
          </div>
        </form>
      </main>
    </div>
  </div>

  <script src="/laporan_bmkg/assets/js/main.js"></script>
  <script src="/laporan_bmkg/assets/js/petugasAutofill.js"></script>
  <script>
    const jenisEl = document.getElementById('jenis');
    const headingEl = document.getElementById('heading');
    const subtitleEl = document.getElementById('subtitle');
    const heroTitleEl = document.getElementById('hero-title');
    const heroSubEl = document.getElementById('hero-sub');

    const accChecklistMaster = [
      { category: 'WRSNG', items: ['Shelter', 'Sekitar Peralatan'] },
      { category: 'Sistem Kelistrikan', items: ['Stabilizer', 'UPS', 'Baterai', 'Terminal Arrester', 'LAN Arrester', 'Solar Panel', 'Regulator', 'Inverter'] },
      { category: 'Sistem Komunikasi', items: ['Modem GSM', 'Antenna Yagi', 'Modem VSAT', 'Antenna VSAT'] },
      { category: 'Peralatan Seismik', items: ['Sensor Accelerometer', 'Digitizer', 'GPS'] }
    ];

    // ========================================
    // HELPER UNTUK AUTOFILL PETUGAS
    // ========================================
    
    /**
     * Generate ID unik untuk datalist
     */
    let petugasRowCount = 0;
    function generatePetugasRowId() {
      return `petugas_row_${++petugasRowCount}_${Date.now()}`;
    }

    function toNullableNumber(value) {
      if (value === '' || value === null || value === undefined) return '';
      const num = Number(value);
      return Number.isFinite(num) ? num : '';
    }

    function reindexTable(selector) {
      document.querySelectorAll(`${selector} tbody tr`).forEach((row, i) => {
        const no = row.querySelector('[data-no]');
        if (no) no.textContent = String(i + 1);
      });
    }

    function addRow(selector, html, onCreate) {
      const tbody = document.querySelector(`${selector} tbody`);
      const tr = document.createElement('tr');
      tr.innerHTML = html;
      if (onCreate) onCreate(tr);
      tbody.appendChild(tr);
      reindexTable(selector);
      return tr;
    }

    function bindRemove(btn, selector) {
      btn.addEventListener('click', () => {
        btn.closest('tr')?.remove();
        reindexTable(selector);
      });
    }

    // Use centralized PetugasAutofill from petugasAutofill.js
    function bindPetugasAutoFill(row) {
      const namaInput = row?.querySelector('.nama');
      const nipInput = row?.querySelector('.nip');
      if (namaInput && nipInput) {
        PetugasAutofill.bindRow(namaInput, nipInput);
      }
    }

    function collectRows(selector, mapper) {
      return Array.from(document.querySelectorAll(`${selector} tbody tr`)).map(mapper).filter(Boolean);
    }

    function collectPenggantian(selector) {
      return collectRows(selector, (row) => {
        const data = {
          nama_alat: row.querySelector('.nama')?.value.trim() || '',
          merk_type: row.querySelector('.merk')?.value.trim() || '',
          jumlah: row.querySelector('.jumlah')?.value || '',
          sn_baru: row.querySelector('.sn-baru')?.value.trim() || '',
          sn_lama: row.querySelector('.sn-lama')?.value.trim() || '',
          keterangan: row.querySelector('.ket')?.value.trim() || ''
        };
        return Object.values(data).some(v => String(v).trim() !== '') ? data : null;
      });
    }

    function addPenggantianRow(selector) {
      addRow(selector, `
        <td data-no></td>
        <td><input type="text" class="nama" /></td>
        <td><input type="text" class="merk" /></td>
        <td><input type="number" class="jumlah" min="0" step="1" /></td>
        <td><input type="text" class="sn-baru" /></td>
        <td><input type="text" class="sn-lama" /></td>
        <td><input type="text" class="ket" /></td>
        <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
      `, (tr) => bindRemove(tr.querySelector('.btn-remove'), selector));
    }

    function setActiveBlock(jenis) {
      document.querySelectorAll('.form-block').forEach(el => el.classList.remove('is-active'));
      const block = document.getElementById(`block-${jenis === 'accelerograph' ? 'acc' : (jenis === 'seismograph' ? 'seis' : 'wrs')}`);
      if (block) block.classList.add('is-active');

      const map = {
        wrs_ng: { h: 'Laporan WRS NG', s: 'Form khusus pemeliharaan WRS NG', heroH: 'Form Laporan WRS NG', heroS: 'Isi data pemeliharaan WRS NG' },
        accelerograph: { h: 'Laporan Preventif Maintenance', s: 'Accelerograph & Intensitymeter', heroH: 'Laporan Preventif Maintenance', heroS: 'Accelerograph & Intensitymeter' },
        seismograph: { h: 'Laporan Seismograph', s: 'Form khusus preventif maintenance seismograph', heroH: 'Form Laporan Seismograph', heroS: 'Isi data sesuai checklist seismograph' }
      };
      const info = map[jenis] || map.wrs_ng;
      headingEl.textContent = info.h;
      subtitleEl.textContent = info.s;
      heroTitleEl.textContent = info.heroH;
      heroSubEl.textContent = info.heroS;
    }

    function initWrs() {
      document.getElementById('wrs-add-petugas').addEventListener('click', () => {
        const rowId = generatePetugasRowId();
        addRow('#wrs-petugas-table', `
          <td data-no></td>
          <td><input type="text" class="nama" id="wrs_nama_${rowId}" list="list_nama_${rowId}" autocomplete="off" /></td>
          <td><input type="text" class="nip" id="wrs_nip_${rowId}" list="list_nip_${rowId}" autocomplete="off" /></td>
          <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
        `, (tr) => {
          bindRemove(tr.querySelector('.btn-remove'), '#wrs-petugas-table');
          bindPetugasAutoFill(tr);
        });
      });

      document.getElementById('wrs-add-kegiatan').addEventListener('click', () => {
        addRow('#wrs-kegiatan-table', `
          <td data-no></td>
          <td><input type="text" class="judul" /></td>
          <td><input type="text" class="keterangan" /></td>
          <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
        `, (tr) => bindRemove(tr.querySelector('.btn-remove'), '#wrs-kegiatan-table'));
      });

      document.getElementById('wrs-add-ganti').addEventListener('click', () => addPenggantianRow('#wrs-ganti-table'));
      document.getElementById('wrs-add-petugas').click();
      document.getElementById('wrs-add-kegiatan').click();
      document.getElementById('wrs-add-ganti').click();
    }

    function initAccelerograph() {
      document.getElementById('acc-add-petugas').addEventListener('click', () => {
        const rowId = generatePetugasRowId();
        addRow('#acc-petugas-table', `
          <td data-no></td>
          <td><input type="text" class="nama" id="acc_nama_${rowId}" list="list_nama_${rowId}" autocomplete="off" /></td>
          <td><input type="text" class="nip" id="acc_nip_${rowId}" list="list_nip_${rowId}" autocomplete="off" /></td>
          <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
        `, (tr) => {
          bindRemove(tr.querySelector('.btn-remove'), '#acc-petugas-table');
          bindPetugasAutoFill(tr);
        });
      });
      document.getElementById('acc-add-ganti').addEventListener('click', () => addPenggantianRow('#acc-ganti-table'));

      document.getElementById('acc-add-petugas').click();
      document.getElementById('acc-add-ganti').click();
      renderAccChecklist();
      document.getElementById('acc-tipe').addEventListener('change', () => renderAccChecklist());
    }

    function renderAccChecklist() {
      const tbody = document.querySelector('#acc-checklist-table tbody');
      tbody.innerHTML = '';
      const tipeVal = document.getElementById('acc-tipe')?.value || 'accelerograph';
      const tipeLabel = tipeVal === 'intensitymeter' ? 'Intensitymeter' : 'Accelerograph';
      const kondisiOpt = '<option value="Baik" selected>Baik</option><option value="Rusak">Rusak</option><option value="Perlu Perbaikan">Perlu Perbaikan</option>';
      accChecklistMaster.forEach(group => {
        const categoryDisplay = group.category === 'WRSNG' ? tipeLabel : group.category;
        const cat = document.createElement('tr');
        cat.className = 'cat';
        cat.innerHTML = `<td colspan="5">${categoryDisplay}</td>`;
        tbody.appendChild(cat);
        group.items.forEach(item => {
          const tr = document.createElement('tr');
          tr.dataset.category = group.category;
          tr.dataset.label = item;
          tr.innerHTML = `
            <td>${item}</td>
            <td><select class="sebelum">${kondisiOpt}</select></td>
            <td><input type="file" class="foto-sebelum" accept="image/*" /></td>
            <td><select class="sesudah">${kondisiOpt}</select></td>
            <td><input type="file" class="foto-sesudah" accept="image/*" /></td>
          `;
          tbody.appendChild(tr);
        });
      });
    }

    function initSeismograph() {
      document.getElementById('seis-add-petugas').addEventListener('click', () => {
        const rowId = generatePetugasRowId();
        addRow('#seis-petugas-table', `
          <td data-no></td>
          <td><input type="text" class="nama" id="seis_nama_${rowId}" list="list_nama_${rowId}" autocomplete="off" /></td>
          <td><input type="text" class="nip" id="seis_nip_${rowId}" list="list_nip_${rowId}" autocomplete="off" /></td>
          <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
        `, (tr) => {
          bindRemove(tr.querySelector('.btn-remove'), '#seis-petugas-table');
          bindPetugasAutoFill(tr);
        });
      });

      document.getElementById('seis-add-checklist').addEventListener('click', () => {
        addRow('#seis-checklist-table', `
          <td data-no></td>
          <td><input type="text" class="deskripsi" /></td>
          <td><input type="text" class="parameter" /></td>
          <td><input type="file" class="foto-sebelum" accept="image/*" /></td>
          <td><input type="file" class="foto-sesudah" accept="image/*" /></td>
          <td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
        `, (tr) => bindRemove(tr.querySelector('.btn-remove'), '#seis-checklist-table'));
      });

      document.getElementById('seis-add-ganti').addEventListener('click', () => addPenggantianRow('#seis-ganti-table'));

      document.getElementById('seis-add-petugas').click();
      document.getElementById('seis-add-checklist').click();
      document.getElementById('seis-add-ganti').click();
    }

    function appendGeneralDocs(fd) {
      const files = document.getElementById('foto-umum').files || [];
      const captions = (document.getElementById('caption-umum').value || '').split(/\r?\n/).filter(Boolean);
      Array.from(files).forEach((file, idx) => {
        fd.append('foto[]', file);
        fd.append('foto_caption[]', captions[idx] || file.name);
      });
    }

    function buildWrsPayload(fd) {
      const nomor = document.getElementById('wrs-nomor-surat').value.trim();
      const kegiatan = document.getElementById('wrs-kegiatan').value.trim();
      const tempat = document.getElementById('wrs-tempat').value.trim();
      if (!nomor || !kegiatan || !tempat) {
        throw new Error('Field wajib WRS NG belum lengkap.');
      }

      fd.append('nomor_surat', nomor);
      const detail = {
        nomor_surat: nomor,
        kegiatan,
        tempat,
        petugas_pelaksana: collectRows('#wrs-petugas-table', (row) => {
          const nama = row.querySelector('.nama')?.value.trim() || '';
          const nip = row.querySelector('.nip')?.value.trim() || '';
          return nama || nip ? { nama, nip } : null;
        }),
        kegiatan_pemeliharaan: collectRows('#wrs-kegiatan-table', (row) => {
          const title = row.querySelector('.judul')?.value.trim() || '';
          const description = row.querySelector('.keterangan')?.value.trim() || '';
          return title || description ? { title, description } : null;
        }),
        catatan_penggantian: collectPenggantian('#wrs-ganti-table')
      };

      const surat = document.getElementById('wrs-upload-surat').files?.[0];
      const checklist = document.getElementById('wrs-upload-checklist').files?.[0];
      if (surat) fd.append('upload_surat', surat);
      if (checklist) fd.append('upload_checklist', checklist);

      return detail;
    }

    function buildAccPayload(fd) {
      const nomor = document.getElementById('acc-nomor-surat').value.trim();
      const kodeSite = document.getElementById('acc-kode-site').value.trim();
      const namaSite = document.getElementById('acc-nama-site').value.trim();
      if (!nomor || !kodeSite || !namaSite) {
        throw new Error('Field wajib Accelerograph belum lengkap.');
      }

      fd.append('nomor_surat', nomor);

      const kondisi = Array.from(document.querySelectorAll('#acc-checklist-table tbody tr')).filter(row => !row.classList.contains('cat')).map(row => {
        const label = row.dataset.label || '';
        const category = row.dataset.category || '';
        const sebelum = row.querySelector('.sebelum')?.value || 'Baik';
        const sesudah = row.querySelector('.sesudah')?.value || 'Baik';
        const fotoSebelum = row.querySelector('.foto-sebelum')?.files?.[0];
        const fotoSesudah = row.querySelector('.foto-sesudah')?.files?.[0];
        if (fotoSebelum) {
          fd.append('foto[]', fotoSebelum);
          fd.append('foto_caption[]', `${label} - sebelum`);
        }
        if (fotoSesudah) {
          fd.append('foto[]', fotoSesudah);
          fd.append('foto_caption[]', `${label} - sesudah`);
        }
        return { category, label, kondisi_sebelum: sebelum, kondisi_sesudah: sesudah };
      });

      return {
        tipe_laporan: document.getElementById('acc-tipe').value,
        nomor_spt: nomor,
        kode_site: kodeSite,
        nama_site: namaSite,
        deskripsi_kerusakan: document.getElementById('acc-kerusakan').value.trim(),
        rekomendasi: document.getElementById('acc-rekomendasi').value.trim(),
        status_alat: document.getElementById('acc-status').value,
        petugas_pelaksana: collectRows('#acc-petugas-table', (row) => {
          const nama = row.querySelector('.nama')?.value.trim() || '';
          const nip = row.querySelector('.nip')?.value.trim() || '';
          return nama || nip ? { nama, nip } : null;
        }),
        kondisi_peralatan: kondisi,
        pembersihan: {
          lingkungan: document.getElementById('acc-pembersihan-lingkungan').value,
          peralatan: document.getElementById('acc-pembersihan-peralatan').value
        },
        pencatatan_parameter: {
          sisa_token: toNullableNumber(document.getElementById('acc-sisa-token').value),
          tegangan_sumber: toNullableNumber(document.getElementById('acc-tegangan-sumber').value),
          tegangan_output_stabilizer: toNullableNumber(document.getElementById('acc-tegangan-stabilizer').value),
          tegangan_output_ups: toNullableNumber(document.getElementById('acc-tegangan-ups').value),
          tegangan_baterai: toNullableNumber(document.getElementById('acc-tegangan-baterai').value),
          jumlah_baterai: toNullableNumber(document.getElementById('acc-jumlah-baterai').value)
        },
        pengecekan_listrik: {
          ketahanan_ups: document.getElementById('acc-ketahanan-ups').value
        },
        pengecekan_komunikasi: {
          ping_modem: document.getElementById('acc-ping-modem').value,
          ping_digitizer: document.getElementById('acc-ping-digitizer').value,
          ping_display: document.getElementById('acc-ping-display').value,
          ping_server: document.getElementById('acc-ping-server').value
        },
        catatan_penggantian: collectPenggantian('#acc-ganti-table')
      };
    }

    function buildSeisPayload(fd) {
      const nomor = document.getElementById('seis-nomor-surat').value.trim();
      const tanggalPemeliharaan = document.getElementById('seis-tanggal-pemeliharaan').value;
      if (!nomor || !tanggalPemeliharaan) {
        throw new Error('Nomor surat tugas dan tanggal pemeliharaan Seismograph wajib diisi.');
      }

      fd.append('nomor_surat', nomor);

      const checklist = collectRows('#seis-checklist-table', (row) => {
        const deskripsi = row.querySelector('.deskripsi')?.value.trim() || '';
        const parameter = row.querySelector('.parameter')?.value.trim() || '';
        if (!deskripsi && !parameter) return null;

        const fotoSebelum = row.querySelector('.foto-sebelum')?.files?.[0];
        const fotoSesudah = row.querySelector('.foto-sesudah')?.files?.[0];
        if (fotoSebelum) {
          fd.append('foto[]', fotoSebelum);
          fd.append('foto_caption[]', `${deskripsi || 'Checklist'} - sebelum`);
        }
        if (fotoSesudah) {
          fd.append('foto[]', fotoSesudah);
          fd.append('foto_caption[]', `${deskripsi || 'Checklist'} - sesudah`);
        }

        return { deskripsi, parameter };
      });

      const surat = document.getElementById('seis-upload-surat').files?.[0];
      const checklistPdf = document.getElementById('seis-upload-checklist').files?.[0];
      if (surat) fd.append('upload_surat', surat);
      if (checklistPdf) fd.append('upload_checklist', checklistPdf);

      return {
        tugas: {
          nomor_surat_tugas: nomor,
          tanggal_pemeliharaan: tanggalPemeliharaan
        },
        stasiun: {
          nama_upt: document.getElementById('seis-nama-upt').value.trim(),
          nama_stasiun: document.getElementById('seis-nama-stasiun').value.trim(),
          kode_stasiun: document.getElementById('seis-kode-stasiun').value.trim(),
          koordinat: document.getElementById('seis-koordinat').value.trim(),
          alamat: document.getElementById('seis-alamat').value.trim(),
          tanggal_instalasi: document.getElementById('seis-tahun-instalasi').value,
          status: document.getElementById('seis-status-stasiun').value.trim(),
          kontak: {
            nama: document.getElementById('seis-kontak-nama').value.trim(),
            hp: document.getElementById('seis-kontak-hp').value.trim()
          }
        },
        petugas_pelaksana: collectRows('#seis-petugas-table', (row) => {
          const nama = row.querySelector('.nama')?.value.trim() || '';
          const nip = row.querySelector('.nip')?.value.trim() || '';
          return nama || nip ? { nama, nip } : null;
        }),
        checklist,
        pembersihan: {
          rumput: document.getElementById('seis-p-rumput').value.trim(),
          sarang: document.getElementById('seis-p-sarang').value.trim(),
          panel: document.getElementById('seis-p-panel').value.trim(),
          regulator: document.getElementById('seis-p-regulator').value.trim(),
          baterai: document.getElementById('seis-p-baterai').value.trim()
        },
        pengukuran_power: {
          tegangan_pln: toNullableNumber(document.getElementById('seis-tegangan-pln').value),
          sisa_token: toNullableNumber(document.getElementById('seis-sisa-token').value),
          regulator: [],
          baterai: []
        },
        digitizer_sensor: {
          centering_broadband: document.getElementById('seis-centering').value.trim(),
          leveling_broadband: document.getElementById('seis-leveling-bb').value.trim(),
          leveling_accelerometer: document.getElementById('seis-leveling-acc').value.trim(),
          kondisi_kabel: document.getElementById('seis-kabel').value.trim()
        },
        rekomendasi: document.getElementById('seis-rekomendasi').value.trim(),
        catatan: document.getElementById('seis-catatan').value.trim(),
        catatan_penggantian: collectPenggantian('#seis-ganti-table')
      };
    }

    async function submitLaporan(status) {
      const jenis = jenisEl.value;
      const tanggalLaporan = document.getElementById('tanggal_laporan').value;
      if (!tanggalLaporan) {
        showGlobalAlert('Tanggal laporan wajib diisi.', 'error');
        return;
      }

      const fd = new FormData();
      fd.append('jenis', jenis);
      fd.append('status', status);
      fd.append('tanggal_laporan', tanggalLaporan);

      try {
        let detail = {};
        if (jenis === 'wrs_ng') {
          detail = buildWrsPayload(fd);
        } else if (jenis === 'accelerograph') {
          detail = buildAccPayload(fd);
        } else if (jenis === 'seismograph') {
          detail = buildSeisPayload(fd);
        }

        appendGeneralDocs(fd);
        fd.append('detail_json', JSON.stringify(detail));

        const res = await apiRequest('/api/laporan/create.php', { method: 'POST', body: fd });
        showGlobalAlert(res.message || 'Berhasil menyimpan laporan.', 'success');
        if (res.data?.laporan_id) {
          window.location.href = `${APP_BASE_PATH}/laporan_detail.php?id=${res.data.laporan_id}`;
        }
      } catch (error) {
        showGlobalAlert(error.message || 'Gagal menyimpan laporan.', 'error');
      }
    }

    jenisEl.addEventListener('change', () => setActiveBlock(jenisEl.value));
    document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
    document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));

    initWrs();
    initAccelerograph();
    initSeismograph();

    const params = new URLSearchParams(window.location.search);
    const preset = params.get('jenis');
    if (preset && ['wrs_ng', 'accelerograph', 'seismograph'].includes(preset)) {
      jenisEl.value = preset;
    }

    setActiveBlock(jenisEl.value);
    requireAuth();
  </script>
</body>
</html>
