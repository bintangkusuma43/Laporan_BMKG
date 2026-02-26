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
	<title>Laporan Preventif Maintenance</title>
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
					<div class="subtitle">Accelerograph &amp; Intensitymeter</div>
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
						<div class="section-sub">Default kondisi sebelum/sesudah adalah Baik.</div>
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

					<section class="section-card">
						<div class="section-title">Dokumentasi Umum</div>
						<div class="grid-2">
							<div><label>Upload Foto (bisa lebih dari satu)</label><input type="file" id="foto-umum" accept="image/*" multiple /></div>
							<div><label>Caption Foto (satu baris per foto)</label><textarea id="caption-umum" rows="4" placeholder="Caption 1&#10;Caption 2"></textarea></div>
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
		}

		function addPetugasRow() {
			addRow('#petugas-table', `
				<td data-no></td>
				<td><input type="text" class="nama" /></td>
				<td><input type="text" class="nip" /></td>
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

		function renderChecklist() {
			const tbody = document.querySelector('#checklist-table tbody');
			const kondisiOpt = '<option value="Baik" selected>Baik</option><option value="Rusak">Rusak</option><option value="Perlu Perbaikan">Perlu Perbaikan</option>';
			checklistMaster.forEach(group => {
				const cat = document.createElement('tr');
				cat.className = 'cat';
				cat.innerHTML = `<td colspan="5">${group.category}</td>`;
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
				nomor_spt: document.getElementById('nomor_surat').value.trim(),
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
				const res = await apiRequest('/api/laporan/create.php', { method: 'POST', body: fd });
				showGlobalAlert(res.message || 'Berhasil menyimpan laporan.', 'success');
				if (res.data?.laporan_id) {
					window.location.href = `${APP_BASE_PATH}/laporan_detail.php?id=${res.data.laporan_id}`;
				}
			} catch (error) {
				showGlobalAlert(error.message || 'Gagal menyimpan laporan.', 'error');
			}
		}

		document.getElementById('add-petugas').addEventListener('click', addPetugasRow);
		document.getElementById('add-ganti').addEventListener('click', addGantiRow);
		document.getElementById('btn-save').addEventListener('click', () => submitLaporan('draft'));
		document.getElementById('btn-generate').addEventListener('click', () => submitLaporan('submitted'));

		const kodeSiteInput = document.getElementById('kode_site');
		const namaSiteInput = document.getElementById('nama_site');
		if (kodeSiteInput) ['change', 'blur'].forEach(evt => kodeSiteInput.addEventListener(evt, autoFillSiteFromMaster));
		if (namaSiteInput) ['change', 'blur'].forEach(evt => namaSiteInput.addEventListener(evt, autoFillSiteFromMaster));
		const tipeSelect = document.getElementById('tipe_laporan');
		if (tipeSelect) tipeSelect.addEventListener('change', () => {
			kodeSiteInput.value = '';
			namaSiteInput.value = '';
			hydrateSiteSuggestions();
		});

		addPetugasRow();
		addGantiRow();
		renderChecklist();
		requireAuth();
		hydrateSiteSuggestions();
	</script>
</body>
</html>
