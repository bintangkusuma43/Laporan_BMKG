# Bab 6 — Modul & Fitur

---

## 6.1 Manajemen Pengguna & Petugas

### Pengguna (Users)
Akun login sistem dikelola oleh admin melalui halaman manajemen pengguna.

| Atribut | Keterangan |
|---------|-----------|
| `username` | Unik, digunakan untuk login |
| `role` | `admin` atau `petugas` |
| `petugas_id` | Tautan opsional ke data petugas lapangan |

- Admin dapat membuat akun baru (`/api/users/create.php`)
- Password disimpan sebagai hash menggunakan `password_hash()` (bcrypt)
- Satu akun dapat ditautkan ke satu data petugas

### Petugas
Data petugas menyimpan identitas lapangan yang dicantumkan dalam laporan.

| Atribut | Keterangan |
|---------|-----------|
| `nama` | Nama lengkap petugas |
| `upt` | Unit Pelaksana Teknis asal |
| `jabatan` | Jabatan/posisi |
| `kontak` | Nomor telepon |
| `email` | Alamat email |

---

## 6.2 Form Laporan per Jenis

Sistem mendukung tiga jenis laporan, masing-masing dengan form dan struktur data tersendiri.

### WRS NG (Warning Receiver System Next Generation)
- **File:** `laporan_form.php` (tab WRS NG) / `laporan_form_wrs.php`
- **Field utama:** Tanggal kegiatan, tempat, jenis kegiatan, checklist kondisi perangkat, catatan penggantian suku cadang
- **PDF template:** `api/laporan/pdf_templates/wrs_ng.php`

### Accelerograph / Intensitymeter
- **File:** `laporan_form_accelerograph.php`, `laporan_edit_accelerograph.php`
- **Field utama:** Kode site, nama site, tipe alat (`accelerograph` / `intensitymeter`), status alat, checklist kondisi, catatan penggantian suku cadang
- **PDF template:** `api/laporan/pdf_templates/preventif.php`
- **Catatan:** Label checklist menyesuaikan pilihan tipe alat secara dinamis

### Seismograph
- **File:** `laporan_form.php` (tab Seismograph)
- **Field utama:** Data stasiun (nama, kode, koordinat, elevasi), kondisi komponen (sensor, digitizer, GPS, baterai, dll.), catatan lapangan
- **PDF template:** `api/laporan/pdf_templates/seismograph.php`

### Alur Pengisian Laporan

```
Petugas isi form → Simpan Draft (opsional)
                 → Ajukan (status: diajukan)
                      ↓
              Admin terima & tinjau
                      ↓
        diproses → selesai / ditolak
```

---

## 6.3 Checklist Kondisi Peralatan

Checklist adalah komponen inti dari setiap form laporan. Setiap item memiliki:

| Kolom | Keterangan |
|-------|-----------|
| Label / Nama Komponen | Nama perangkat atau bagian yang diperiksa |
| Foto | Foto dokumentasi kondisi komponen |
| Kondisi | Pilihan kondisi: `Baik`, `Rusak`, `Tidak Ada`, dll. |

**Cara kerja:**
- Daftar item checklist didefinisikan sebagai array JavaScript (`checklistMaster` / `accChecklistMaster`) di sisi frontend
- Saat form dimuat, fungsi `renderChecklist()` membuild baris tabel secara dinamis
- Untuk laporan Accelerograph, label kategori `WRSNG` di data master diganti secara dinamis sesuai pilihan tipe alat (`Accelerograph` atau `Intensitymeter`)
- Hasil checklist disimpan sebagai bagian dari field `detail_json` di tabel `laporan_detail`

---

## 6.4 Generate & Download PDF

Laporan dapat diunduh dalam format PDF oleh admin maupun petugas.

**Endpoint:** `GET /api/laporan/pdf.php?id={id}`

**Proses generate:**

```
Request → Ambil data laporan + detail_json
        → Pilih template sesuai jenis laporan
        → Render HTML (dengan data terisi)
        → Dompdf konversi HTML → PDF
        → Jika ada lampiran PDF → FPDI merge
        → Output sebagai file unduhan
```

**Template per jenis:**

| Jenis | Template |
|-------|---------|
| WRS NG | `pdf_templates/wrs_ng.php` |
| Accelerograph / Intensitymeter | `pdf_templates/preventif.php` |
| Seismograph | `pdf_templates/seismograph.php` |

**Fitur tambahan:**
- Header laporan menyertakan kop instansi (logo dari folder `assets/kop/`)
- Label checklist di PDF menyesuaikan tipe alat (Accelerograph / Intensitymeter)
- Lampiran berformat PDF dari folder `uploads/` dapat digabung ke laporan utama menggunakan FPDI
