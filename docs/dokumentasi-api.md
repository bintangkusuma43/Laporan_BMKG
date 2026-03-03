# Bab 4 — Dokumentasi API

SIMPels BMKG menggunakan arsitektur **REST API berbasis PHP** tanpa framework. Semua endpoint mengembalikan respons JSON dengan format standar. Autentikasi menggunakan **PHP Session** (cookie-based).

---

## 4.1 Format Respons Standar

Semua endpoint menggunakan struktur respons yang konsisten:

### Respons Sukses
```json
{
  "success": true,
  "data": { ... } 
}
```

### Respons Gagal
```json
{
  "success": false,
  "message": "Pesan error yang dapat dibaca pengguna."
}
```

### Kode HTTP yang Digunakan

| Kode | Keterangan |
|------|-----------|
| `200` | OK — permintaan berhasil |
| `400` | Bad Request — body tidak valid |
| `401` | Unauthorized — belum login atau kredensial salah |
| `403` | Forbidden — tidak punya hak akses |
| `404` | Not Found — data tidak ditemukan |
| `405` | Method Not Allowed — metode HTTP salah |
| `409` | Conflict — kondisi data bertentangan |
| `422` | Unprocessable Entity — validasi gagal |

---

## 4.2 Autentikasi

Sistem menggunakan **PHP Session** standar. Token tidak digunakan. Cookie sesi dikirim otomatis oleh browser.

### Mekanisme

```
POST /api/auth/login.php
  → server memverifikasi kredensial
  → session_regenerate_id(true)   ← cegah session fixation
  → $_SESSION['user_id'] = id
  → $_SESSION['role']    = role
  → Set-Cookie: PHPSESSID=...
```

Setiap request berikutnya harus menyertakan cookie `PHPSESSID`. Semua endpoint (kecuali `login.php`) memanggil `require_role()` atau `require_admin()` di awal file. Jika sesi tidak valid, server merespons `401`.

### Level Akses

| Role | Hak Akses |
|------|-----------|
| `petugas` | Buat, lihat, dan edit laporan **milik sendiri** |
| `admin` | Akses semua laporan (kecuali draft milik orang lain), ubah status, kelola master data |

---

## 4.3 Endpoint: Autentikasi

### POST `/api/auth/login.php`

Login dan mulai sesi.

**Request Body** (JSON):

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `username` | string | ✅ | Username akun |
| `password` | string | ✅ | Password akun |

**Contoh Request:**
```json
{
  "username": "petugas01",
  "password": "rahasia123"
}
```

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": {
    "id": 3,
    "username": "petugas01",
    "full_name": "Budi Santoso",
    "role": "petugas",
    "petugas_id": 12
  }
}
```

---

### POST `/api/auth/logout.php`

Akhiri sesi pengguna yang sedang login.

**Request Body:** *(tidak diperlukan)*

**Respons Sukses (200):**
```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

---

### GET `/api/auth/me.php`

Cek status autentikasi dan ambil data pengguna aktif beserta data petugas terkait.

**Respons jika terautentikasi (200):**
```json
{
  "success": true,
  "authenticated": true,
  "data": {
    "id": 3,
    "username": "petugas01",
    "full_name": "Budi Santoso",
    "role": "petugas",
    "petugas": {
      "id": 12,
      "nama": "Budi Santoso",
      "upt": "UPT BMKG Bandung",
      "jabatan": "Teknisi",
      "kontak": "081234567890",
      "email": "budi@bmkg.go.id"
    }
  }
}
```

**Respons jika belum login (200):**
```json
{
  "success": false,
  "authenticated": false,
  "message": "NOT_AUTHENTICATED"
}
```

> **Catatan:** Endpoint ini selalu mengembalikan HTTP 200 meskipun tidak terautentikasi. Cek field `authenticated` untuk menentukan status login.

---

## 4.4 Endpoint: Laporan

### GET `/api/laporan/list.php`

Ambil daftar laporan. Petugas hanya melihat laporan milik sendiri; admin melihat semua laporan (kecuali draft milik petugas lain).

**Query Parameter:**

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|-----------|
| `jenis` | string | ❌ | Filter: `wrs_ng`, `accelerograph`, `seismograph` |
| `status` | string | ❌ | Filter: `draft`, `diajukan`, `diproses`, `selesai`, `ditolak` |
| `tanggal_mulai` | string | ❌ | Filter tanggal mulai (format `YYYY-MM-DD`) |
| `tanggal_selesai` | string | ❌ | Filter tanggal akhir (format `YYYY-MM-DD`) |
| `search` | string | ❌ | Cari berdasarkan `kode_laporan`, `nomor_surat`, atau `nama_stasiun` |
| `limit` | integer | ❌ | Batasi jumlah hasil (contoh: `limit=10` untuk dashboard) |

**Contoh Request:**
```
GET /api/laporan/list.php?jenis=wrs_ng&status=diajukan&limit=20
```

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "kode_laporan": "LAP-WRS-20250301-001",
      "jenis": "wrs_ng",
      "tanggal_laporan": "2025-03-01",
      "status": "diajukan",
      "nomor_surat": "B-001/BMKG/2025",
      "nama_stasiun": "BMKG Bandung",
      "kode_stasiun": "BDG",
      "nama_pelapor": "Budi Santoso",
      "created_at": "2025-03-01 09:00:00",
      "kegiatan": "Pemeliharaan berkala"
    }
  ]
}
```

---

### GET `/api/laporan/detail.php`

Ambil detail lengkap satu laporan, termasuk `detail_json`, foto, dan lampiran.

**Query Parameter:**

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|-----------|
| `id` | integer | ✅ | ID laporan |

**Contoh Request:**
```
GET /api/laporan/detail.php?id=45
```

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": {
    "laporan": {
      "id": 45,
      "kode_laporan": "LAP-WRS-20250301-001",
      "jenis": "wrs_ng",
      "user_id": 3,
      "stasiun_id": 7,
      "nomor_surat": "B-001/BMKG/2025",
      "tanggal_laporan": "2025-03-01",
      "status": "diajukan",
      "catatan_admin": null,
      "created_at": "2025-03-01 09:00:00",
      "updated_at": "2025-03-01 09:00:00",
      "nama_pelapor": "Budi Santoso",
      "nama_stasiun": "BMKG Bandung",
      "kode_stasiun": "BDG"
    },
    "detail": {
      "detail_type": "wrs_ng",
      "detail_json": { ... }
    },
    "fotos": [
      {
        "id": 101,
        "file_name": "45/foto_panel.jpg",
        "caption": "Kondisi panel utama",
        "uploaded_at": "2025-03-01 09:05:00"
      }
    ]
  }
}
```

**Aturan Akses:**
- Petugas hanya bisa mengakses laporan miliknya sendiri → `403` jika bukan miliknya
- Admin tidak bisa melihat draft milik petugas lain → `403`

---

### POST `/api/laporan/create.php`

Buat laporan baru. Request menggunakan `multipart/form-data` (karena dapat menyertakan file foto).

**Form Fields:**

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `jenis` | string | ✅ | `wrs_ng`, `accelerograph`, atau `seismograph` |
| `stasiun_id` | integer | ❌ | ID stasiun terkait |
| `tanggal_laporan` | string | ✅* | Format `YYYY-MM-DD`. *Opsional jika `status=draft` |
| `nomor_surat` | string | ✅* | *Wajib jika bukan draft |
| `detail_json` | string | ✅ | JSON string berisi data teknis laporan |
| `status` | string | ❌ | `draft` atau `submitted` (default: `submitted`) |
| `foto[]` | file | ❌ | Upload foto (multiple, format JPG/PNG/WEBP, maks 5MB/file) |

**Mapping status input → database:**

| Input | Nilai di DB |
|-------|------------|
| `submitted` | `diajukan` |
| `draft` | `draft` |

**Contoh Request (JSON `detail_json` untuk WRS NG):**
```json
{
  "kegiatan": "Pemeliharaan berkala",
  "tempat": "BMKG Bandung",
  "tanggal_mulai": "2025-03-01",
  "tanggal_selesai": "2025-03-01",
  "checklist": [ ... ]
}
```

**Respons Sukses (200):**
```json
{
  "success": true,
  "message": "Laporan berhasil dibuat.",
  "data": {
    "id": 46,
    "kode_laporan": "LAP-WRS-20250301-002"
  }
}
```

---

### POST `/api/laporan/update.php`

Edit laporan yang masih berstatus `draft`. Laporan yang sudah diajukan (`diajukan`, `diproses`, `selesai`, `ditolak`) **tidak bisa diubah** melalui endpoint ini.

**Form Fields:** *(sama seperti `create.php`, ditambah:)*

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `id` | integer | ✅ | ID laporan yang akan diubah |
| `delete_foto_ids[]` | integer | ❌ | ID foto yang akan dihapus |

**Respons Sukses (200):**
```json
{
  "success": true,
  "message": "Laporan berhasil diperbarui."
}
```

**Error Khas:**
- `409` — Laporan sudah bukan draft, tidak bisa diubah
- `403` — Laporan bukan milik pengguna ini

---

### POST `/api/laporan/update_status.php`

Ubah status laporan. **Hanya admin** yang dapat mengakses endpoint ini.

**Request Body** (JSON):

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `id` | integer | ✅ | ID laporan |
| `status` | string | ✅ | Nilai baru status laporan |
| `catatan` | string | ❌ | Catatan/komentar dari admin |

**Nilai `status` yang valid:** `diajukan`, `diproses`, `selesai`, `ditolak`

**Contoh Request:**
```json
{
  "id": 45,
  "status": "selesai",
  "catatan": "Laporan telah diverifikasi dan disetujui."
}
```

**Respons Sukses (200):**
```json
{
  "success": true,
  "message": "Status laporan berhasil diperbarui."
}
```

---

### GET `/api/laporan/statistik.php`

Ambil ringkasan statistik laporan, dikelompokkan berdasarkan status dan jenis. Petugas hanya melihat statistik laporannya sendiri.

**Query Parameter:** *(tidak ada)*

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": {
    "by_status": {
      "draft": 2,
      "diajukan": 5,
      "diproses": 3,
      "selesai": 10,
      "ditolak": 1
    },
    "by_jenis": {
      "wrs_ng": 12,
      "accelerograph": 7,
      "seismograph": 2
    }
  }
}
```

---

### GET `/api/laporan/gallery.php`

Ambil semua foto dokumentasi laporan. Petugas hanya melihat foto dari laporannya sendiri.

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 101,
      "file_name": "45/foto_panel.jpg",
      "caption": "Kondisi panel utama",
      "uploaded_at": "2025-03-01 09:05:00",
      "kode_laporan": "LAP-WRS-20250301-001",
      "jenis": "wrs_ng",
      "url": "http://localhost/laporan_bmkg/uploads/45/foto_panel.jpg"
    }
  ]
}
```

---

### GET `/api/laporan/suku_cadang.php`

Ambil riwayat penggantian suku cadang dari semua laporan yang memiliki field `catatan_penggantian` di `detail_json`.

**Query Parameter:**

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|-----------|
| `jenis` | string | ❌ | Filter berdasarkan jenis laporan |
| `search` | string | ❌ | Pencarian teks bebas |

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "laporan_id": 45,
      "kode_laporan": "LAP-ACC-20250301-001",
      "jenis": "accelerograph",
      "tanggal_laporan": "2025-03-01",
      "nama_stasiun": "BMKG Bandung",
      "nama_site": "Site Lembang",
      "kode_site": "LBG-001",
      "penggantian": [
        {
          "nama_komponen": "Baterai",
          "jumlah": 2,
          "keterangan": "Baterai lama sudah lemah"
        }
      ]
    }
  ]
}
```

---

### GET `/api/laporan/pdf.php`

Generate dan unduh PDF laporan. Mengembalikan file PDF (bukan JSON).

**Query Parameter:**

| Parameter | Tipe | Wajib | Keterangan |
|-----------|------|-------|-----------|
| `id` | integer | ✅ | ID laporan |

**Response Header (sukses):**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="LAP-WRS-20250301-001.pdf"
```

**Catatan:** Jika laporan memiliki file lampiran PDF, sistem akan menggabungkan (merge) laporan utama dengan lampiran menggunakan FPDI.

---

## 4.5 Endpoint: Referensi Master Data

### GET `/api/reference/stasiun.php`

Ambil daftar stasiun BMKG. Data bersumber dari database; jika database kosong, sistem otomatis mengisi data dari file `_master_stasiun_data.php` (seed) dan mengembalikan hasilnya.

**Akses:** admin, petugas

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 7,
      "kode": "BDG",
      "nama": "BMKG Bandung",
      "lokasi": "Jl. Cemara, Bandung",
      "tipe": "meteorologi"
    }
  ],
  "source": "database"
}
```

> Jika data dari fallback statis, field `source` = `"fallback_master"` dan akan ada field `warning`.

---

### GET `/api/reference/petugas.php`

Ambil daftar petugas. **Hanya admin.**

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "nama": "Budi Santoso",
      "upt": "UPT BMKG Bandung",
      "jabatan": "Teknisi",
      "kontak": "081234567890",
      "email": "budi@bmkg.go.id"
    }
  ]
}
```

---

### GET `/api/reference/master_data.php`

Ambil semua master data referensi yang digunakan pada form laporan (nama komponen, jenis kegiatan, dll.).

**Akses:** admin, petugas

---

## 4.6 Endpoint: Manajemen Pengguna

### GET `/api/users/list.php`

Ambil daftar semua akun pengguna. **Hanya admin.**

**Respons Sukses (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "username": "petugas01",
      "full_name": "Budi Santoso",
      "role": "petugas",
      "petugas_id": 12,
      "created_at": "2025-01-15 08:00:00",
      "nama_petugas": "Budi Santoso",
      "upt_petugas": "UPT BMKG Bandung"
    }
  ]
}
```

---

### POST `/api/users/create.php`

Buat akun pengguna baru. **Hanya admin.**

**Request Body** (JSON):

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| `username` | string | ✅ | Username unik |
| `password` | string | ✅ | Password (di-hash dengan `password_hash()`) |
| `full_name` | string | ✅ | Nama lengkap |
| `role` | string | ✅ | `admin` atau `petugas` |
| `petugas_id` | integer | ❌ | ID petugas yang ditautkan ke akun ini |

---

## 4.7 Ringkasan Semua Endpoint

| Metode | Endpoint | Akses | Keterangan |
|--------|----------|-------|-----------|
| `POST` | `/api/auth/login.php` | Publik | Login |
| `POST` | `/api/auth/logout.php` | Semua | Logout |
| `GET` | `/api/auth/me.php` | Semua | Cek sesi aktif |
| `GET` | `/api/laporan/list.php` | admin, petugas | Daftar laporan |
| `GET` | `/api/laporan/detail.php` | admin, petugas | Detail satu laporan |
| `POST` | `/api/laporan/create.php` | admin, petugas | Buat laporan baru |
| `POST` | `/api/laporan/update.php` | admin, petugas | Edit laporan draft |
| `POST` | `/api/laporan/update_status.php` | **admin only** | Ubah status laporan |
| `GET` | `/api/laporan/statistik.php` | admin, petugas | Statistik laporan |
| `GET` | `/api/laporan/gallery.php` | admin, petugas | Galeri foto |
| `GET` | `/api/laporan/suku_cadang.php` | admin, petugas | Riwayat suku cadang |
| `GET` | `/api/laporan/pdf.php` | admin, petugas | Download PDF laporan |
| `GET` | `/api/reference/stasiun.php` | admin, petugas | Master data stasiun |
| `GET` | `/api/reference/petugas.php` | **admin only** | Master data petugas |
| `GET` | `/api/reference/master_data.php` | admin, petugas | Master data referensi |
| `GET` | `/api/users/list.php` | **admin only** | Daftar pengguna |
| `POST` | `/api/users/create.php` | **admin only** | Buat pengguna baru |
