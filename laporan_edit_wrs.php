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
	<title>Edit Draft Laporan WRS NG</title>
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
					<h1>Edit Draft Laporan WRS NG</h1>
					<div class="subtitle">Perbarui data pemeliharaan WRS NG</div>
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
							<div><label for="tanggal_laporan">Tanggal Laporan *</label><input type="date" id="tanggal_laporan" required /></div>
							<div><label for="nomor_surat">Nomor Surat Tugas *</label><input type="text" id="nomor_surat" required /></div>
							<div><label for="kegiatan">Kegiatan *</label><input type="text" id="kegiatan" required /></div>
							<div><label for="tempat">Tempat *</label><input type="text" id="tempat" required /></div>
						</div>
					</section>

					<section class="section-card" id="section-lampiran-wrs">
						<div class="section-title">Lampiran PDF</div>
						<div class="section-sub">Wajib saat Generate laporan.</div>
						<div class="grid-2">
							<div>
								<div class="label-inline"><label>Upload Surat Tugas (PDF)</label><span id="flag-surat-wrs" class="chip-filled" style="display:none;">Sudah diupload</span></div>
								<input type="file" id="upload_surat" accept="application/pdf" />
							</div>
							<div>
								<div class="label-inline"><label>Upload Checklist (PDF)</label><span id="flag-checklist-wrs" class="chip-filled" style="display:none;">Sudah diupload</span></div>
								<input type="file" id="upload_checklist" accept="application/pdf" />
							</div>
						</div>
						<div id="lampiran-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:6px;">Lampiran tersimpan (tetap ada kecuali diunggah ulang)</div>
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

					<section class="section-card" id="section-dok-peralatan">
						<div class="section-title">Dokumentasi Peralatan <span id="flag-dok-peralatan" class="chip-filled" style="display:none;">Foto tersimpan</span></div>
						<div id="dok-peralatan-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:8px;">Foto tersimpan (tidak hilang kecuali diunggah ulang)</div>
							<div id="dok-peralatan-existing-list" class="dok-grid"></div>
						</div>
						<div class="stack" style="margin-bottom:8px;"><button type="button" class="btn-mini" id="add-dok-peralatan">Tambah Dokumentasi</button></div>
						<div class="table-wrap">
							<table class="line-table" id="dok-peralatan-table">
								<thead><tr><th style="width:70px;">No</th><th>Judul / Caption</th><th style="width:260px;">File</th><th style="width:140px;">Aksi</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card" id="section-dok-kegiatan">
						<div class="section-title">Dokumentasi Kegiatan Pemeliharaan <span id="flag-dok-kegiatan" class="chip-filled" style="display:none;">Foto tersimpan</span></div>
						<div id="dok-kegiatan-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:8px;">Foto tersimpan (tidak hilang kecuali diunggah ulang)</div>
							<div id="dok-kegiatan-existing-list" class="dok-grid"></div>
						</div>
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
		const LAPORAN_ID = <?php echo json_encode($laporan_id); ?>;

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

		function addPetugasRow(data = {}) {
			const html = `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama || ''}" /></td>
				<td><input type="text" class="nip" value="${data.nip || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`;
			addRow('#petugas-table', html);
		}

		function addKegiatanRow(data = {}) {
			const html = `
				<td data-no></td>
				<td><input type="text" class="judul" value="${data.title || ''}" /></td>
				<td><input type="text" class="ket" value="${data.description || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`;
			addRow('#kegiatan-table', html);
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

		function escapeHtml(str) {
			return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
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

		function renderExistingDocs(list, wrapId, gridId) {
			const wrap = document.getElementById(wrapId);
			const grid = document.getElementById(gridId);
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

		function appendDocsByTable(fd, tableSelector) {
			const rows = document.querySelectorAll(`${tableSelector} tbody tr`);
			rows.forEach((row, idx) => {
				const file = row.querySelector('.dok-file')?.files?.[0];
				const cap = row.querySelector('.dok-cap')?.value?.trim();
				if (file) {
					fd.append('foto[]', file);
					fd.append('foto_caption[]', cap || file.name || `Foto ${idx + 1}`);
				}
			});
		}

		function appendGeneralDocs(fd) {
			appendDocsByTable(fd, '#dok-peralatan-table');
			appendDocsByTable(fd, '#dok-kegiatan-table');
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

				// Validasi status draft
				if (laporan.status !== 'draft') {
					throw new Error('Hanya laporan dengan status draft yang bisa diedit');
				}

				// Validasi jenis
				if (laporan.jenis !== 'wrs_ng') {
					throw new Error('Jenis laporan tidak sesuai');
				}

				renderExistingLampiran(lampiran);
				// Tampilkan dokumentasi tersimpan satu kali saja agar tidak terlihat dobel
				renderExistingDocs(dokumentasi, 'dok-peralatan-existing', 'dok-peralatan-existing-list');
				const dokKegiatanWrap = document.getElementById('dok-kegiatan-existing');
				if (dokKegiatanWrap) dokKegiatanWrap.style.display = 'none';

				const hasSurat = lampiran.some((l) => l.lampiran_type === 'surat_tugas');
				const hasChecklist = lampiran.some((l) => l.lampiran_type === 'checklist');
				setFlag('flag-surat-wrs', hasSurat, 'Sudah diupload');
				setFlag('flag-checklist-wrs', hasChecklist, 'Sudah diupload');
				setSectionFilled('section-lampiran-wrs', hasSurat || hasChecklist);

				const peralatanGrid = document.getElementById('dok-peralatan-existing-list');
				const kegiatanGrid = document.getElementById('dok-kegiatan-existing-list');
				const hasDokPeralatan = peralatanGrid ? peralatanGrid.children.length > 0 : false;
				const hasDokKegiatan = kegiatanGrid ? kegiatanGrid.children.length > 0 : false;
				setFlag('flag-dok-peralatan', hasDokPeralatan, 'Foto tersimpan');
				setSectionFilled('section-dok-peralatan', hasDokPeralatan);
				setFlag('flag-dok-kegiatan', hasDokKegiatan, 'Foto tersimpan');
				setSectionFilled('section-dok-kegiatan', hasDokKegiatan);

				// Isi form utama
				document.getElementById('tanggal_laporan').value = laporan.tanggal_laporan || '';
				document.getElementById('nomor_surat').value = detail.nomor_surat || '';
				document.getElementById('kegiatan').value = detail.kegiatan || '';
				document.getElementById('tempat').value = detail.tempat || '';

				// Load petugas
				const petugas = detail.petugas_pelaksana || [];
				petugas.forEach(p => addPetugasRow(p));
				if (petugas.length === 0) addPetugasRow();

				// Load kegiatan
				const kegiatan = detail.kegiatan_pemeliharaan || [];
				kegiatan.forEach(k => addKegiatanRow(k));
				if (kegiatan.length === 0) addKegiatanRow();

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
		document.getElementById('add-kegiatan').addEventListener('click', () => addKegiatanRow());
		document.getElementById('add-ganti').addEventListener('click', () => addGantiRow());
		const addDokPeralatanBtn = document.getElementById('add-dok-peralatan');
		if (addDokPeralatanBtn) addDokPeralatanBtn.addEventListener('click', () => addDokRow('#dok-peralatan-table'));
		const addDokKegiatanBtn = document.getElementById('add-dok-kegiatan');
		if (addDokKegiatanBtn) addDokKegiatanBtn.addEventListener('click', () => addDokRow('#dok-kegiatan-table'));
		document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
		document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));
		hydrateLokasiSuggestions();
		addDokRow('#dok-peralatan-table');
		addDokRow('#dok-kegiatan-table');

		requireAuth();
		loadLaporanData();
	</script>
</body>
</html>
