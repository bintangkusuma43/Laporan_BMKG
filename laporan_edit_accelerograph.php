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
	<title>Edit Draft Laporan Accelerograph</title>
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
				<h1 id="page-title">Edit Draft Laporan Accelerograph</h1>
				<div class="subtitle" id="page-subtitle">Perbarui data pemeliharaan Accelerograph</div>
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
						<div class="section-title">Jenis Laporan</div>
						<div>
							<label for="tipe_laporan">Pilih Jenis Laporan:</label>
							<select id="tipe_laporan">
								<option value="accelerograph" selected>Accelerograph</option>
								<option value="intensitymeter">Intensitymeter</option>
							</select>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Informasi Tugas</div>
						<div class="grid-2">
							<div><label for="tanggal_laporan">Tanggal Pelaksanaan *</label><input type="date" id="tanggal_laporan" required /></div>
							<div><label for="nomor_surat">Nomor Surat Tugas *</label><input type="text" id="nomor_surat" required /></div>
							<div><label for="kode_site">Kode Site *</label><input type="text" id="kode_site" required /></div>
							<div><label for="nama_site">Nama Site *</label><input type="text" id="nama_site" required /></div>
						</div>
						<div style="margin-top:12px;">
							<label for="kerusakan">Kerusakan</label>
							<textarea id="kerusakan" rows="3"></textarea>
						</div>
						<div style="margin-top:12px;">
							<label for="rekomendasi">Rekomendasi</label>
							<textarea id="rekomendasi" rows="3"></textarea>
						</div>
						<div style="margin-top:12px;">
							<label for="status_alat">Status</label>
							<select id="status_alat">
								<option value="ON" selected>ON</option>
								<option value="OFF">OFF</option>
							</select>
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
						<div class="section-sub">Default kondisi sebelum/sesudah adalah Baik. Foto akan diganti jika upload ulang.</div>
						<div class="table-wrap">
							<table class="line-table" id="checklist-table">
								<thead><tr><th>Deskripsi</th><th>Kondisi Sebelum</th><th>Foto Sebelum</th><th>Kondisi Sesudah</th><th>Foto Sesudah</th></tr></thead>
								<tbody></tbody>
							</table>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Pelaksanaan Maintenance</div>
						<div class="section-sub">Pembersihan</div>
						<div class="grid-2" style="margin-bottom:10px;">
							<div><label for="pembersihan_lingkungan">Pembersihan Lingkungan</label><select id="pembersihan_lingkungan"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="pembersihan_peralatan">Pembersihan Peralatan</label><select id="pembersihan_peralatan"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
						</div>

						<div class="section-sub">Pengecekan Kelistrikan</div>
						<div class="grid-3">
							<div><label for="sisa_token">Sisa Token (kWh)</label><input type="number" id="sisa_token" min="0" step="0.01" /></div>
							<div><label for="tegangan_sumber">Tegangan Sumber (Volt)</label><input type="number" id="tegangan_sumber" min="0" step="0.01" /></div>
							<div><label for="tegangan_output_stabilizer">Tegangan Output Stabilizer (Volt)</label><input type="number" id="tegangan_output_stabilizer" min="0" step="0.01" /></div>
							<div><label for="tegangan_output_ups">Tegangan Output UPS (Volt)</label><input type="number" id="tegangan_output_ups" min="0" step="0.01" /></div>
							<div><label for="tegangan_baterai">Tegangan Baterai (Avg) (Volt)</label><input type="number" id="tegangan_baterai" min="0" step="0.01" /></div>
							<div><label for="jumlah_baterai">Jumlah Baterai</label><input type="number" id="jumlah_baterai" min="0" step="1" /></div>
							<div><label for="ketahanan_ups">Ketahanan UPS</label><select id="ketahanan_ups"><option value="Baik" selected>Baik</option><option value="Cukup">Cukup</option><option value="Buruk">Buruk</option></select></div>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Pengecekan Sistem Komunikasi</div>
						<div class="grid-2">
							<div><label for="ping_modem">Ping IP Modem/Gateway</label><select id="ping_modem"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
							<div><label for="ping_digitizer">Ping IP Digitizer</label><select id="ping_digitizer"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
							<div><label for="ping_display">Ping PC Display</label><select id="ping_display"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
							<div><label for="ping_server">Ping IP Server</label><select id="ping_server"><option value="OK" selected>OK</option><option value="Tidak OK">Tidak OK</option></select></div>
						</div>
					</section>

					<section class="section-card">
						<div class="section-title">Pengecekan Peralatan Seismik</div>
						<div class="grid-2">
							<div><label for="leveling_sensor">Leveling Sensor</label><select id="leveling_sensor"><option value="Dilakukan" selected>Dilakukan</option><option value="Tidak">Tidak</option></select></div>
							<div><label for="pengecekan_slmon">Pengecekan SLMON Simora</label><select id="pengecekan_slmon"><option value="ON" selected>ON</option><option value="OFF">OFF</option></select></div>
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
						<div id="dok-existing" class="existing-list" style="display:none;">
							<div class="section-sub" style="margin-bottom:8px;">Foto tersimpan (tetap ada kecuali diunggah ulang)</div>
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
	<script src="/laporan_bmkg/assets/js/petugasAutofill.js"></script>
	<script>
		const LAPORAN_ID = <?php echo json_encode($laporan_id); ?>;
		
		const checklistMaster = [
			{ category: 'WRSNG', items: ['Shelter', 'Sekitar Peralatan'] },
			{ category: 'Sistem Kelistrikan', items: ['Stabilizer', 'UPS', 'Baterai', 'Terminal Arrester', 'LAN Arrester', 'Solar Panel', 'Regulator', 'Inverter'] },
			{ category: 'Sistem Komunikasi', items: ['Modem GSM', 'Antenna Yagi', 'Modem VSAT', 'Antenna VSAT'] },
			{ category: 'Peralatan Seismik', items: ['Sensor Accelerometer', 'Digitizer', 'GPS'] }
		];

		function toNullableNumber(value) {
			if (value === '' || value === null || value === undefined) return '';
			const num = Number(value);
			return Number.isFinite(num) ? num : '';
		}

		const masterStasiunCache = {};

		function getSelectedTipe() {
			return (document.getElementById('tipe_laporan')?.value || '').toLowerCase();
		}

		function loadMasterStasiun(tipe) {
			const key = tipe || 'all';
			const cache = masterStasiunCache[key];
			if (cache?.data) return Promise.resolve(cache.data);
			if (cache?.promise) return cache.promise;
			const promise = apiRequest(`/api/reference/master_data.php?kind=stasiun${tipe ? `&tipe=${encodeURIComponent(tipe)}` : ''}`)
				.then(res => res.data || [])
				.catch(() => [])
				.then(data => {
					masterStasiunCache[key] = { data, promise: Promise.resolve(data) };
					return data;
				});
			masterStasiunCache[key] = { promise };
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

		function normalizeText(value) {
			return (value || '').trim().toLowerCase();
		}



		// Use centralized PetugasAutofill from petugasAutofill.js
		function bindPetugasAutoFill(row) {
			const namaInput = row?.querySelector('.nama');
			const nipInput = row?.querySelector('.nip');
			if (namaInput && nipInput) {
				PetugasAutofill.bindRow(namaInput, nipInput);
			}
		}

		function autoFillSiteFromMaster() {
			const kodeInput = document.getElementById('kode_site');
			const namaInput = document.getElementById('nama_site');
			if (!kodeInput || !namaInput) return;
			const kode = (kodeInput.value || '').trim().toLowerCase();
			const nama = (namaInput.value || '').trim().toLowerCase();
			if (!kode && !nama) return;
			const tipe = getSelectedTipe();
			loadMasterStasiun(tipe).then(list => {
				const match = list.find(item => {
					const kodeMatch = kode && (item.kode || '').toLowerCase() === kode;
					const namaMatch = nama && (item.nama || '').toLowerCase() === nama;
					return kodeMatch || namaMatch;
				});
				if (match) {
					if (match.kode) kodeInput.value = match.kode;
					if (match.nama) namaInput.value = match.nama;
				}
			});
		}

		function hydrateSiteSuggestions() {
			const kodeInput = document.getElementById('kode_site');
			const namaInput = document.getElementById('nama_site');
			if (!kodeInput || !namaInput) return;
			const tipe = getSelectedTipe();
			loadMasterStasiun(tipe).then(list => {
				const options = list.map(item => ({
					value: item.kode || '',
					label: `${item.kode || ''} - ${item.nama || ''}`.trim()
				}));
				ensureDatalist('site-kode-list', options);
				ensureDatalist('site-nama-list', list.map(item => ({ value: item.nama || '', label: `${item.nama || ''} (${item.kode || ''})` })));
				kodeInput.setAttribute('list', 'site-kode-list');
				namaInput.setAttribute('list', 'site-nama-list');
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
			return tr;
		}

		function addPetugasRow(data = {}) {
			const html = `
				<td data-no></td>
				<td><input type="text" class="nama" value="${data.nama || ''}" /></td>
				<td><input type="text" class="nip" value="${data.nip || ''}" /></td>
				<td><button type="button" class="btn-mini btn-remove">Hapus</button></td>
			`;
			const tr = addRow('#petugas-table', html);
			bindPetugasAutoFill(tr);
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

		function renderChecklist(existingData = []) {
			const tbody = document.querySelector('#checklist-table tbody');
			tbody.innerHTML = '';
			const tipeVal = document.getElementById('tipe_laporan')?.value || 'accelerograph';
			const tipeLabel = tipeVal === 'intensitymeter' ? 'Intensitymeter' : 'Accelerograph';
			const kondisiOpt = '<option value="Baik">Baik</option><option value="Rusak">Rusak</option><option value="Perlu Perbaikan">Perlu Perbaikan</option>';
			
			checklistMaster.forEach(group => {
				const categoryDisplay = group.category === 'WRSNG' ? tipeLabel : group.category;
				const cat = document.createElement('tr');
				cat.className = 'cat';
				cat.innerHTML = `<td colspan="5">${categoryDisplay}</td>`;
				tbody.appendChild(cat);
				
				group.items.forEach(item => {
					const existing = existingData.find(e => e.label === item && e.category === group.category);
					const kondisiSebelum = existing?.kondisi_sebelum || 'Baik';
					const kondisiSesudah = existing?.kondisi_sesudah || 'Baik';
					
					const tr = document.createElement('tr');
					tr.dataset.category = group.category;
					tr.dataset.label = item;
					tr.innerHTML = `
						<td>${item}</td>
						<td><select class="sebelum">${kondisiOpt.replace(`value="${kondisiSebelum}"`, `value="${kondisiSebelum}" selected`)}</select></td>
						<td>
							<div class="file-stack">
								<input type="file" class="foto-sebelum" accept="image/*" />
								<span class="chip-filled existing-before" style="display:none;">Foto tersimpan</span>
							</div>
						</td>
						<td><select class="sesudah">${kondisiOpt.replace(`value="${kondisiSesudah}"`, `value="${kondisiSesudah}" selected`)}</select></td>
						<td>
							<div class="file-stack">
								<input type="file" class="foto-sesudah" accept="image/*" />
								<span class="chip-filled existing-after" style="display:none;">Foto tersimpan</span>
							</div>
						</td>
					`;
					tbody.appendChild(tr);
				});
			});
		}

		function collectRows(selector, mapper) {
			return Array.from(document.querySelectorAll(`${selector} tbody tr`)).map(mapper).filter(Boolean);
		}

		function escapeHtml(str) {
			return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
		}

		function setFlag(flagId, isOn, text) {
			const el = document.getElementById(flagId);
			if (!el) return;
			el.style.display = isOn ? 'inline-flex' : 'none';
			if (text) el.textContent = text;
		}

		function markChecklistExistingPhotos(dokumentasi) {
			const docs = Array.isArray(dokumentasi) ? dokumentasi : [];
			const rows = document.querySelectorAll('#checklist-table tbody tr:not(.cat)');
			rows.forEach((row) => {
				const label = (row.dataset.label || '').trim();
				const base = label.toLowerCase();
				const hasBefore = docs.some((d) => (d.caption || '').toLowerCase() === `${base} - sebelum`);
				const hasAfter = docs.some((d) => (d.caption || '').toLowerCase() === `${base} - sesudah`);
				const beforeFlag = row.querySelector('.existing-before');
				const afterFlag = row.querySelector('.existing-after');
				if (beforeFlag) beforeFlag.style.display = hasBefore ? 'inline-flex' : 'none';
				if (afterFlag) afterFlag.style.display = hasAfter ? 'inline-flex' : 'none';
				row.classList.toggle('has-existing-photo', hasBefore || hasAfter);
			});
		}

		function setSectionFilled(sectionId, isFilled) {
			const section = document.getElementById(sectionId);
			if (!section) return;
			section.classList.toggle('filled', Boolean(isFilled));
		}

		function renderExistingDocs(list) {
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
				const filePath = `${APP_BASE_PATH}/uploads/${f.file_name}`;
				return `<div class="dok-card"><div class="dok-img" style="background-image:url('${filePath}')"></div><div class="dok-caption">${caption}</div></div>`;
			}).join('');
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
				console.log('Laporan:', laporan);
				console.log('Detail:', detail);

				if (laporan.status !== 'draft') {
					throw new Error('Hanya laporan dengan status draft yang bisa diedit');
				}

				if (laporan.jenis !== 'accelerograph') {
					throw new Error('Jenis laporan tidak sesuai');
				}

				// Isi form utama
				document.getElementById('tipe_laporan').value = detail.tipe_laporan || 'accelerograph';
				updateTipeTitle();
				document.getElementById('tanggal_laporan').value = laporan.tanggal_laporan || '';
				document.getElementById('nomor_surat').value = detail.nomor_surat || detail.nomor_spt || '';
				document.getElementById('kode_site').value = detail.kode_site || '';
				document.getElementById('nama_site').value = detail.nama_site || '';
				document.getElementById('kerusakan').value = detail.deskripsi_kerusakan || '';
				document.getElementById('rekomendasi').value = detail.rekomendasi || '';
				document.getElementById('status_alat').value = detail.status_alat || 'ON';

				// Load petugas
				const petugas = detail.petugas_pelaksana || [];
				petugas.forEach(p => addPetugasRow(p));
				if (petugas.length === 0) addPetugasRow();

				// Load checklist
				renderChecklist(detail.kondisi_peralatan || []);

				renderExistingDocs(dokumentasi);
				const hasDok = Array.isArray(dokumentasi) && dokumentasi.length > 0;
				setFlag('flag-dok-umum', hasDok, 'Foto tersimpan');
				setSectionFilled('section-dok-umum', hasDok);
				markChecklistExistingPhotos(dokumentasi);

				// Load pembersihan
				document.getElementById('pembersihan_lingkungan').value = detail.pembersihan?.lingkungan || 'Dilakukan';
				document.getElementById('pembersihan_peralatan').value = detail.pembersihan?.peralatan || 'Dilakukan';

				// Load parameter kelistrikan
				const param = detail.pencatatan_parameter || {};
				document.getElementById('sisa_token').value = param.sisa_token || '';
				document.getElementById('tegangan_sumber').value = param.tegangan_sumber || '';
				document.getElementById('tegangan_output_stabilizer').value = param.tegangan_output_stabilizer || '';
				document.getElementById('tegangan_output_ups').value = param.tegangan_output_ups || '';
				document.getElementById('tegangan_baterai').value = param.tegangan_baterai || '';
				document.getElementById('jumlah_baterai').value = param.jumlah_baterai || '';
				document.getElementById('ketahanan_ups').value = detail.pengecekan_listrik?.ketahanan_ups || 'Baik';

				// Load komunikasi
				const kom = detail.pengecekan_komunikasi || {};
				document.getElementById('ping_modem').value = kom.ping_modem || 'OK';
				document.getElementById('ping_digitizer').value = kom.ping_digitizer || 'OK';
				document.getElementById('ping_display').value = kom.ping_display || 'OK';
				document.getElementById('ping_server').value = kom.ping_server || 'OK';

				// Load peralatan seismik
				const seismik = detail.pengecekan_peralatan_seismik || {};
				document.getElementById('leveling_sensor').value = seismik.leveling_sensor || 'Dilakukan';
				document.getElementById('pengecekan_slmon').value = seismik.slmon_simora || 'ON';

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
			fd.append('jenis', 'accelerograph');
			fd.append('status', status);
			fd.append('tanggal_laporan', document.getElementById('tanggal_laporan').value);
			fd.append('nomor_surat', document.getElementById('nomor_surat').value.trim());

			const kondisi = Array.from(document.querySelectorAll('#checklist-table tbody tr')).filter(row => !row.classList.contains('cat')).map(row => {
				const label = row.dataset.label || '';
				const category = row.dataset.category || '';
				const kondisiSebelum = row.querySelector('.sebelum')?.value || 'Baik';
				const kondisiSesudah = row.querySelector('.sesudah')?.value || 'Baik';
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
				return { category, label, kondisi_sebelum: kondisiSebelum, kondisi_sesudah: kondisiSesudah };
			});

			const detail = {
				tipe_laporan: document.getElementById('tipe_laporan').value,
				nomor_surat: document.getElementById('nomor_surat').value.trim(),
				kode_site: document.getElementById('kode_site').value.trim(),
				nama_site: document.getElementById('nama_site').value.trim(),
				deskripsi_kerusakan: document.getElementById('kerusakan').value.trim(),
				rekomendasi: document.getElementById('rekomendasi').value.trim(),
				status_alat: document.getElementById('status_alat').value,
				petugas_pelaksana: collectRows('#petugas-table', (row) => {
					const nama = row.querySelector('.nama')?.value.trim() || '';
					const nip = row.querySelector('.nip')?.value.trim() || '';
					return nama || nip ? { nama, nip } : null;
				}),
				kondisi_peralatan: kondisi,
				pembersihan: {
					lingkungan: document.getElementById('pembersihan_lingkungan').value,
					peralatan: document.getElementById('pembersihan_peralatan').value
				},
				pencatatan_parameter: {
					sisa_token: toNullableNumber(document.getElementById('sisa_token').value),
					tegangan_sumber: toNullableNumber(document.getElementById('tegangan_sumber').value),
					tegangan_output_stabilizer: toNullableNumber(document.getElementById('tegangan_output_stabilizer').value),
					tegangan_output_ups: toNullableNumber(document.getElementById('tegangan_output_ups').value),
					tegangan_baterai: toNullableNumber(document.getElementById('tegangan_baterai').value),
					jumlah_baterai: toNullableNumber(document.getElementById('jumlah_baterai').value)
				},
				pengecekan_listrik: {
					ketahanan_ups: document.getElementById('ketahanan_ups').value
				},
				pengecekan_komunikasi: {
					ping_modem: document.getElementById('ping_modem').value,
					ping_digitizer: document.getElementById('ping_digitizer').value,
					ping_display: document.getElementById('ping_display').value,
					ping_server: document.getElementById('ping_server').value
				},
				pengecekan_peralatan_seismik: {
					leveling_sensor: document.getElementById('leveling_sensor').value,
					slmon_simora: document.getElementById('pengecekan_slmon').value
				},
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
		document.getElementById('add-ganti').addEventListener('click', () => addGantiRow());
		document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
		document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));

		const kodeSiteInput = document.getElementById('kode_site');
		const namaSiteInput = document.getElementById('nama_site');
		if (kodeSiteInput) ['change', 'blur'].forEach(evt => kodeSiteInput.addEventListener(evt, autoFillSiteFromMaster));
		if (namaSiteInput) ['change', 'blur'].forEach(evt => namaSiteInput.addEventListener(evt, autoFillSiteFromMaster));
		
		function updateTipeTitle() {
			const tipe = document.getElementById('tipe_laporan').value;
			const titleEl = document.getElementById('page-title');
			const subtitleEl = document.getElementById('page-subtitle');
			if (tipe === 'intensitymeter') {
				titleEl.textContent = 'Edit Draft Laporan Intensitymeter';
				subtitleEl.textContent = 'Perbarui data pemeliharaan Intensitymeter';
			} else {
				titleEl.textContent = 'Edit Draft Laporan Accelerograph';
				subtitleEl.textContent = 'Perbarui data pemeliharaan Accelerograph';
			}
		}
		
		const tipeSelect = document.getElementById('tipe_laporan');
		if (tipeSelect) tipeSelect.addEventListener('change', () => {
			updateTipeTitle();
			kodeSiteInput.value = '';
			namaSiteInput.value = '';
			autoFillSiteFromMaster();
			hydrateSiteSuggestions();
			renderChecklist();
		});

		requireAuth();
		loadLaporanData();
		hydrateSiteSuggestions();
	</script>
</body>
</html>
