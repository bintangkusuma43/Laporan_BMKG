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
	<title>Form Laporan Seismograph</title>
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
					<h1>Laporan Preventif Maintenance</h1>
					<div class="subtitle">Stasiun Seismik</div>
				</div>
				<div class="user-box">
					<div data-user-info><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
					<div class="user-role"><?php echo htmlspecialchars($user['role']); ?></div>
				</div>
			</header>
			<main class="page-body">
				<div id="global-alert"></div>

				<form id="laporan-form" style="display:grid;gap:16px;">
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
						<div class="section-sub">Wajib saat Generate laporan.</div>
						<div class="grid-2">
							<div>
								<label>Upload Surat Tugas (PDF)</label>
								<input type="file" id="upload_surat" accept="application/pdf" />
							</div>
							<div>
								<label>Upload Checklist (PDF)</label>
								<input type="file" id="upload_checklist" accept="application/pdf" />
							</div>
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
								<label for="dokumentasi_digitizer">Dokumentasi Digitizer</label>
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
	<script>
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
			['nama_upt','koordinat','tanggal_instalasi','kontak_nama','kontak_hp','alamat'].forEach(id => {
				const el = document.getElementById(id);
				if (el) el.value = '';
			});
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
				if (match.kode) kodeInput.value = match.kode;
				const fields = { nama_stasiun: match.nama, nama_upt: match.upt, koordinat: match.koordinat, kontak_nama: match.kontak, kontak_hp: match.hp, alamat: match.alamat };
				Object.entries(fields).forEach(([id, val]) => { const el = document.getElementById(id); if (el && val) el.value = val; });
				const ti = document.getElementById('tanggal_instalasi');
				if (ti) { const p = normalizeDateString(match.instalasi); if (p) ti.value = p; }
			});
		}

		function hydrateSeismoSuggestions() {
			const kodeInput = document.getElementById('kode_stasiun');
			const namaInput = document.getElementById('nama_stasiun');
			if (!kodeInput || !namaInput) return;
			loadMasterSeismo().then(list => {
				ensureDatalist('stasiun-kode-list', list.map(i => ({ value: i.kode || '', label: `${i.kode||''} - ${i.nama||''}`.trim() })));
				ensureDatalist('stasiun-nama-list', list.map(i => ({ value: i.nama || '', label: `${i.nama||''} (${i.kode||''})` })));
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
				removeBtn.addEventListener('click', () => { tr.remove(); reindex(selector); });
			}
			tbody.appendChild(tr);
			reindex(selector);
		}

		function addPetugasRow(data = {}) {
			addRow('#petugas-table', `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama || ''}" /></td>
				<td><input type="text" class="nip" value="${data.nip || data.peran || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
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
					<td><input type="file" class="foto-sebelum" accept="image/*" /></td>
					<td><input type="file" class="foto-sesudah" accept="image/*" /></td>
				`;
				tbody.appendChild(tr);
			});
		}

		function addGantiRow(data = {}) {
			addRow('#ganti-table', `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama_alat || ''}" /></td>
				<td><input type="text" class="merk" value="${data.merk_type || ''}" /></td>
				<td><input type="number" class="jumlah" min="0" step="1" value="${data.jumlah || ''}" /></td>
				<td><input type="text" class="sn-baru" value="${data.sn_baru || ''}" /></td>
				<td><input type="text" class="sn-lama" value="${data.sn_lama || ''}" /></td>
				<td><input type="text" class="ket" value="${data.keterangan || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
		}

		function addRegulatorRow() {
			const container = document.getElementById('regulator-list');
			const index = container.querySelectorAll('.regulator-item').length + 1;
			const wrap = document.createElement('div');
			wrap.className = 'dynamic-card regulator-item';
			wrap.innerHTML = `
				<div class="dynamic-title">Regulator ${index}</div>
				<div class="grid-3">
					<div><label>Input Panel</label><input type="number" class="reg-input-panel" min="0" step="0.01" /></div>
					<div><label>Input Baterai</label><input type="number" class="reg-input-baterai" min="0" step="0.01" /></div>
					<div><label>Output Beban</label><input type="number" class="reg-output-beban" min="0" step="0.01" /></div>
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
			if (rows.length > 0) rows[rows.length - 1].remove();
		}

		function addBateraiRow() {
			const container = document.getElementById('baterai-list');
			const index = container.querySelectorAll('.baterai-item').length + 1;
			const wrap = document.createElement('div');
			wrap.className = 'dynamic-card baterai-item';
			wrap.innerHTML = `
				<div class="dynamic-title">Baterai ${index}</div>
				<div class="grid-3">
					<div><label>Tegangan</label><input type="number" class="bat-tegangan" min="0" step="0.01" /></div>
					<div><label>Kondisi</label><select class="bat-kondisi"><option value="Baik" selected>Baik</option><option value="Cukup">Cukup</option><option value="Buruk">Buruk</option></select></div>
					<div><label>Foto Baterai</label><input type="file" class="bat-foto" accept="image/*" /></div>
				</div>
			`;
			container.appendChild(wrap);
		}

		function removeBateraiRow() {
			const container = document.getElementById('baterai-list');
			const rows = container.querySelectorAll('.baterai-item');
			if (rows.length > 0) rows[rows.length - 1].remove();
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

		async function submitLaporan(status) {
			const form = document.getElementById('laporan-form');
			if (!form.reportValidity()) return;

			const fd = new FormData();
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
				if (fotoSebelum) { fd.append('foto[]', fotoSebelum); fd.append('foto_caption[]', `${captionBase || 'Checklist'} - sebelum`); }
				if (fotoSesudah) { fd.append('foto[]', fotoSesudah); fd.append('foto_caption[]', `${captionBase || 'Checklist'} - sesudah`); }
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
						if (fotoPanel) { fd.append('foto[]', fotoPanel); fd.append('foto_caption[]', `${nama} - Input Panel`); }
						if (fotoBaterai) { fd.append('foto[]', fotoBaterai); fd.append('foto_caption[]', `${nama} - Input Baterai`); }
						if (fotoOutput) { fd.append('foto[]', fotoOutput); fd.append('foto_caption[]', `${nama} - Output Beban`); }
						if (inputPanel === '' && inputBaterai === '' && outputBeban === '') return null;
						return { nama, tegangan: outputBeban, catatan: `Input Panel: ${inputPanel === '' ? '-' : inputPanel}; Input Baterai: ${inputBaterai === '' ? '-' : inputBaterai}` };
					}).filter(Boolean),
					baterai: Array.from(document.querySelectorAll('#baterai-list .baterai-item')).map((row, idx) => {
						const posisi = `Baterai ${idx + 1}`;
						const tegangan = toNullableNumber(row.querySelector('.bat-tegangan')?.value || '');
						const kondisi = row.querySelector('.bat-kondisi')?.value || 'Baik';
						const foto = row.querySelector('.bat-foto')?.files?.[0];
						if (foto) { fd.append('foto[]', foto); fd.append('foto_caption[]', posisi); }
						if (tegangan === '' && kondisi === 'Baik') return null;
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
			if (dokumentasiDigitizer) { fd.append('foto[]', dokumentasiDigitizer); fd.append('foto_caption[]', 'Dokumentasi digitizer'); }

			appendGeneralDocs(fd);
			fd.append('detail_json', JSON.stringify(detail));

						try {
								const res = await apiRequest('/api/laporan/create.php', { method: 'POST', body: fd });
								showGlobalAlert(res.message || 'Laporan berhasil disimpan.', 'success');
								const laporanId = res.data?.laporan_id;
								if (laporanId) {
									setTimeout(() => { window.location.href = `${APP_BASE_PATH}/laporan_detail.php?id=${laporanId}`; }, 1000);
								} else {
									setTimeout(() => { window.location.href = `${APP_BASE_PATH}/laporan_list.php`; }, 1000);
								}
						} catch (error) {
								showGlobalAlert(error.message || 'Gagal menyimpan laporan.', 'error');
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
		renderChecklistRows();
		addPetugasRow();
		addRegulatorRow();
		addBateraiRow();
		addGantiRow();
		hydrateSeismoSuggestions();
	</script>
</body>
</html>
