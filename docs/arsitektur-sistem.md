# Arsitektur Sistem — SIMPels BMKG

**Versi Dokumen:** 1.0  
**Tanggal:** 2026-03-03  
**Aplikasi:** SIMPels (Sistem Informasi Maintenance Peralatan Seismik) BMKG

---

## 1. Gambaran Umum Arsitektur

SIMPels adalah aplikasi web berbasis **PHP vanilla** (tanpa framework) dengan arsitektur **Client–Server monolitik**. Frontend dan backend berada dalam satu server XAMPP (Apache), berkomunikasi melalui REST API internal berbasis JSON. Laporan PDF di-generate di sisi server menggunakan library Dompdf.

```
┌─────────────────────────────────────────────────────────────────────┐
│                          BROWSER (Client)                           │
│                                                                     │
│  HTML Pages                  JavaScript (Fetch API)                 │
│  login.html  dashboard.php   ├─ Auth   → /api/auth/                 │
│  laporan_form_*.php          ├─ Laporan→ /api/laporan/              │
│  laporan_list.php            ├─ Ref    → /api/reference/            │
│  laporan_detail.php          └─ Users  → /api/users/                │
└───────────────────────────────────┬─────────────────────────────────┘
                                    │ HTTP (JSON)
                   ┌────────────────▼────────────────┐
                   │         APACHE + PHP 8.x         │
                   │         (XAMPP / localhost)       │
                   │                                  │
                   │  ┌──────────────────────────┐    │
                   │  │     REST API Layer        │    │
                   │  │   /api/**/*.php           │    │
                   │  └──────────┬───────────────┘    │
                   │             │                    │
                   │  ┌──────────▼───────────────┐    │
                   │  │    Includes / Core        │    │
                   │  │  auth.php  database.php   │    │
                   │  │  config.php response.php  │    │
                   │  └──────────┬───────────────┘    │
                   │             │                    │
                   │  ┌──────────▼───────────────┐    │
                   │  │   PDF Generator           │    │
                   │  │  Dompdf + FPDI            │    │
                   │  │  /api/laporan/pdf.php     │    │
                   │  │  pdf_templates/           │    │
                   │  └──────────────────────────┘    │
                   └────────────────┬────────────────┘
                                    │
              ┌─────────────────────┼────────────────────┐
              │                     │                    │
   ┌──────────▼──────┐   ┌──────────▼──────┐  ┌─────────▼──────────┐
   │  MySQL Database  │   │  File Storage   │  │   Assets / Kop     │
   │  laporan_bmkg    │   │  /uploads/{id}/ │  │  /assets/kop/      │
   │  (PDO + utf8mb4) │   │  foto, lampiran │  │  kop_bmkg.jpg      │
   └──────────────────┘   └─────────────────┘  └────────────────────┘
```

---

## 2. Stack Teknologi

| Lapisan          | Teknologi                        | Versi       |
|------------------|----------------------------------|-------------|
| Web Server       | Apache (XAMPP)                   | 2.4.x       |
| Backend Language | PHP                              | ≥ 8.0       |
| Database         | MySQL                            | 8.x         |
| DB Interface     | PDO (PHP Data Objects)           | bawaan PHP  |
| PDF Generator    | Dompdf                           | ^2.0        |
| PDF Merge        | FPDI + FPDF                      | 2.6 / 1.8   |
| Frontend         | HTML5 + Vanilla JavaScript       | ES2020+     |
| CSS              | Custom CSS (tanpa framework)     | —           |
| Dependency Mgr   | Composer                         | 2.x         |
| PHP Ekstensi     | PDO, PDO_MySQL, GD, mbstring     | —           |

---

## 3. Struktur Direktori

```
laporan_bmkg/
├── api/                        # REST API backend
│   ├── auth/                   # Autentikasi (login, logout, me)
│   ├── laporan/                # CRUD laporan + PDF generator
│   │   ├── pdf_templates/      # Template HTML→PDF per jenis laporan
│   │   │   ├── preventif.php   # Accelerograph / Intensitymeter
│   │   │   ├── wrs_ng.php      # WRS NG
│   │   │   └── seismograph.php # Seismograph
│   │   └── _pdf_helpers.php    # Helper fungsi PDF (escape, format tanggal, dll)
│   ├── reference/              # Data referensi (stasiun, petugas, master data)
│   └── users/                  # Manajemen pengguna
│
├── assets/
│   ├── css/styles.css          # Stylesheet global
│   ├── js/main.js              # JavaScript global
│   └── kop/kop_bmkg.jpg        # Kop surat untuk PDF
│
├── database/
│   └── schema.sql              # DDL skema database lengkap
│
├── includes/                   # Core library PHP
│   ├── auth.php                # Fungsi autentikasi & otorisasi
│   ├── config.php              # Konstanta konfigurasi (DB, BASE_URL)
│   ├── database.php            # Singleton PDO connection
│   ├── response.php            # Helper JSON response
│   └── upload.php              # Helper upload file
│
├── uploads/{laporan_id}/       # File foto & lampiran per laporan
│   └── ...
│
├── vendor/                     # Dependency Composer (Dompdf, FPDI)
├── composer.json
│
├── dashboard.php               # Halaman dashboard
├── login.html                  # Halaman login
├── laporan_form.php            # Form WRS NG + Accelerograph (unified)
├── laporan_form_accelerograph.php  # Form buat laporan Accelerograph/Intensitymeter
├── laporan_form_wrs.php        # Form buat laporan WRS NG
├── laporan_edit_accelerograph.php  # Form edit laporan Accelerograph/Intensitymeter
├── laporan_edit_wrs.php        # Form edit laporan WRS NG
├── laporan_list.php            # Daftar laporan
└── laporan_detail.php          # Detail & review laporan
```

---

## 4. Arsitektur API

Semua endpoint berada di `/api/` dan merespons JSON. Autentikasi menggunakan **PHP Session** (`bmkg_session`).

### 4.1 Struktur Endpoint

```
/api/
├── version.php                 GET   Info versi API
│
├── auth/
│   ├── login.php               POST  Login → set session
│   ├── logout.php              POST  Logout → destroy session
│   └── me.php                  GET   Data user yang sedang login
│
├── laporan/
│   ├── list.php                GET   Daftar laporan (filter: jenis, status)
│   ├── create.php              POST  Buat laporan baru (draft)
│   ├── detail.php              GET   Detail laporan + detail_json
│   ├── update.php              POST  Update laporan (draft)
│   ├── update_status.php       POST  Update status (admin: approve/reject)
│   ├── gallery.php             GET   Galeri foto laporan
│   ├── pdf.php                 GET   Generate & stream PDF
│   ├── statistik.php           GET   Statistik laporan
│   └── suku_cadang.php         GET   Rekap suku cadang
│
├── reference/
│   ├── stasiun.php             GET   Daftar stasiun
│   ├── petugas.php             GET   Daftar petugas
│   ├── master_data.php         GET   Master data (checklist, dll)
│   └── _master_stasiun_data.php    Data statis referensi stasiun
│
└── users/
    ├── list.php                GET   Daftar user (admin only)
    └── create.php              POST  Buat user baru (admin only)
```

### 4.2 Format Response JSON

Semua endpoint menggunakan format seragam:

```json
// Sukses
{ "success": true, "data": { ... } }

// Sukses dengan pagination
{ "success": true, "data": [...], "total": 42, "page": 1 }

// Gagal
{ "success": false, "message": "Pesan error yang deskriptif" }
```

### 4.3 Autentikasi & Otorisasi

```
Request → includes/auth.php → require_role(['admin','petugas'])
                                     │
             ┌───────────────────────┴───────────────────────┐
             │                                               │
          Session valid                              Session tidak ada
          → ambil user dari DB                       → return 401 Unauthorized
          → cek role
          → lanjut ke logika endpoint
```

**Role yang tersedia:**

| Role      | Hak Akses                                                    |
|-----------|--------------------------------------------------------------|
| `petugas` | Buat, lihat, edit laporan milik sendiri; download PDF sendiri |
| `admin`   | Semua akses petugas + approve/reject + lihat semua laporan   |

---

## 5. Skema Database

### 5.1 Diagram Relasi (ERD Sederhana)

```
users (id, username, full_name, role, petugas_id)
   │ petugas_id ──────────────────────────────────────────┐
   │                                                      ▼
   │                                            petugas (id, nama, upt, jabatan)
   │                                                      ▲
laporan (id, kode_laporan, jenis, user_id, petugas_id, stasiun_id, status, ...)
   │  user_id ──────────────────────────────────────► users
   │  petugas_id ───────────────────────────────────► petugas
   │  stasiun_id ───────────────────────────────────► stasiun (id, kode, nama, tipe)
   │
   ├──► laporan_detail (laporan_id, detail_type, detail_json JSON)
   ├──► dokumentasi_foto (laporan_id, file_name, caption)
   └──► laporan_lampiran (laporan_id, lampiran_type, file_name)
```

### 5.2 Penjelasan Tabel Kunci

#### `laporan`
Tabel utama laporan pemeliharaan.

| Kolom            | Tipe                                                      | Keterangan                          |
|------------------|-----------------------------------------------------------|-------------------------------------|
| `kode_laporan`   | VARCHAR(30) UNIQUE                                        | Kode unik, contoh: `ACC-20260303-001` |
| `jenis`          | ENUM(`wrs_ng`, `accelerograph`, `seismograph`)            | Jenis peralatan                     |
| `status`         | ENUM(`draft`, `diajukan`, `diproses`, `selesai`, `ditolak`) | Alur persetujuan                  |
| `tanggal_laporan`| DATE                                                      | Tanggal pelaksanaan maintenance     |

#### `laporan_detail`
Menyimpan data teknis laporan dalam format JSON (schema-flexible per jenis laporan).

| Kolom         | Tipe        | Keterangan                                              |
|---------------|-------------|---------------------------------------------------------|
| `detail_type` | ENUM        | Harus sama dengan `laporan.jenis`                       |
| `detail_json` | JSON        | Seluruh data teknis: checklist, parameter, petugas, dll |

Contoh isi `detail_json` untuk laporan Accelerograph:
```json
{
  "tipe_laporan": "accelerograph",
  "kode_site": "STA-001",
  "nama_site": "Stasiun Bandung",
  "petugas_pelaksana": [{ "nama": "Budi", "nip": "12345" }],
  "kondisi_peralatan": [
    { "category": "WRSNG", "label": "Sensor", "kondisi_sebelum": "Baik", "kondisi_sesudah": "Baik" }
  ],
  "pencatatan_parameter": { "tegangan_sumber": "220", ... }
}
```

---

## 6. Alur Generate PDF

```
Browser                     pdf.php                    Dompdf/FPDI
   │                           │                           │
   │── GET /api/laporan/pdf.php?id=X ──────────────────►  │
   │                           │                           │
   │                    1. Autentikasi session             │
   │                    2. Query laporan + detail          │
   │                    3. Query foto & lampiran           │
   │                    4. Tentukan template:              │
   │                       jenis=accelerograph → preventif.php
   │                       jenis=wrs_ng        → wrs_ng.php
   │                       jenis=seismograph   → seismograph.php
   │                           │                           │
   │                    5. Render HTML template      ──►  loadHtml()
   │                           │                    ──►  render()
   │                           │                           │
   │                    6. Jika ?merge=1:                  │
   │                       FPDI merge PDF lampiran         │
   │                           │                           │
   │◄── stream PDF (inline) ───┤                           │
```

Foto dalam PDF dikonversi ke **Base64 Data URI** di server (fungsi `pdf_image_data_uri_from_upload()`) agar Dompdf tidak perlu akses filesystem saat rendering.

---

## 7. Alur Status Laporan

```
            [Petugas]                    [Admin]
               │                            │
           Buat Form                        │
               │                            │
           ┌───▼────┐                       │
           │ DRAFT  │◄──── Edit/Update ─────┤
           └───┬────┘                       │
               │ Submit (btn-generate)       │
           ┌───▼────────┐                   │
           │  DIAJUKAN  │                   │
           └───┬────────┘                   │
               │                      Review Admin
       ┌───────┴────────┐                   │
       │                │                   │
   ┌───▼────────┐   ┌───▼──────┐            │
   │  DIPROSES  │   │ DITOLAK  │◄───────────┘
   └───┬────────┘   └──────────┘
       │
   ┌───▼──────┐
   │  SELESAI │
   └──────────┘
```

---

## 8. Keamanan

| Aspek              | Implementasi                                                   |
|--------------------|----------------------------------------------------------------|
| Autentikasi        | PHP Session (`bmkg_session`, httponly, SameSite=Lax)           |
| Otorisasi          | `require_role()` di setiap endpoint API                        |
| Isolasi data       | Petugas hanya bisa akses laporan `user_id = session.user_id`   |
| SQL Injection      | Semua query menggunakan PDO Prepared Statements                |
| XSS (PDF)          | Semua output di template PDF melewati `pdf_escape()` (htmlspecialchars) |
| Upload file        | Validasi ekstensi + MIME type di `includes/upload.php`         |
| Path traversal     | Nama file dinormalisasi, path `..` difilter sebelum akses disk |

---

## 9. Persyaratan Server

| Komponen      | Minimum              | Rekomendasi           |
|---------------|----------------------|-----------------------|
| PHP           | 8.0                  | 8.2+                  |
| MySQL         | 8.0                  | 8.0+                  |
| Ekstensi PHP  | pdo, pdo_mysql, gd, mbstring, json, zlib | (sama) |
| Web Server    | Apache 2.4           | Apache 2.4            |
| RAM           | 256 MB               | 512 MB+ (untuk PDF render foto besar) |
| Disk          | Sesuai volume foto   | —                     |

---

*Dokumen ini dibuat berdasarkan analisis source code SIMPels versi Maret 2026.*
