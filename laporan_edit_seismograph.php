<?php
require_once __DIR__ . '/includes/auth.php';
$user = current_user();
if (!$user) {
		header('Location: /laporan_bmkg/login.php');
		exit;
}
$laporan_id = $_GET['id'] ?? null;
if (!$laporan_id) {
		header('Location: /laporan_bmkg/laporan_list.php');
		exit;
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Edit Draft Laporan Seismograph</title>
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
					<h1>Edit Draft Laporan Seismograph</h1>
					<div class="subtitle">Perbarui data pemeliharaan seismograph</div>
				</div>
				<div class="user-box">
					<div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
					<div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
				</div>
			</header>
			<main class="page-body">
				<div id="global-alert"></div>
				<div id="loading-indicator" style="text-align:center;padding:40px;display:block;">
					<div class="loading-spinner" style="margin:0 auto;"></div>
					<p>Memuat data laporan...</p>
				</div>

				<form id="laporan-form" style="display:none;gap:16px;">
					<section class="section-card">
						<div class="section-title">Informasi Tugas</div>
						<div class="grid-2">
							<div><label for="nomor_surat_tugas">Nomor Surat Tugas *</label><input type="text" id="nomor_surat_tugas" required /></div>
							<div><label for="tanggal_pemeliharaan">Tanggal Pemeliharaan *</label><input type="date" id="tanggal_pemeliharaan" required /></div>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Data Stasiun</div>
						<div class="grid-3">
							<div><label for="nama_upt">Nama UPT</label><input type="text" id="nama_upt" /></div>
							<div><label for="nama_stasiun">Nama Stasiun</label><input type="text" id="nama_stasiun" /></div>
							<div><label for="kode_stasiun">Kode Stasiun</label><input type="text" id="kode_stasiun" /></div>
							<div><label for="koordinat">Koordinat</label><input type="text" id="koordinat" /></div>
							<div><label for="status_stasiun">Status</label><select id="status_stasiun"><option value="ON">ON</option><option value="OFF">OFF</option></select></div>
							<div><label for="tanggal_instalasi">Tanggal Instalasi</label><input type="date" id="tanggal_instalasi" /></div>
							<div><label for="kontak_nama">Kontak Person</label><input type="text" id="kontak_nama" /></div>
							<div><label for="kontak_hp">No HP Kontak</label><input type="text" id="kontak_hp" /></div>
							<div style="grid-column:1/-1;"><label for="alamat">Alamat</label><textarea id="alamat" rows="2"></textarea></div>
						</div>
					</section>

					<section class="section-card" id="section-lampiran">
						<div class="section-title">Lampiran PDF</div>
						<div class="section-sub">Upload ulang jika ingin mengganti file yang sudah ada.</div>
						<div class="grid-2">
							<div>
								<div class="label-inline"><label>Upload Surat Tugas (PDF)</label><span id="flag-surat" class="chip-filled" style="display:none;">Sudah diupload</span></div>
								<input type="file" id="upload_surat" accept="application/pdf" />
							</div>
							<div>
								<div class="label-inline"><label>Upload Checklist (PDF)</label><span id="flag-checklist" class="chip-filled" style="display:none;">Sudah diupload</span></div>
								<input type="file" id="upload_checklist" accept="application/pdf" />
							</div>
						</div>
						<div id="lampiran-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:6px;">Lampiran tersimpan (tidak akan hilang kecuali diunggah ulang)</div>
							<ul id="lampiran-existing-list" style="margin:0;padding-left:18px;display:grid;gap:6px;"></ul>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Petugas Pelaksana</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-petugas">Tambah Petugas</button></div>
						<div class="table-wrap">
							<table class="line-table" id="petugas-table">
								<thead><tr><th style="width:70px;">No</th><th>Nama</th><th>NIP</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Checklist Kondisi Peralatan</div>
						<div class="section-sub">Foto akan diganti jika upload ulang.</div>
						<div class="table-wrap">
							<table class="line-table" id="checklist-table">
								<thead><tr><th>Deskripsi</th><th>Keterangan</th><th>Foto Sebelum</th><th>Foto Sesudah</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card" id="section-digitizer">
						<div class="section-title">Kegiatan Preventif Maintenance</div>
						<div class="section-sub" style="font-weight:700;color:#0f172a;">Pembersihan</div>
						<div class="grid-2" style="margin-bottom:12px;">
							<div><label for="p_rumput">Rumput dan Dahan Pohon</label><select id="p_rumput"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="p_sarang">Sarang Laba-laba</label><select id="p_sarang"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="p_panel">Parabola dan Solar Cell</label><select id="p_panel"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="p_regulator">Solar Regulator</label><select id="p_regulator"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="p_baterai">Baterai</label><select id="p_baterai"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
						</div>

						<div class="section-sub" style="font-weight:700;color:#0f172a;">Pengukuran Power</div>
						<div style="margin-bottom:10px;">
							<div class="inline-group" style="margin-bottom:8px;">
								<label style="min-width:180px;">Detail Pengukuran Regulator</label>
								<button type="button" class="btn-add" id="add-regulator">+ Tambah Regulator</button>
								<button type="button" class="btn-del" id="remove-regulator">- Hapus Regulator</button>
							</div>
							<div id="regulator-list"></div>
						</div>

						<div style="margin-bottom:12px;">
							<div class="inline-group" style="margin-bottom:8px;">
								<label style="min-width:180px;">Detail Pengukuran Baterai</label>
								<button type="button" class="btn-add" id="add-baterai">+ Tambah Baterai</button>
								<button type="button" class="btn-del" id="remove-baterai">- Hapus Baterai</button>
							</div>
							<div id="baterai-list"></div>
						</div>

						<div class="grid-2" style="margin-bottom:12px;">
							<div><label for="tegangan_pln">Tegangan PLN (Volt)</label><input type="number" id="tegangan_pln" min="0" step="0.01" /></div>
							<div><label for="sisa_token">Sisa Token (kWH)</label><input type="number" id="sisa_token" min="0" step="0.01" /></div>
						</div>

						<div class="section-sub" style="font-weight:700;color:#0f172a;">Pengecekan Digitizer & Sensor</div>
						<div class="grid-2" style="margin-bottom:10px;">
							<div>
								<div class="label-inline"><label for="dokumentasi_digitizer">Dokumentasi Digitizer</label><span id="flag-digitizer" class="chip-filled" style="display:none;">Sudah diupload</span></div>
								<input type="file" id="dokumentasi_digitizer" accept="image/*" />
							</div>
							<div></div>
							<div><label for="centering_broadband">Centering Broadband</label><select id="centering_broadband"><option value="Tidak" selected>Tidak</option><option value="Ya">Ya</option></select></div>
							<div><label for="leveling_broadband">Leveling Sensor Broadband</label><select id="leveling_broadband"><option value="Tidak" selected>Tidak</option><option value="Ya">Ya</option></select></div>
							<div><label for="leveling_accelerometer">Leveling Sensor Accelerometer</label><select id="leveling_accelerometer"><option value="Tidak" selected>Tidak</option><option value="Ya">Ya</option></select></div>
							<div><label for="kondisi_kabel">Kondisi Kabel-Kabel</label><select id="kondisi_kabel"><option value="Baik" selected>Baik</option><option value="Cukup">Cukup</option><option value="Buruk">Buruk</option></select></div>
						</div>

						<div class="grid-2">
							<div><label for="rekomendasi">Rekomendasi</label><textarea id="rekomendasi" rows="3"></textarea></div>
							<div><label for="catatan">Catatan</label><textarea id="catatan" rows="3"></textarea></div>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Catatan Penggantian Alat</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-ganti">Tambah Penggantian</button></div>
						<div class="table-wrap">
							<table class="line-table" id="ganti-table">
								<thead><tr><th style="width:70px;">No</th><th>Nama Alat</th><th>Merk/Type</th><th>Jumlah</th><th>S/N Baru</th><th>S/N Lama</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card" id="section-dok-umum">
						<div class="section-title">Dokumentasi Umum <span id="flag-dok-umum" class="chip-filled" style="display:none;">Foto tersimpan</span></div>
						<div class="section-sub">Upload ulang foto jika ingin mengganti foto yang sudah ada.</div>
						<div id="dok-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:8px;">Foto tersimpan (tidak akan hilang kecuali diunggah ulang)</div>
							<div id="dok-existing-list" class="dok-grid"></div>
						</div>
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
	<script>
		const LAPORAN_ID = <?php echo json_encode($laporan_id); ?>;
		
		const checklistMaster = [
			{ category: 'Shelter', deskripsi: 'Kondisi Shelter 4 Sisi', parameter: 'Sisi 1 (Depan)' },
			{ category: 'Shelter', deskripsi: 'Kondisi Shelter 4 Sisi', parameter: 'Sisi 2 (Kanan)' },
			{ category: 'Shelter', deskripsi: 'Kondisi Shelter 4 Sisi', parameter: 'Sisi 3 (Belakang)' },
			{ category: 'Shelter', deskripsi: 'Kondisi Shelter 4 Sisi', parameter: 'Sisi 4 (Kiri)' },
			{ category: 'Shelter', deskripsi: 'Kondisi Solar Cell', parameter: '' },
			{ category: 'Shelter', deskripsi: 'Kondisi Antenna Parabola', parameter: '' },
			{ category: 'Sistem Power', deskripsi: 'Sistem Power', parameter: 'Accu' },
			{ category: 'Sistem Power', deskripsi: 'Sistem Power', parameter: 'Listrik PLN (Jika Ada)' },
			{ category: 'Sistem Power', deskripsi: 'Sistem Power', parameter: 'Solar Regulator' },
			{ category: 'Peralatan Seismik', deskripsi: 'Peralatan Seismik', parameter: 'Seismograf' },
			{ category: 'Peralatan Seismik', deskripsi: 'Peralatan Seismik', parameter: 'Accelerometer' },
			{ category: 'Peralatan Seismik', deskripsi: 'Peralatan Seismik', parameter: 'Modem Komunikasi' }
		];

		function toNullableNumber(value) {
			if (value === '' || value === null || value === undefined) return '';
			const num = Number(value);
			return Number.isFinite(num) ? num : '';
		}

		function escapeHtml(str) {
			return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
		}

		function lowerCaption(text) {
			return (text || '').toLowerCase();
		}

		function renderExistingLampiran(list) {
			const wrap = document.getElementById('lampiran-existing');
			const ul = document.getElementById('lampiran-existing-list');
			if (!wrap || !ul) return;
			if (!Array.isArray(list) || list.length === 0) {
				wrap.style.display = 'none';
				return;
			}
			wrap.style.display = 'block';
			ul.innerHTML = list.map((l) => {
				const label = l.lampiran_type === 'surat_tugas' ? 'Surat Tugas' : l.lampiran_type === 'checklist' ? 'Checklist' : (l.lampiran_type || 'Lampiran');
				const base = (l.file_name || '').split('/').pop();
				return `<li><a href="${APP_BASE_PATH}/uploads/${l.file_name}" target="_blank" rel="noopener">${label}${base ? ' (' + base + ')' : ''}</a></li>`;
			}).join('');
		}

		function setFlag(flagId, isOn, text) {
			const el = document.getElementById(flagId);
			if (!el) return;
			el.style.display = isOn ? 'inline-flex' : 'none';
			if (text) el.textContent = text;
		}

		function setSectionFilled(sectionId, isFilled) {
			const section = document.getElementById(sectionId);
			if (!section) return;
			section.classList.toggle('filled', Boolean(isFilled));
		}

		function markChecklistExistingPhotos(dokumentasi) {
			const docs = Array.isArray(dokumentasi) ? dokumentasi : [];
			const rows = document.querySelectorAll('#checklist-table tbody tr.checklist-item');
			rows.forEach((row) => {
				const deskripsi = (row.dataset.deskripsi || '').trim();
				const parameter = (row.dataset.parameter || '').trim();
				const base = parameter ? `${deskripsi} - ${parameter}` : deskripsi;
				const baseLower = base.toLowerCase().trim();
				const hasBefore = docs.some((d) => lowerCaption(d.caption).trim() === `${baseLower} - sebelum`);
				const hasAfter = docs.some((d) => lowerCaption(d.caption).trim() === `${baseLower} - sesudah`);
				const beforeFlag = row.querySelector('.existing-before');
				const afterFlag = row.querySelector('.existing-after');
				if (beforeFlag) beforeFlag.style.display = hasBefore ? 'inline-flex' : 'none';
				if (afterFlag) afterFlag.style.display = hasAfter ? 'inline-flex' : 'none';
				row.classList.toggle('has-existing-photo', hasBefore || hasAfter);
			});
		}

		function renderExistingDokumentasi(list) {
			const wrap = document.getElementById('dok-existing');
			const grid = document.getElementById('dok-existing-list');
			if (!wrap || !grid) return;
			if (!Array.isArray(list) || list.length === 0) {
				wrap.style.display = 'none';
				return;
			}
			wrap.style.display = 'block';
			grid.innerHTML = list.map((f) => {
				const caption = escapeHtml(f.caption || 'Tanpa caption');
				return `<div class="dok-card"><div class="dok-img" style="background-image:url('${APP_BASE_PATH}/uploads/${f.file_name}')"></div><div class="dok-caption">${caption}</div></div>`;
			}).join('');
		}

		const masterSeismoCache = {};

		function loadMasterSeismo() {
			if (masterSeismoCache.data) return Promise.resolve(masterSeismoCache.data);
			if (masterSeismoCache.promise) return masterSeismoCache.promise;
			const promise = apiRequest('/api/reference/master_data.php?kind=stasiun&tipe=Seismograph')
				.then(res => res.data || [])
				.catch(() => [])
				.then(data => {
					masterSeismoCache.data = data;
					masterSeismoCache.promise = Promise.resolve(data);
					return data;
				});
			masterSeismoCache.promise = promise;
			return promise;
		}

		function ensureDatalist(id, options) {
			let listEl = document.getElementById(id);
			if (!listEl) {
				listEl = document.createElement('datalist');
				listEl.id = id;
				document.body.appendChild(listEl);
			}
			listEl.innerHTML = options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('');
			return listEl;
		}

		function normalizeDateString(value) {
			const val = (value || '').trim();
			const full = val.match(/\d{4}-\d{2}-\d{2}/);
			if (full) return full[0];
			const yearOnly = val.match(/^(\d{4})$/);
			if (yearOnly) return `${yearOnly[1]}-01-01`;
			return '';
		}

		function clearStasiunDetails() {
			const namaUpt = document.getElementById('nama_upt');
			const koordinat = document.getElementById('koordinat');
			const tanggalInstalasi = document.getElementById('tanggal_instalasi');
			const kontakNama = document.getElementById('kontak_nama');
			const kontakHp = document.getElementById('kontak_hp');
			const alamat = document.getElementById('alamat');
			if (namaUpt) namaUpt.value = '';
			if (koordinat) koordinat.value = '';
			if (tanggalInstalasi) tanggalInstalasi.value = '';
			if (kontakNama) kontakNama.value = '';
			if (kontakHp) kontakHp.value = '';
			if (alamat) alamat.value = '';
		}

		function autoFillStasiunFromMaster() {
			const kodeInput = document.getElementById('kode_stasiun');
			const namaInput = document.getElementById('nama_stasiun');
			if (!kodeInput || !namaInput) return;
			const kode = (kodeInput.value || '').trim().toLowerCase();
			const nama = (namaInput.value || '').trim().toLowerCase();
			if (!kode && !nama) return;
			loadMasterSeismo().then(list => {
				const match = list.find(item => {
					const kodeMatch = kode && (item.kode || '').toLowerCase() === kode;
					const namaMatch = nama && (item.nama || '').toLowerCase() === nama;
					return kodeMatch || namaMatch;
				});
				if (!match) {
					clearStasiunDetails();
					showGlobalAlert('Kode/nama stasiun tidak ditemukan di master seismograph.', 'error');
					return;
				}
				const namaStasiun = document.getElementById('nama_stasiun');
				const namaUpt = document.getElementById('nama_upt');
				const koordinat = document.getElementById('koordinat');
				const tanggalInstalasi = document.getElementById('tanggal_instalasi');
				const kontakNama = document.getElementById('kontak_nama');
				const kontakHp = document.getElementById('kontak_hp');
				const alamat = document.getElementById('alamat');

				if (match.kode) kodeInput.value = match.kode;
				if (namaStasiun) namaStasiun.value = match.nama || namaStasiun.value;
				if (namaUpt) namaUpt.value = match.upt || namaUpt.value;
				if (koordinat) koordinat.value = match.koordinat || koordinat.value;
				if (kontakNama) kontakNama.value = match.kontak || kontakNama.value;
				if (kontakHp) kontakHp.value = match.hp || kontakHp.value;
				if (alamat) alamat.value = match.alamat || alamat.value;
				if (tanggalInstalasi) {
					const parsed = normalizeDateString(match.instalasi);
					if (parsed) tanggalInstalasi.value = parsed;
				}
			});
		}

		function hydrateSeismoSuggestions() {
			const kodeInput = document.getElementById('kode_stasiun');
			const namaInput = document.getElementById('nama_stasiun');
			if (!kodeInput || !namaInput) return;
			loadMasterSeismo().then(list => {
				const kodeOptions = list.map(item => ({ value: item.kode || '', label: `${item.kode || ''} - ${item.nama || ''}`.trim() }));
				const namaOptions = list.map(item => ({ value: item.nama || '', label: `${item.nama || ''} (${item.kode || ''})` }));
				ensureDatalist('stasiun-kode-list', kodeOptions);
				ensureDatalist('stasiun-nama-list', namaOptions);
				kodeInput.setAttribute('list', 'stasiun-kode-list');
				namaInput.setAttribute('list', 'stasiun-nama-list');
			});
		}

		function reindex(selector) {
			document.querySelectorAll(`${selector} tbody tr`).forEach((row, idx) => {
				const no = row.querySelector('[data-no]');
				if (no) no.textContent = String(idx + 1);
			});
		}

		function addRow(selector, html) {
			const tbody = document.querySelector(`${selector} tbody`);
			const tr = document.createElement('tr');
			tr.innerHTML = html;
			const removeBtn = tr.querySelector('.btn-remove');
			if (removeBtn) {
				removeBtn.addEventListener('click', () => {
					tr.remove();
					reindex(selector);
				});
			}
			tbody.appendChild(tr);
			reindex(selector);
		}

		function addPetugasRow(data = {}) {
			const html = `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama || ''}" /></td>
				<td><input type="text" class="nip" value="${data.nip || data.peran || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`;
			addRow('#petugas-table', html);
		}

		function renderChecklistRows() {
			const tbody = document.querySelector('#checklist-table tbody');
			tbody.innerHTML = '';
			let currentCategory = '';
			checklistMaster.forEach(item => {
				if (item.category !== currentCategory) {
					currentCategory = item.category;
					const catRow = document.createElement('tr');
					catRow.className = 'cat';
					catRow.innerHTML = `<td colspan="4">${currentCategory}</td>`;
					tbody.appendChild(catRow);
				}

				const tr = document.createElement('tr');
				tr.className = 'checklist-item';
				tr.dataset.deskripsi = item.deskripsi;
				tr.dataset.parameter = item.parameter;
				tr.innerHTML = `
					<td>${item.deskripsi}</td>
					<td>${item.parameter || '-'}</td>
					<td>
						<div class="file-stack">
							<input type="file" class="foto-sebelum" accept="image/*" />
							<span class="chip-filled existing-before" style="display:none;">Foto tersimpan</span>
						</div>
					</td>
					<td>
						<div class="file-stack">
							<input type="file" class="foto-sesudah" accept="image/*" />
							<span class="chip-filled existing-after" style="display:none;">Foto tersimpan</span>
						</div>
					</td>
				`;
				tbody.appendChild(tr);
			});
		}

		function addGantiRow(data = {}) {
			const html = `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama_alat || ''}" /></td>
				<td><input type="text" class="merk" value="${data.merk_type || ''}" /></td>
				<td><input type="number" class="jumlah" min="0" step="1" value="${data.jumlah || ''}" /></td>
				<td><input type="text" class="sn-baru" value="${data.sn_baru || ''}" /></td>
				<td><input type="text" class="sn-lama" value="${data.sn_lama || ''}" /></td>
				<td><input type="text" class="ket" value="${data.keterangan || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`;
			addRow('#ganti-table', html);
		}

		function addRegulatorRow(data = {}) {
			const container = document.getElementById('regulator-list');
			const index = container.querySelectorAll('.regulator-item').length + 1;
			const wrap = document.createElement('div');
			wrap.className = 'dynamic-card regulator-item';
			
			// Parse catatan if exists
			let inputPanel = '';
			let inputBaterai = '';
			if (data.catatan) {
				const match = data.catatan.match(/Input Panel: (.+?); Input Baterai: (.+)/);
				if (match) {
					inputPanel = match[1] === '-' ? '' : match[1];
					inputBaterai = match[2] === '-' ? '' : match[2];
				}
			}
			
			wrap.innerHTML = `
				<div class="dynamic-title">Regulator ${index}</div>
				<div class="grid-3">
					<div><label>Input Panel</label><input type="number" class="reg-input-panel" min="0" step="0.01" value="${inputPanel}" /></div>
					<div><label>Input Baterai</label><input type="number" class="reg-input-baterai" min="0" step="0.01" value="${inputBaterai}" /></div>
					<div><label>Output Beban</label><input type="number" class="reg-output-beban" min="0" step="0.01" value="${data.tegangan || ''}" /></div>
					<div><label>Foto Input Panel</label><input type="file" class="reg-foto-panel" accept="image/*" /></div>
					<div><label>Foto Input Baterai</label><input type="file" class="reg-foto-baterai" accept="image/*" /></div>
					<div><label>Foto Output Beban</label><input type="file" class="reg-foto-output" accept="image/*" /></div>
				</div>
			`;
			container.appendChild(wrap);
		}

		function removeRegulatorRow() {
			const container = document.getElementById('regulator-list');
			const rows = container.querySelectorAll('.regulator-item');
			if (rows.length > 0) {
				rows[rows.length - 1].remove();
			}
		}

		function addBateraiRow(data = {}) {
			const container = document.getElementById('baterai-list');
			const index = container.querySelectorAll('.baterai-item').length + 1;
			const wrap = document.createElement('div');
			wrap.className = 'dynamic-card baterai-item';
			wrap.innerHTML = `
				<div class="dynamic-title">Baterai ${index}</div>
				<div class="grid-3">
					<div><label>Tegangan</label><input type="number" class="bat-tegangan" min="0" step="0.01" value="${data.tegangan || ''}" /></div>
					<div><label>Kondisi</label><select class="bat-kondisi"><option value="Baik" ${data.kondisi === 'Baik' ? 'selected' : ''}>Baik</option><option value="Cukup" ${data.kondisi === 'Cukup' ? 'selected' : ''}>Cukup</option><option value="Buruk" ${data.kondisi === 'Buruk' ? 'selected' : ''}>Buruk</option></select></div>
					<div><label>Foto Baterai</label><input type="file" class="bat-foto" accept="image/*" /></div>
				</div>
			`;
			container.appendChild(wrap);
		}

		function removeBateraiRow() {
			const container = document.getElementById('baterai-list');
			const rows = container.querySelectorAll('.baterai-item');
			if (rows.length > 0) {
				rows[rows.length - 1].remove();
			}
		}

		function collectRows(selector, mapper) {
			return Array.from(document.querySelectorAll(`${selector} tbody tr`)).map(mapper).filter(Boolean);
		}

		function appendGeneralDocs(fd) {
			const files = document.getElementById('foto-umum').files || [];
			const captions = (document.getElementById('caption-umum').value || '').split(/\r?\n/).filter(Boolean);
			Array.from(files).forEach((file, idx) => {
				fd.append('foto[]', file);
				fd.append('foto_caption[]', captions[idx] || file.name);
			});
		}

		async function loadLaporanData() {
			const loadingEl = document.getElementById('loading-indicator');
			const formEl = document.getElementById('laporan-form');
			
			try {
				loadingEl.style.display = 'block';
				const res = await apiRequest(`/api/laporan/detail.php?id=${LAPORAN_ID}`);
				console.log('API Response:', res);
				
				if (!res.data || !res.data.laporan) {
					throw new Error('Data laporan tidak ditemukan');
				}
				
				const laporan = res.data.laporan;
				const detail = res.data.detail?.detail_json || {};
				const dokumentasi = res.data.dokumentasi || [];
				const lampiran = res.data.lampiran || [];
				console.log('Laporan:', laporan);
				console.log('Detail:', detail);

				if (laporan.status !== 'draft') {
					throw new Error('Hanya laporan dengan status draft yang bisa diedit');
				}

				if (laporan.jenis !== 'seismograph') {
					throw new Error('Jenis laporan tidak sesuai');
				}

				renderExistingLampiran(lampiran);
				renderExistingDokumentasi(dokumentasi);
				const hasSurat = lampiran.some((l) => l.lampiran_type === 'surat_tugas');
				const hasChecklist = lampiran.some((l) => l.lampiran_type === 'checklist');
				setFlag('flag-surat', hasSurat, 'Sudah diupload');
				setFlag('flag-checklist', hasChecklist, 'Sudah diupload');
				setSectionFilled('section-lampiran', hasSurat || hasChecklist);

				const hasDigitizerDoc = dokumentasi.some((d) => lowerCaption(d.caption).includes('digitizer'));
				const generalDocs = dokumentasi.filter((d) => !lowerCaption(d.caption).includes('digitizer'));
				const hasGeneralDocs = generalDocs.length > 0;
				setFlag('flag-digitizer', hasDigitizerDoc, 'Sudah diupload');
				setSectionFilled('section-digitizer', hasDigitizerDoc);
				setFlag('flag-dok-umum', hasGeneralDocs, 'Foto tersimpan');
				setSectionFilled('section-dok-umum', hasGeneralDocs);

				// Isi form utama
				document.getElementById('nomor_surat_tugas').value = detail.tugas?.nomor_surat_tugas || '';
				document.getElementById('tanggal_pemeliharaan').value = detail.tugas?.tanggal_pemeliharaan || laporan.tanggal_laporan || '';

				// Load stasiun
				const stasiun = detail.stasiun || {};
				document.getElementById('nama_upt').value = stasiun.nama_upt || '';
				document.getElementById('nama_stasiun').value = stasiun.nama_stasiun || '';
				document.getElementById('kode_stasiun').value = stasiun.kode_stasiun || '';
				document.getElementById('koordinat').value = stasiun.koordinat || '';
				const statusSel = document.getElementById('status_stasiun');
				statusSel.value = stasiun.status || 'ON';
				document.getElementById('tanggal_instalasi').value = stasiun.tanggal_instalasi || '';
				document.getElementById('kontak_nama').value = stasiun.kontak?.nama || '';
				document.getElementById('kontak_hp').value = stasiun.kontak?.hp || '';
				document.getElementById('alamat').value = stasiun.alamat || '';

				// Load petugas
				const petugas = detail.petugas_pelaksana || [];
				petugas.forEach(p => addPetugasRow(p));
				if (petugas.length === 0) addPetugasRow();

				// Load checklist
				renderChecklistRows();
				markChecklistExistingPhotos(dokumentasi);

				// Load pembersihan
				const p = detail.pembersihan || {};
				document.getElementById('p_rumput').value = p.rumput || 'Dilakukan';
				document.getElementById('p_sarang').value = p.sarang || 'Dilakukan';
				document.getElementById('p_panel').value = p.panel || 'Dilakukan';
				document.getElementById('p_regulator').value = p.regulator || 'Dilakukan';
				document.getElementById('p_baterai').value = p.baterai || 'Dilakukan';

				// Load pengukuran power
				const power = detail.pengukuran_power || {};
				document.getElementById('tegangan_pln').value = power.tegangan_pln || '';
				document.getElementById('sisa_token').value = power.sisa_token || '';

				// Load regulator
				const regulators = power.regulator || [];
				regulators.forEach(r => addRegulatorRow(r));
				if (regulators.length === 0) addRegulatorRow();

				// Load baterai
				const baterais = power.baterai || [];
				baterais.forEach(b => addBateraiRow(b));
				if (baterais.length === 0) addBateraiRow();

				// Load digitizer sensor
				const dig = detail.digitizer_sensor || {};
				document.getElementById('centering_broadband').value = dig.centering_broadband || 'Tidak';
				document.getElementById('leveling_broadband').value = dig.leveling_broadband || 'Tidak';
				document.getElementById('leveling_accelerometer').value = dig.leveling_accelerometer || 'Tidak';
				document.getElementById('kondisi_kabel').value = dig.kondisi_kabel || 'Baik';

				// Load rekomendasi dan catatan
				document.getElementById('rekomendasi').value = detail.rekomendasi || '';
				document.getElementById('catatan').value = detail.catatan || '';

				// Load penggantian
				const ganti = detail.catatan_penggantian || [];
				ganti.forEach(g => addGantiRow(g));
				if (ganti.length === 0) addGantiRow();

				loadingEl.style.display = 'none';
				formEl.style.display = 'grid';
			} catch (error) {
				console.error('Error loading laporan:', error);
				loadingEl.style.display = 'none';
				showGlobalAlert(error.message || 'Gagal memuat data laporan', 'error');
				setTimeout(() => {
					window.location.href = `${APP_BASE_PATH}/laporan_list.php`;
				}, 3000);
			}
		}

		async function submitLaporan(status) {
			const form = document.getElementById('laporan-form');
			if (!form.reportValidity()) return;

			const fd = new FormData();
			fd.append('id', LAPORAN_ID);
			fd.append('jenis', 'seismograph');
			fd.append('status', status);
			fd.append('tanggal_laporan', document.getElementById('tanggal_pemeliharaan').value);

			const nomorSuratTugas = document.getElementById('nomor_surat_tugas').value.trim();
			fd.append('nomor_surat', nomorSuratTugas);

			const checklist = Array.from(document.querySelectorAll('#checklist-table tbody tr.checklist-item')).map((row) => {
				const deskripsi = row.dataset.deskripsi || '';
				const parameter = row.dataset.parameter || '';
				const captionBase = parameter ? `${deskripsi} - ${parameter}` : deskripsi;

				const fotoSebelum = row.querySelector('.foto-sebelum')?.files?.[0];
				const fotoSesudah = row.querySelector('.foto-sesudah')?.files?.[0];
				if (fotoSebelum) {
					fd.append('foto[]', fotoSebelum);
					fd.append('foto_caption[]', `${captionBase || 'Checklist'} - sebelum`);
				}
				if (fotoSesudah) {
					fd.append('foto[]', fotoSesudah);
					fd.append('foto_caption[]', `${captionBase || 'Checklist'} - sesudah`);
				}

				return { deskripsi, parameter };
			});

			const detail = {
				tugas: {
					nomor_surat_tugas: nomorSuratTugas,
					tanggal_pemeliharaan: document.getElementById('tanggal_pemeliharaan').value
				},
				stasiun: {
					nama_upt: document.getElementById('nama_upt').value.trim(),
					nama_stasiun: document.getElementById('nama_stasiun').value.trim(),
					kode_stasiun: document.getElementById('kode_stasiun').value.trim(),
					koordinat: document.getElementById('koordinat').value.trim(),
					alamat: document.getElementById('alamat').value.trim(),
					tanggal_instalasi: document.getElementById('tanggal_instalasi').value,
					status: document.getElementById('status_stasiun').value,
					kontak: {
						nama: document.getElementById('kontak_nama').value.trim(),
						hp: document.getElementById('kontak_hp').value.trim()
					}
				},
				petugas_pelaksana: collectRows('#petugas-table', (row) => {
					const nama = row.querySelector('.nama')?.value.trim() || '';
					const nip = row.querySelector('.nip')?.value.trim() || '';
					return nama || nip ? { nama, nip } : null;
				}),
				checklist,
				pembersihan: {
					rumput: document.getElementById('p_rumput').value.trim(),
					sarang: document.getElementById('p_sarang').value.trim(),
					panel: document.getElementById('p_panel').value.trim(),
					regulator: document.getElementById('p_regulator').value.trim(),
					baterai: document.getElementById('p_baterai').value.trim()
				},
				pengukuran_power: {
					tegangan_pln: toNullableNumber(document.getElementById('tegangan_pln').value),
					sisa_token: toNullableNumber(document.getElementById('sisa_token').value),
					regulator: Array.from(document.querySelectorAll('#regulator-list .regulator-item')).map((row, idx) => {
						const nama = `Regulator ${idx + 1}`;
						const inputPanel = toNullableNumber(row.querySelector('.reg-input-panel')?.value || '');
						const inputBaterai = toNullableNumber(row.querySelector('.reg-input-baterai')?.value || '');
						const outputBeban = toNullableNumber(row.querySelector('.reg-output-beban')?.value || '');

						const fotoPanel = row.querySelector('.reg-foto-panel')?.files?.[0];
						const fotoBaterai = row.querySelector('.reg-foto-baterai')?.files?.[0];
						const fotoOutput = row.querySelector('.reg-foto-output')?.files?.[0];
						if (fotoPanel) {
							fd.append('foto[]', fotoPanel);
							fd.append('foto_caption[]', `${nama} - Input Panel`);
						}
						if (fotoBaterai) {
							fd.append('foto[]', fotoBaterai);
							fd.append('foto_caption[]', `${nama} - Input Baterai`);
						}
						if (fotoOutput) {
							fd.append('foto[]', fotoOutput);
							fd.append('foto_caption[]', `${nama} - Output Beban`);
						}

						if (inputPanel === '' && inputBaterai === '' && outputBeban === '') {
							return null;
						}

						return {
							nama,
							tegangan: outputBeban,
							catatan: `Input Panel: ${inputPanel === '' ? '-' : inputPanel}; Input Baterai: ${inputBaterai === '' ? '-' : inputBaterai}`
						};
					}).filter(Boolean),
					baterai: Array.from(document.querySelectorAll('#baterai-list .baterai-item')).map((row, idx) => {
						const posisi = `Baterai ${idx + 1}`;
						const tegangan = toNullableNumber(row.querySelector('.bat-tegangan')?.value || '');
						const kondisi = row.querySelector('.bat-kondisi')?.value || 'Baik';
						const foto = row.querySelector('.bat-foto')?.files?.[0];
						if (foto) {
							fd.append('foto[]', foto);
							fd.append('foto_caption[]', posisi);
						}
						if (tegangan === '' && kondisi === 'Baik') {
							return null;
						}
						return { posisi, tegangan, kondisi };
					}).filter(Boolean)
				},
				digitizer_sensor: {
					centering_broadband: document.getElementById('centering_broadband').value,
					leveling_broadband: document.getElementById('leveling_broadband').value,
					leveling_accelerometer: document.getElementById('leveling_accelerometer').value,
					kondisi_kabel: document.getElementById('kondisi_kabel').value
				},
				rekomendasi: document.getElementById('rekomendasi').value.trim(),
				catatan: document.getElementById('catatan').value.trim(),
				catatan_penggantian: collectRows('#ganti-table', (row) => {
					const item = {
						nama_alat: row.querySelector('.nama')?.value.trim() || '',
						merk_type: row.querySelector('.merk')?.value.trim() || '',
						jumlah: row.querySelector('.jumlah')?.value || '',
						sn_baru: row.querySelector('.sn-baru')?.value.trim() || '',
						sn_lama: row.querySelector('.sn-lama')?.value.trim() || '',
						keterangan: row.querySelector('.ket')?.value.trim() || ''
					};
					return Object.values(item).some(v => String(v).trim() !== '') ? item : null;
				})
			};

			const surat = document.getElementById('upload_surat').files?.[0];
			const checklistPdf = document.getElementById('upload_checklist').files?.[0];
			if (surat) fd.append('upload_surat', surat);
			if (checklistPdf) fd.append('upload_checklist', checklistPdf);

			const dokumentasiDigitizer = document.getElementById('dokumentasi_digitizer')?.files?.[0];
			if (dokumentasiDigitizer) {
				fd.append('foto[]', dokumentasiDigitizer);
				fd.append('foto_caption[]', 'Dokumentasi digitizer');
			}

			appendGeneralDocs(fd);
			fd.append('detail_json', JSON.stringify(detail));

			try {
				const res = await apiRequest('/api/laporan/update.php', { method: 'POST', body: fd });
				showGlobalAlert(res.message || 'Berhasil memperbarui laporan.', 'success');
				setTimeout(() => {
					window.location.href = `${APP_BASE_PATH}/laporan_detail.php?id=${LAPORAN_ID}`;
				}, 1000);
			} catch (error) {
				showGlobalAlert(error.message || 'Gagal memperbarui laporan.', 'error');
			}
		}

		document.getElementById('add-petugas').addEventListener('click', () => addPetugasRow());
		document.getElementById('add-regulator').addEventListener('click', () => addRegulatorRow());
		document.getElementById('remove-regulator').addEventListener('click', removeRegulatorRow);
		document.getElementById('add-baterai').addEventListener('click', () => addBateraiRow());
		document.getElementById('remove-baterai').addEventListener('click', removeBateraiRow);
		document.getElementById('add-ganti').addEventListener('click', () => addGantiRow());
		document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
		document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));

		const kodeStasiunInput = document.getElementById('kode_stasiun');
		const namaStasiunInput = document.getElementById('nama_stasiun');
		if (kodeStasiunInput) ['change', 'blur'].forEach(evt => kodeStasiunInput.addEventListener(evt, autoFillStasiunFromMaster));
		if (namaStasiunInput) ['change', 'blur'].forEach(evt => namaStasiunInput.addEventListener(evt, autoFillStasiunFromMaster));

		requireAuth();
		loadLaporanData();
		hydrateSeismoSuggestions();
	</script>
</body>
</html>
