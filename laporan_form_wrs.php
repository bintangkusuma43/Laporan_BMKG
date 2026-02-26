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
	<title>Form Laporan WRS NG</title>
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
					<h1>Laporan Pemeliharaan Mandiri</h1>
					<div class="subtitle">Warning Receiver System New Generation (WRS NG)</div>
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
							<div><label for="tanggal_laporan">Tanggal Laporan *</label><input type="date" id="tanggal_laporan" required /></div>
							<div><label for="nomor_surat">Nomor Surat Tugas *</label><input type="text" id="nomor_surat" required /></div>
							<div><label for="kegiatan">Kegiatan *</label><input type="text" id="kegiatan" required /></div>
							<div><label for="tempat">Tempat *</label><input type="text" id="tempat" required /></div>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Lampiran PDF</div>
						<div class="section-sub">Wajib saat Generate laporan.</div>
						<div class="grid-2">
							<div><label>Upload Surat Tugas (PDF)</label><input type="file" id="upload_surat" accept="application/pdf" /></div>
							<div><label>Upload Checklist (PDF)</label><input type="file" id="upload_checklist" accept="application/pdf" /></div>
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
						<div class="section-title">Kegiatan Pemeliharaan</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-kegiatan">Tambah Kegiatan</button></div>
						<div class="table-wrap">
							<table class="line-table" id="kegiatan-table">
								<thead><tr><th style="width:70px;">No</th><th>Judul</th><th>Keterangan</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
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

					<section class="section-card">
						<div class="section-title">Dokumentasi Peralatan</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-dok-peralatan">Tambah Dokumentasi</button></div>
						<div class="table-wrap">
							<table class="line-table" id="dok-peralatan-table">
								<thead><tr><th style="width:70px;">No</th><th>Judul / Caption</th><th style="width:260px;">File</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Dokumentasi Kegiatan Pemeliharaan</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-dok-kegiatan">Tambah Dokumentasi</button></div>
						<div class="table-wrap">
							<table class="line-table" id="dok-kegiatan-table">
								<thead><tr><th style="width:70px;">No</th><th>Judul / Caption</th><th style="width:260px;">File</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<div class="actions">
						<button type="button" class="btn-save" id="btn-save">Simpan Semua Data</button>
						<button type="button" class="btn-gen" id="btn-generate">Generate Laporan</button>
					</div>
				</form>
			</main>
		</div>
	</div>

	<script src="/laporan_bmkg/assets/js/main.js"></script>
	<script>
		function reindex(selector) {
			document.querySelectorAll(`${selector} tbody tr`).forEach((row, idx) => {
				const no = row.querySelector('[data-no]');
				if (no) no.textContent = String(idx + 1);
			});
		}

		function bindRemove(btn, selector) {
			btn.addEventListener('click', () => {
				btn.closest('tr')?.remove();
				reindex(selector);
			});
		}

		function addRow(selector, html) {
			const tbody = document.querySelector(`${selector} tbody`);
			const tr = document.createElement('tr');
			tr.innerHTML = html;
			const removeBtn = tr.querySelector('.btn-remove');
			if (removeBtn) bindRemove(removeBtn, selector);
			tbody.appendChild(tr);
			reindex(selector);
		}

		function addPetugasRow() {
			addRow('#petugas-table', `
				<td data-no></td>
				<td><input type="text" class="nama" /></td>
				<td><input type="text" class="nip" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
		}

		function addKegiatanRow() {
			addRow('#kegiatan-table', `
				<td data-no></td>
				<td><input type="text" class="judul" /></td>
				<td><input type="text" class="ket" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
		}

		function addGantiRow() {
			addRow('#ganti-table', `
				<td data-no></td>
				<td><input type="text" class="nama" /></td>
				<td><input type="text" class="merk" /></td>
				<td><input type="number" class="jumlah" min="0" step="1" /></td>
				<td><input type="text" class="sn-baru" /></td>
				<td><input type="text" class="sn-lama" /></td>
				<td><input type="text" class="ket" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
		}

		function addDokRow(tableSelector) {
			addRow(tableSelector, `
				<td data-no></td>
				<td><input type="text" class="dok-cap" placeholder="Judul / Caption" /></td>
				<td><input type="file" class="dok-file" accept="image/*" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`);
		}

		function collectRows(selector, mapper) {
			return Array.from(document.querySelectorAll(`${selector} tbody tr`)).map(mapper).filter(Boolean);
		}

		function appendGeneralDocs(fd) {
			const collect = (selector) => {
				const rows = document.querySelectorAll(`${selector} tbody tr`);
				rows.forEach((row, idx) => {
					const file = row.querySelector('.dok-file')?.files?.[0];
					const cap = row.querySelector('.dok-cap')?.value?.trim();
					if (file) {
						fd.append('foto[]', file);
						fd.append('foto_caption[]', cap || file.name || `Foto ${idx + 1}`);
					}
				});
			};
			collect('#dok-peralatan-table');
			collect('#dok-kegiatan-table');
		}

		const masterLokasiCache = { promise: null, data: null };

		function loadMasterLokasi() {
			if (masterLokasiCache.data) return Promise.resolve(masterLokasiCache.data);
			if (masterLokasiCache.promise) return masterLokasiCache.promise;
			masterLokasiCache.promise = apiRequest('/api/reference/master_data.php?kind=lokasi')
				.then((res) => {
					const payload = res?.data;
					masterLokasiCache.data = Array.isArray(payload) ? payload : (payload?.lokasi || []);
					return masterLokasiCache.data;
				})
				.catch((err) => {
					console.error('Gagal memuat master lokasi', err);
					masterLokasiCache.data = [];
					return masterLokasiCache.data;
				});
			return masterLokasiCache.promise;
		}

		function ensureDatalist(id, options) {
			let dl = document.getElementById(id);
			if (!dl) {
				dl = document.createElement('datalist');
				dl.id = id;
				document.body.appendChild(dl);
			}
			dl.innerHTML = '';
			options.forEach((opt) => {
				const optionEl = document.createElement('option');
				optionEl.value = opt.value;
				if (opt.label) optionEl.label = opt.label;
				dl.appendChild(optionEl);
			});
			return dl;
		}

		function hydrateLokasiSuggestions() {
			const tempatInput = document.getElementById('tempat');
			if (!tempatInput) return;
			loadMasterLokasi()
				.then((list) => {
					const options = list.map((item) => ({ value: item, label: item }));
					ensureDatalist('lokasi-list', options);
					tempatInput.setAttribute('list', 'lokasi-list');
				})
				.catch(() => {
					/* suggestion loading failed; keep manual input */
				});
		}

		async function submitLaporan(status) {
			const form = document.getElementById('laporan-form');
			if (!form.reportValidity()) return;

			const fd = new FormData();
			fd.append('jenis', 'wrs_ng');
			fd.append('status', status);
			fd.append('tanggal_laporan', document.getElementById('tanggal_laporan').value);
			fd.append('nomor_surat', document.getElementById('nomor_surat').value.trim());

			const detail = {
				nomor_surat: document.getElementById('nomor_surat').value.trim(),
				kegiatan: document.getElementById('kegiatan').value.trim(),
				tempat: document.getElementById('tempat').value.trim(),
				petugas_pelaksana: collectRows('#petugas-table', (row) => {
					const nama = row.querySelector('.nama')?.value.trim() || '';
					const nip = row.querySelector('.nip')?.value.trim() || '';
					return nama || nip ? { nama, nip } : null;
				}),
				kegiatan_pemeliharaan: collectRows('#kegiatan-table', (row) => {
					const title = row.querySelector('.judul')?.value.trim() || '';
					const description = row.querySelector('.ket')?.value.trim() || '';
					return title || description ? { title, description } : null;
				}),
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
			const checklist = document.getElementById('upload_checklist').files?.[0];
			if (surat) fd.append('upload_surat', surat);
			if (checklist) fd.append('upload_checklist', checklist);

			appendGeneralDocs(fd);
			fd.append('detail_json', JSON.stringify(detail));

			try {
				const res = await apiRequest('/api/laporan/create.php', { method: 'POST', body: fd });
				showGlobalAlert(res.message || 'Berhasil menyimpan laporan.', 'success');
				if (res.data?.laporan_id) {
					window.location.href = `${APP_BASE_PATH}/laporan_detail.php?id=${res.data.laporan_id}`;
				}
			} catch (error) {
				showGlobalAlert(error.message || 'Gagal menyimpan laporan.', 'error');
			}
		}

		hydrateLokasiSuggestions();

		document.getElementById('add-petugas').addEventListener('click', addPetugasRow);
		document.getElementById('add-kegiatan').addEventListener('click', addKegiatanRow);
		document.getElementById('add-ganti').addEventListener('click', addGantiRow);
		const addDokPeralatanBtn = document.getElementById('add-dok-peralatan');
		if (addDokPeralatanBtn) addDokPeralatanBtn.addEventListener('click', () => addDokRow('#dok-peralatan-table'));
		const addDokKegiatanBtn = document.getElementById('add-dok-kegiatan');
		if (addDokKegiatanBtn) addDokKegiatanBtn.addEventListener('click', () => addDokRow('#dok-kegiatan-table'));
		document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
		document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));

		addPetugasRow();
		addKegiatanRow();
		addGantiRow();
		addDokRow('#dok-peralatan-table');
		addDokRow('#dok-kegiatan-table');
		requireAuth();
	</script>
</body>
</html>
