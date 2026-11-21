# Flow Data Ujian - GajiPro
## Dari Setup Ujian hingga Peserta Mengerjakan & Hasil

**Dokumentasi:** Flow lengkap + tabel yang terlibat + contoh data  
**Bahasa:** Bahasa Indonesia  
**Terakhir Update:** 2026-11-16

---

## Table of Contents
1. [Fase 1: Setup Master Data](#fase-1-setup-master-data)
2. [Fase 2: Manajemen Ujian](#fase-2-manajemen-ujian)
3. [Fase 3: Peserta Login & Mulai Ujian](#fase-3-peserta-login--mulai-ujian)
4. [Fase 4: Peserta Mengerjakan Ujian](#fase-4-peserta-mengerjakan-ujian)
5. [Fase 5: Submit & Hasil Ujian](#fase-5-submit--hasil-ujian)
6. [Fase 6: Review & Monitoring](#fase-6-review--monitoring)

---

# FASE 1: SETUP MASTER DATA

## 1.1 Buat Jenis Ujian

**Admin Action:** `Superadmin\JenisUjianController::store()`  
**Form:** Modal di halaman `/superadmin/jenis-ujian`

### Tabel yang Terlibat:
```
panritta_jenis_ujian (main table)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_jenis_ujian
CREATE TABLE panritta_jenis_ujian (
    id BIGINT PRIMARY KEY,
    nama_jenis_ujian VARCHAR(255),        -- "CPNS", "ASN", "SKD", dll
    deskripsi TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data:
INSERT INTO panritta_jenis_ujian (id, nama_jenis_ujian) VALUES
(1, 'CPNS 2024'),
(2, 'ASN Non CPNS'),
(3, 'Ujian Psikologi');
```

**Response di UI:**
```
✓ Jenis ujian berhasil dibuat.
```

---

## 1.2 Buat Sub Jenis Ujian (per Jenis Ujian)

**Admin Action:** `Superadmin\SubJenisUjianController::store()`  
**Form:** Modal di halaman `/superadmin/sub-jenis-ujian`

### Tabel yang Terlibat:
```
panritta_jenis_ujian (parent)
panritta_sub_jenis_ujian (main table)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_sub_jenis_ujian
CREATE TABLE panritta_sub_jenis_ujian (
    id BIGINT PRIMARY KEY,
    jenis_ujian_id BIGINT,                -- FK ke panritta_jenis_ujian
    nama_sub_jenis_ujian VARCHAR(255),    -- "TWK", "TIU", "TKP"
    sistem_penilaian ENUM('benar_salah', 'tiap_jawaban_ada_poin'),  -- scoring method
    nilai_benar DECIMAL(8,2),             -- poin untuk jawaban benar (benar_salah)
    jumlah_jawaban_pilihan_ganda INT,     -- biasanya 5 (A-E)
    urutan INT,                           -- untuk sorting
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data:
INSERT INTO panritta_sub_jenis_ujian (id, jenis_ujian_id, nama_sub_jenis_ujian, sistem_penilaian, nilai_benar, jumlah_jawaban_pilihan_ganda, urutan) VALUES
(1, 1, 'TWK',  'benar_salah', 5, 5, 1),
(2, 1, 'TIU',  'benar_salah', 5, 5, 2),
(3, 1, 'TKP',  'tiap_jawaban_ada_poin', NULL, 5, 3);
-- TKP pakai tiap jawaban ada poin (1-5 per opsi), bukan benar/salah
```

**Relasi:**
- Jenis Ujian "CPNS 2024" → 3 Sub Jenis (TWK, TIU, TKP)
- Jenis Ujian "ASN Non CPNS" → bisa punya Sub Jenis sendiri

---

## 1.3 Buat Sub Indikator (Kategori Soal)

**Admin Action:** `Superadmin\SubIndikatorController::store()`  
**Form:** Modal di halaman `/superadmin/sub-indikator`

### Tabel yang Terlibat:
```
panritta_jenis_ujian (grandparent)
panritta_sub_jenis_ujian (parent)
panritta_sub_indikator (main table)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_sub_indikator
CREATE TABLE panritta_sub_indikator (
    id BIGINT PRIMARY KEY,
    sub_jenis_ujian_id BIGINT,            -- FK ke panritta_sub_jenis_ujian
    jenis_ujian_id BIGINT,                -- FK ke panritta_jenis_ujian (shortcut)
    nama_sub_indikator VARCHAR(255),      -- kategori soal
    deskripsi TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data (untuk TWK):
INSERT INTO panritta_sub_indikator (id, sub_jenis_ujian_id, jenis_ujian_id, nama_sub_indikator) VALUES
(1, 1, 1, 'Pancasila'),
(2, 1, 1, 'UUD 1945'),
(3, 1, 1, 'Pelanggaran HAM');

-- INSERT contoh data (untuk TKP):
INSERT INTO panritta_sub_indikator (id, sub_jenis_ujian_id, jenis_ujian_id, nama_sub_indikator) VALUES
(10, 3, 1, 'Integritas'),
(11, 3, 1, 'Disiplin'),
(12, 3, 1, 'Kerjasama');
```

**Hirarki:**
```
Jenis Ujian: CPNS 2024
├── Sub Jenis: TWK (benar_salah)
│   ├── Sub Indikator: Pancasila
│   ├── Sub Indikator: UUD 1945
│   └── Sub Indikator: Pelanggaran HAM
├── Sub Jenis: TIU (benar_salah)
│   ├── Sub Indikator: Analogi
│   ├── Sub Indikator: Logika
│   └── Sub Indikator: Numerik
└── Sub Jenis: TKP (tiap_jawaban_ada_poin)
    ├── Sub Indikator: Integritas
    ├── Sub Indikator: Disiplin
    └── Sub Indikator: Kerjasama
```

---

## 1.4 Buat Soal (ke Bank Soal)

**Admin Action:** `Superadmin\SoalController::store()` atau bulk import  
**Form:** Form halaman `/superadmin/soal/create` atau `/superadmin/soal/import`

### Tabel yang Terlibat:
```
panritta_sub_indikator (parent)
panritta_soal (main table)
users (pembuat_soal_id, user_updated_by)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_soal
CREATE TABLE panritta_soal (
    id BIGINT PRIMARY KEY,
    sub_indikator_id BIGINT,              -- FK ke panritta_sub_indikator
    pembuat_soal_id BIGINT,               -- FK ke users (pembuat)
    user_updated_by BIGINT,               -- FK ke users (terakhir edit)
    
    -- Konten soal (HTML, stored ESCAPED di DB)
    soal LONGTEXT,                        -- "&lt;p&gt;Apa ibukota Indonesia?&lt;/p&gt;"
    opsi_a VARCHAR(1000),
    opsi_b VARCHAR(1000),
    opsi_c VARCHAR(1000),
    opsi_d VARCHAR(1000),
    opsi_e VARCHAR(1000),
    pembahasan LONGTEXT,                  -- penjelasan jawaban
    
    -- Gambar (path ke file di storage)
    gambar_soal VARCHAR(500),             -- path: panritta/soal/xyz.jpg
    gambar_opsi_a VARCHAR(500),
    gambar_opsi_b VARCHAR(500),
    gambar_opsi_c VARCHAR(500),
    gambar_opsi_d VARCHAR(500),
    gambar_opsi_e VARCHAR(500),
    gambar_pembahasan VARCHAR(500),
    
    -- Penilaian: Benar-Salah
    kunci_jawaban CHAR(1),                -- "A", "B", "C", "D", "E", NULL
    nilai_bobot_benar DECIMAL(8,2),       -- poin jika benar (default 5)
    
    -- Penilaian: Tiap Jawaban Ada Poin
    nilai_bobot_a DECIMAL(8,2),           -- poin opsi A (1-5)
    nilai_bobot_b DECIMAL(8,2),           -- poin opsi B (1-5)
    nilai_bobot_c DECIMAL(8,2),           -- poin opsi C (1-5)
    nilai_bobot_d DECIMAL(8,2),           -- poin opsi D (1-5)
    nilai_bobot_e DECIMAL(8,2),           -- poin opsi E (1-5)
    
    -- UI rendering
    tipe_tampil ENUM('horizontal', 'vertikal'),  -- layout opsi
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data (untuk TWK - Benar Salah):
INSERT INTO panritta_soal (
    id, sub_indikator_id, pembuat_soal_id, 
    soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e,
    kunci_jawaban, nilai_bobot_benar, tipe_tampil
) VALUES
(101, 1, 5,
    '&lt;p&gt;Apa ibukota Indonesia?&lt;/p&gt;',
    'Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta',
    'A', 5, 'horizontal'),
(102, 2, 5,
    '&lt;p&gt;Pasal berapa UUD 1945 mengatur HAM?&lt;/p&gt;',
    'Pasal 27', 'Pasal 28', 'Pasal 29', 'Pasal 30', 'Pasal 31',
    'B', 5, 'horizontal');

-- INSERT contoh data (untuk TKP - Tiap Jawaban Ada Poin):
INSERT INTO panritta_soal (
    id, sub_indikator_id, pembuat_soal_id,
    soal, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e,
    nilai_bobot_a, nilai_bobot_b, nilai_bobot_c, nilai_bobot_d, nilai_bobot_e,
    tipe_tampil
) VALUES
(201, 10, 5,
    '&lt;p&gt;Seberapa penting integritas dalam pekerjaan?&lt;/p&gt;',
    'Sangat penting', 'Penting', 'Cukup penting', 'Kurang penting', 'Tidak penting',
    5, 4, 3, 2, 1, 'vertikal');
```

**Catatan:**
- `soal`, `pembahasan` disimpan **ESCAPED** di DB (`&lt;p&gt;` bukan `<p>`)
- Konten bisa punya **formula KaTeX**: `[[\\frac{3}{5}]]` akan di-render ke math display
- File gambar disimpan di `public/uploads/panritta/soal/`
- Soal bisa punya **kombinasi**: HTML + formula + gambar

---

# FASE 2: MANAJEMEN UJIAN

## 2.1 Buat Ujian (Master)

**Admin Action:** `Superadmin\UjianController::store()`  
**Form:** Form halaman `/superadmin/ujian/create`

### Tabel yang Terlibat:
```
panritta_ujian (main table)
panritta_jenis_ujian (N-to-M via pivot)
users (dibuat_oleh, user_updated_by)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_ujian
CREATE TABLE panritta_ujian (
    id BIGINT PRIMARY KEY,
    
    -- Basic info
    nama_ujian VARCHAR(255),              -- "CPNS 2024 Batch 1"
    tipe_ujian ENUM('online', 'offline_kelas'),  -- mode ujian
    jumlah_soal INT,                      -- kapasitas total soal
    status ENUM('draft', 'aktif', 'selesai'),  -- status ujian
    
    -- Soal config
    acak_soal BOOLEAN,                    -- acak urutan soal?
    tampilkan_hasil BOOLEAN,              -- tampilkan hasil ke peserta?
    
    -- Offline ujian khusus
    tanggal_ujian DATETIME,               -- kapan ujian dimulai
    durasi_ujian INT,                     -- durasi dalam menit (misal 180)
    batas_keterlambatan DATETIME,         -- sampai jam berapa boleh mulai
    token_ujian VARCHAR(10),              -- token akses offline (misal: "ABC123")
    
    -- Online ujian khusus
    akses_member JSON,                    -- ["Free", "Basic", "Pro"] (array)
    
    -- Workflow
    finalized_at DATETIME,                -- kapan soal final di-lock? (atau NULL jika draft)
    dibuat_oleh BIGINT,                   -- FK ke users
    user_updated_by BIGINT,               -- FK ke users
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data (ujian online):
INSERT INTO panritta_ujian (
    id, nama_ujian, tipe_ujian, jumlah_soal, status,
    acak_soal, tampilkan_hasil, dibuat_oleh, user_updated_by
) VALUES
(1, 'CPNS 2024 Batch 1', 'online', 100, 'draft',
    TRUE, TRUE, 5, 5);

-- INSERT contoh data (ujian offline):
INSERT INTO panritta_ujian (
    id, nama_ujian, tipe_ujian, jumlah_soal, status,
    tanggal_ujian, durasi_ujian, batas_keterlambatan, token_ujian,
    acak_soal, tampilkan_hasil, dibuat_oleh, user_updated_by
) VALUES
(2, 'Ujian Seleksi Lokal Ruang 1', 'offline_kelas', 50, 'draft',
    '2026-11-20 08:00:00', 120, '2026-11-20 09:00:00', 'XYZ789',
    FALSE, TRUE, 5, 5);
```

---

## 2.2 Link Jenis Ujian ke Ujian (Many-to-Many)

**Admin Action:** `Superadmin\UjianController::store()` atau `update()`  
**Form:** Multiselect di form ujian (checkbox "TWK", "TIU", "TKP")

### Tabel yang Terlibat:
```
panritta_ujian (parent)
panritta_jenis_ujian (parent)
panritta_ujian_jenis_ujian (pivot table M-to-M)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_ujian_jenis_ujian (Pivot)
CREATE TABLE panritta_ujian_jenis_ujian (
    ujian_id BIGINT,                      -- FK ke panritta_ujian
    jenis_ujian_id BIGINT,                -- FK ke panritta_jenis_ujian
    passing_grade DECIMAL(8,2),           -- nilai minimum lulus untuk jenis ini (misal 60)
    PRIMARY KEY (ujian_id, jenis_ujian_id)
);

-- INSERT contoh data:
INSERT INTO panritta_ujian_jenis_ujian (ujian_id, jenis_ujian_id, passing_grade) VALUES
(1, 1, 60),    -- Ujian 1 "CPNS 2024" include jenis "CPNS 2024", passing 60
(1, 2, 70);    -- Ujian 1 juga include jenis "ASN Non CPNS", passing 70

-- INSERT contoh data (offline):
INSERT INTO panritta_ujian_jenis_ujian (ujian_id, jenis_ujian_id, passing_grade) VALUES
(2, 1, 65);    -- Ujian 2 "Ujian Seleksi Lokal" include jenis "CPNS 2024", passing 65
```

**Data Flow:**
1. Admin pilih checkbox "TWK", "TIU", "TKP" di form
2. Request: `jenis_ujian_id: [1, 2, 3]`, `passing_grade: [60, 60, 55]`
3. Laravel `sync()` create/update pivot records
4. Result: Ujian linked ke 3 jenis ujian dengan passing grades berbeda

---

## 2.3 Tambah Soal ke Ujian (Assembly)

**Admin Action:** `Superadmin\UjianSoalController::attach()` atau `importExcel()`  
**Form:** Modal "Tambah dari Bank" atau upload file Excel

### Tabel yang Terlibat:
```
panritta_ujian (parent)
panritta_soal (parent)
panritta_ujian_soal (pivot table)
```

### Schema & Contoh Data:

```sql
-- Table: panritta_ujian_soal (Pivot - Assembly)
CREATE TABLE panritta_ujian_soal (
    id BIGINT PRIMARY KEY,
    ujian_id BIGINT,                      -- FK ke panritta_ujian
    soal_id BIGINT,                       -- FK ke panritta_soal
    jenis_ujian_id BIGINT,                -- FK ke panritta_jenis_ujian (shortcut)
    urutan INT,                           -- nomor soal (1, 2, 3, ...)
    created_at TIMESTAMP
);

-- INSERT contoh data:
INSERT INTO panritta_ujian_soal (ujian_id, soal_id, jenis_ujian_id, urutan) VALUES
(1, 101, 1, 1),   -- Ujian 1, Soal 101 (TWK), urutan nomor 1
(1, 102, 1, 2),   -- Ujian 1, Soal 102 (TWK), urutan nomor 2
(1, 201, 3, 3),   -- Ujian 1, Soal 201 (TKP), urutan nomor 3
(1, 202, 3, 4),   -- Ujian 1, Soal 202 (TKP), urutan nomor 4
...
(1, 350, 1, 100); -- Ujian 1, Soal 350 (TWK), urutan nomor 100 (final)
```

**Alur Admin:**
```
1. Buka /superadmin/ujian/1/soal
2. Tab pilih jenis ujian (TWK, TIU, TKP)
3. Klik "Tambah dari Bank"
4. Search & pilih soal (misal: soal 101, 102)
5. Click "Tambah" → create ujian_soal records
6. Urutan auto-assign (nomer 1, 2, 3, ...)
```

---

## 2.4 Finalisasi Ujian (Lock Soal)

**Admin Action:** `Superadmin\UjianSoalController::finalize()`  
**Form:** Button "Finalisasi" di halaman `/superadmin/ujian/{ujian}/soal/finalisasi`

### Tabel yang Terlibat:
```
panritta_ujian (update field)
```

### SQL Update:

```sql
-- Sebelum:
UPDATE panritta_ujian SET finalized_at = NULL WHERE id = 1;

-- Sesudah finalize:
UPDATE panritta_ujian SET finalized_at = '2026-11-16 10:30:00' WHERE id = 1;
```

**Makna:**
- `finalized_at IS NULL` → Ujian masih bisa edit soal (DRAFT)
- `finalized_at IS NOT NULL` → Ujian LOCKED, soal tidak boleh diubah

---

## 2.5 Aktifkan Ujian (Buka untuk Peserta)

**Admin Action:** `Superadmin\UjianController::activate()`  
**Form:** Button "Aktifkan" di `/superadmin/ujian`

### Tabel yang Terlibat:
```
panritta_ujian (update status)
```

### SQL Update:

```sql
-- Sebelum:
UPDATE panritta_ujian SET status = 'draft' WHERE id = 1;

-- Sesudah activate:
UPDATE panritta_ujian SET status = 'aktif' WHERE id = 1;

-- Validasi:
-- 1. finalized_at must NOT be NULL (soal harus sudah final)
-- 2. Jika offline: generate token jika belum ada
```

**Status Ujian:**
- `draft` → sedang setup, peserta tidak bisa lihat
- `aktif` → sedang berlangsung, peserta bisa login & ujian
- `selesai` → ujian ditutup, hanya bisa lihat hasil

---

# FASE 3: PESERTA LOGIN & MULAI UJIAN

## 3.1 Skenario: Peserta Online (Register di Platform)

### Tabel yang Terlibat:
```
users (peserta user account)
panritta_ujian (check status = aktif)
panritta_ujian_peserta (create/get attempt record)
```

### User Registration (Background):

```sql
-- Table: users
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),                    -- "Andi Pratama"
    email VARCHAR(255),                   -- "andi@example.com"
    username VARCHAR(255),                -- "andi.pratama"
    password_hash VARCHAR(255),           -- bcrypt hash
    role_id BIGINT,                       -- FK ke roles (peserta role)
    company_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data:
INSERT INTO users (id, name, email, username, password_hash, role_id, company_id) VALUES
(100, 'Andi Pratama', 'andi@example.com', 'andi.pratama', '$2y$10$...hash...', 2, 1);
-- role_id 2 = peserta role
```

---

## 3.2 Peserta Membuka Halaman Ujian

**User Action:** Buka `/ujian`  
**Controller:** `Peserta\UjianController::index()` atau list semua active ujian

### Query:
```php
$ujians = Ujian::where('status', 'aktif')->get();
```

**Response di UI:**
```
Daftar Ujian Tersedia:
- CPNS 2024 Batch 1 (Online)
- Ujian Seleksi Lokal Ruang 1 (Offline)
```

---

## 3.3 Peserta Klik "Mulai Ujian" - Tampil Konfirmasi

**User Action:** Klik tombol "Mulai" pada ujian tertentu  
**Controller:** `Peserta\UjianController::show()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (check existing attempt)
```

### Query:
```php
$peserta = $ujian->peserta()
    ->where('user_id', auth()->id())
    ->first();
    
// Jika sudah ada attempt:
if ($peserta && $peserta->status === 'selesai') {
    redirect('/ujian/{ujian}/hasil');  // Sudah selesai, lihat hasil
}
```

**UI Tampilkan:**
```
Konfirmasi Mulai Ujian:
- Nama Ujian: CPNS 2024 Batch 1
- Durasi: 180 menit
- Total Soal: 100
- Peringatan: Jangan refresh halaman, dll

[Tombol: Saya Setuju, Mulai Ujian]
```

---

## 3.4 Peserta Klik "Mulai Ujian" - Submit Konfirmasi

**User Action:** Klik "Saya Setuju, Mulai Ujian"  
**Controller:** `Peserta\UjianController::start()`  
**HTTP Method:** POST

### Tabel yang Terlibat:
```
panritta_ujian (check status, durasi)
panritta_ujian_peserta (create or get attempt)
```

### Logic:

```php
1. Validasi:
   - Ujian status = 'aktif' ?
   - User sudah punya attempt status 'sedang_ujian' atau 'terdaftar' ?

2. Jika ONLINE:
   - Buat peserta record jika belum ada:
     ujian->peserta()->create([
         'user_id' => auth()->id(),
         'status' => 'sedang_ujian',
         'waktu_mulai' => now(),
     ])
   - Status: terdaftar → sedang_ujian
   
3. Jika OFFLINE:
   - Token validasi (user input token offline)
   - Check user status tidak diblokir

4. Redirect ke /ujian/{ujian}/kerjakan
```

### SQL Insert:

```sql
-- Table: panritta_ujian_peserta
CREATE TABLE panritta_ujian_peserta (
    id BIGINT PRIMARY KEY,
    ujian_id BIGINT,                      -- FK ke panritta_ujian
    user_id BIGINT,                       -- FK ke users (NULL jika offline)
    peserta_offline_id BIGINT,            -- FK ke panritta_peserta_offline (NULL jika online)
    status ENUM('terdaftar', 'sedang_ujian', 'selesai'),
    
    -- Waktu
    waktu_mulai DATETIME,                 -- kapan peserta mulai
    waktu_selesai DATETIME,               -- kapan peserta submit/timeout
    
    -- Pause info (untuk offline monitoring)
    is_paused BOOLEAN DEFAULT FALSE,      -- dipaused supervisor?
    paused_at DATETIME,                   -- kapan di-pause?
    sisa_detik_saat_pause INT,            -- sisa waktu saat pause
    
    -- Activity tracking
    last_activity_at DATETIME,            -- heartbeat terakhir
    
    -- Hasil
    total_nilai DECIMAL(8,2),             -- nilai akhir
    lulus BOOLEAN,                        -- lulus atau tidak?
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data (peserta online mulai ujian):
INSERT INTO panritta_ujian_peserta (
    id, ujian_id, user_id, status,
    waktu_mulai, last_activity_at
) VALUES
(1000, 1, 100, 'sedang_ujian',
    '2026-11-16 10:00:00', '2026-11-16 10:00:00');
    
-- INSERT contoh data (peserta offline):
INSERT INTO panritta_ujian_peserta (
    id, ujian_id, peserta_offline_id, status,
    waktu_mulai, last_activity_at
) VALUES
(1001, 2, 1, 'sedang_ujian',
    '2026-11-16 08:00:00', '2026-11-16 08:00:00');
```

**Response:**
- Redirect ke `/ujian/1/kerjakan` (halaman soal)

---

# FASE 4: PESERTA MENGERJAKAN UJIAN

## 4.1 Halaman Kerjakan Ujian - Load Soal

**User Action:** Halaman `/ujian/{ujian}/kerjakan` dimuat  
**Controller:** `Peserta\UjianController::kerjakan()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (load attempt info)
panritta_ujian (check durasi, deadline)
panritta_ujian_soal (fetch semua soal)
panritta_soal (konten soal)
panritta_ujian_jawaban (load jawaban peserta yang sudah ada)
```

### Query:

```php
// Get attempt info
$peserta = $ujian->peserta()
    ->where('user_id', auth()->id())
    ->firstOrFail();

// Check deadline
if ($this->deadlinePassed($ujian, $peserta)) {
    $this->autoSubmit($peserta);
    redirect('/ujian/{ujian}/hasil');
}

// Get soal dalam ujian
$ujianSoals = $ujian->ujianSoals()
    ->with('soal')
    ->orderBy('urutan')
    ->get();
    
// Get jawaban yang sudah dijawab
$jawaban = $peserta->jawaban()->pluck('jawaban', 'ujian_soal_id');

// Hitung sisa waktu
$sisaDetik = now()->diffInSeconds($ujian->waktu_selesai);
```

### UI Tampilkan:

```
┌─────────────────────────────────────────────┐
│ CPNS 2024 Batch 1                          │
│ Sisa Waktu: 2:45:30 (countdown timer)       │
│ Soal 1 / 100                                │
│ ✓ Jawaban: 50 | ○ Belum: 50                │
└─────────────────────────────────────────────┘

[Soal Content]
Apa ibukota Indonesia?
◉ Jakarta  ○ Bandung  ○ Surabaya  ○ Semarang  ○ Yogyakarta

[Navigasi]
< Sebelumnya  |  Nomor 1, 2, 3, ..., 100  |  Berikutnya >

[Tombol]
[RAGU-RAGU]  [HAPUS]  [SUBMIT UJIAN]
```

---

## 4.2 Peserta Pilih Jawaban

**User Action:** Klik opsi jawaban (misal: klik "Jakarta")  
**Controller:** Tidak ada page load - AJAX!  
**HTTP Method:** POST (via JavaScript)

### Tabel yang Terlibat:
```
panritta_ujian_jawaban (insert/update)
panritta_ujian_peserta (update last_activity_at)
```

### JavaScript AJAX Call:

```javascript
// Di frontend (Alpine.js / Vanilla JS)
fetch('/ujian/1/jawaban', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
    },
    body: JSON.stringify({
        ujian_soal_id: 1,      // ujian_soal.id
        jawaban: 'A'            // pilih opsi A (Jakarta)
    })
})
.then(response => response.json())
.then(data => {
    if (data.saved) {
        // Update UI: tampilkan checkmark ✓
        console.log('Jawaban tersimpan');
    }
});
```

### Controller Logic (`Peserta\UjianController::saveAnswer()`):

```php
public function saveAnswer(StoreJawabanRequest $request, Ujian $ujian)
{
    $peserta = $request->resolvePeserta();
    $validated = $request->validated();
    
    // Get soal detail untuk hitung poin
    $ujianSoal = $ujian->ujianSoals()
        ->with('soal')
        ->findOrFail($validated['ujian_soal_id']);
    
    // Hitung poin (benar/salah atau gradasi 1-5)
    $score = $this->scoring->scoreAnswer(
        $ujianSoal->soal,
        $validated['jawaban'],
        $ujian,
        $peserta
    );
    
    // Simpan/update jawaban
    $peserta->jawaban()->updateOrCreate(
        ['ujian_soal_id' => $ujianSoal->id],
        [
            'soal_id' => $ujianSoal->soal_id,
            'jenis_ujian_id' => $ujianSoal->jenis_ujian_id,
            'jawaban' => $validated['jawaban'],
            'nilai' => $score['nilai'],
            'benar' => $score['benar'],
        ]
    );
    
    // Update last activity
    $peserta->forceFill(['last_activity_at' => now()])->save();
    
    return response()->json(['saved' => true]);
}
```

### SQL Insert/Update:

```sql
-- Table: panritta_ujian_jawaban
CREATE TABLE panritta_ujian_jawaban (
    id BIGINT PRIMARY KEY,
    ujian_peserta_id BIGINT,              -- FK ke panritta_ujian_peserta
    ujian_soal_id BIGINT,                 -- FK ke panritta_ujian_soal
    soal_id BIGINT,                       -- FK ke panritta_soal (shortcut)
    jenis_ujian_id BIGINT,                -- FK ke panritta_jenis_ujian (shortcut)
    
    jawaban CHAR(1),                      -- "A", "B", "C", "D", "E"
    nilai DECIMAL(8,2),                   -- poin untuk jawaban ini
    benar BOOLEAN,                        -- true/false (untuk stat)
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- INSERT contoh data (peserta jawab soal 1):
INSERT INTO panritta_ujian_jawaban (
    ujian_peserta_id, ujian_soal_id, soal_id, jenis_ujian_id,
    jawaban, nilai, benar
) VALUES
(1000, 1, 101, 1, 'A', 5, TRUE);   -- Benar: 5 poin

-- INSERT contoh data (peserta jawab soal 3 dengan TKP):
INSERT INTO panritta_ujian_jawaban (
    ujian_peserta_id, ujian_soal_id, soal_id, jenis_ujian_id,
    jawaban, nilai, benar
) VALUES
(1000, 3, 201, 3, 'B', 4, FALSE);  -- TKP: opsi B = 4 poin (tidak ada "benar/salah", cuma gradasi)
```

**Response JSON:**
```json
{
    "saved": true
}
```

**UI Update:**
- Checkmark ✓ muncul di nomor soal
- Counter "Jawaban: 51 | Belum: 49" update
- Tidak ada page reload

---

## 4.3 Peserta Lanjut Soal Berikutnya

**User Action:** Klik tombol "Berikutnya" atau klik nomor soal lain  
**Action:** JavaScript ganti soal di UI (atau micro-reload soal saja)

### Tidak ada INSERT/UPDATE - Cuma load soal berikutnya

```javascript
// Frontend: fetch soal berikutnya (AJAX)
fetch('/ujian/1/soal/2', {
    headers: { 'X-CSRF-TOKEN': token }
})
.then(response => response.json())
.then(soal => {
    // Update UI dengan soal #2
    document.getElementById('soal-content').innerHTML = soal.content;
    document.getElementById('opsi').innerHTML = soal.options;
});
```

---

## 4.4 Heartbeat - Peserta Masih Online

**Browser Action:** Setiap 5-10 detik, JavaScript kirim heartbeat ping  
**Controller:** `Peserta\UjianController::heartbeat()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (update last_activity_at)
```

### AJAX Call:

```javascript
setInterval(function() {
    fetch('/ujian/1/heartbeat', {
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': token }
    })
    .then(response => response.json())
    .then(data => {
        if (data.expired) {
            // Ujian timeout / deadline habis
            alert('Batas waktu ujian habis');
            window.location.href = data.redirect;
        }
    });
}, 10000);  // every 10 seconds
```

### Logic:

```php
public function heartbeat(Request $request, Ujian $ujian)
{
    $peserta = $this->resolvePeserta($request, $ujian);
    
    if (!$peserta || $peserta->status === 'selesai') {
        return response()->json([
            'expired' => true,
            'message' => 'Ujian sudah selesai',
            'redirect' => route('peserta.ujian.hasil', $ujian)
        ]);
    }
    
    // Check deadline
    if ($this->deadlinePassed($ujian, $peserta)) {
        $this->autoSubmit($peserta);
        return response()->json([
            'expired' => true,
            'message' => 'Batas waktu habis',
            'redirect' => route('peserta.ujian.hasil', $ujian)
        ]);
    }
    
    // Update last activity
    $peserta->update(['last_activity_at' => now()]);
    
    return response()->json([
        'expired' => false,
        'sisa_detik' => $this->sisaDetik($ujian, $peserta)
    ]);
}
```

### SQL Update:

```sql
UPDATE panritta_ujian_peserta
SET last_activity_at = '2026-11-16 10:05:30'
WHERE id = 1000;
```

---

# FASE 5: SUBMIT & HASIL UJIAN

## 5.1 Peserta Klik "SUBMIT UJIAN"

**User Action:** Klik tombol "SUBMIT UJIAN" (atau timeout otomatis)  
**Controller:** `Peserta\UjianController::submit()` atau autoSubmit  
**HTTP Method:** POST

### Tabel yang Terlibat:
```
panritta_ujian_peserta (update status, waktu_selesai, total_nilai, lulus)
panritta_ujian_jawaban (sudah ada, cuma untuk hitung total)
```

### Logic:

```php
public function submit(Request $request, Ujian $ujian)
{
    $peserta = $this->resolvePeserta($request, $ujian);
    
    // Finalize ujian peserta
    $this->scoring->finalize($peserta);
    
    // Update status
    $peserta->update([
        'status' => 'selesai',
        'waktu_selesai' => now(),
    ]);
    
    // Hitung total nilai + lulus status
    $hasil = $this->scoring->calculateScore($peserta, $ujian);
    
    $peserta->update([
        'total_nilai' => $hasil['total_nilai'],
        'lulus' => $hasil['lulus'],
    ]);
    
    return redirect()->route('peserta.ujian.hasil', $ujian);
}
```

### SQL Update:

```sql
UPDATE panritta_ujian_peserta
SET
    status = 'selesai',
    waktu_selesai = '2026-11-16 11:45:30',
    total_nilai = 72.5,
    lulus = TRUE
WHERE id = 1000;
```

---

## 5.2 Hitung Skor (UjianScoringService)

### Logic:

```php
public function calculateScore(UjianPeserta $peserta, Ujian $ujian)
{
    // Get semua jawaban peserta
    $jawaban = $peserta->jawaban()->get();
    
    // Group by jenis_ujian untuk breakdown
    $breakdown = [];
    $totalNilai = 0;
    
    foreach ($ujian->ujianJenisUjians as $ujianJenis) {
        $jenis = $ujianJenis->jenisUjian;
        $passingGrade = $ujianJenis->passing_grade;
        
        // Sum nilai untuk jenis ini
        $nilaiJenis = $jawaban
            ->where('jenis_ujian_id', $jenis->id)
            ->sum('nilai');
        
        $maxNilai = /* hitung max possible nilai jenis ini */;
        $persen = ($maxNilai > 0) ? ($nilaiJenis / $maxNilai) * 100 : 0;
        $luluJenis = $persen >= $passingGrade;
        
        $breakdown[$jenis->nama_jenis_ujian] = [
            'nilai' => $nilaiJenis,
            'persen' => $persen,
            'passing_grade' => $passingGrade,
            'lulus' => $luluJenis,
        ];
        
        $totalNilai += $nilaiJenis;
    }
    
    // Overall lulus = semua jenis lulus
    $luluOverall = collect($breakdown)->every(fn($b) => $b['lulus']);
    
    return [
        'total_nilai' => $totalNilai,
        'breakdown' => $breakdown,
        'lulus' => $luluOverall,
    ];
}
```

### Contoh Hasil Hitung:

```
Ujian: CPNS 2024 Batch 1
Total 100 soal, 50 soal TWK + 50 soal TKP

Breakdown:
┌─────────────────────────────────────────┐
│ TWK (Benar-Salah, 50 soal)              │
│ Jawaban Benar: 40 × 5 poin = 200        │
│ Nilai Maksimal: 50 × 5 = 250            │
│ Nilai Peserta: 200                      │
│ Persentase: 200/250 = 80%               │
│ Passing Grade: 60%                      │
│ Status: ✓ LULUS                         │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ TKP (Tiap Jawaban Ada Poin, 50 soal)    │
│ Pilihan: A(5pt)=15, B(4pt)=20, C(3pt)=10, D=5│
│ Total: 50 × 3 (avg) = 150               │
│ Nilai Maksimal: 50 × 5 = 250            │
│ Nilai Peserta: 150                      │
│ Persentase: 150/250 = 60%               │
│ Passing Grade: 55%                      │
│ Status: ✓ LULUS                         │
└─────────────────────────────────────────┘

TOTAL:
Nilai: 200 + 150 = 350
Maksimal: 250 + 250 = 500
Persentase: 70%
Status Keseluruhan: ✓ LULUS
```

---

## 5.3 Peserta Lihat Hasil Ujian

**User Action:** Halaman `/ujian/{ujian}/hasil`  
**Controller:** `Peserta\UjianController::hasil()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (load hasil)
panritta_ujian_jawaban (untuk stat jawaban)
```

### UI Tampilkan:

```
╔════════════════════════════════════════════╗
║ HASIL UJIAN ANDA                           ║
║ CPNS 2024 Batch 1                          ║
╚════════════════════════════════════════════╝

Nama Peserta: Andi Pratama
Waktu Mulai: 16 Nov 2026, 10:00
Waktu Selesai: 16 Nov 2026, 11:45
Durasi: 1 jam 45 menit

┌─────────────────────────┐
│ NILAI TOTAL: 350 / 500  │
│ PERSENTASE: 70%         │
│ STATUS: ✓ LULUS         │
└─────────────────────────┘

BREAKDOWN PER JENIS UJIAN:

▓░░░░░░░░░░░░░░░░░░░░░░░ (80%)
TWK - Nilai: 200 / 250
Passing Grade: 60% ✓ LULUS

▓▓░░░░░░░░░░░░░░░░░░░░░░ (60%)
TKP - Nilai: 150 / 250
Passing Grade: 55% ✓ LULUS

[Tombol: Lihat Pembahasan]  [Lihat Ranking]
```

---

# FASE 6: REVIEW & MONITORING

## 6.1 Admin Monitor Ujian Berlangsung

**Admin Action:** Buka `/superadmin/hasil-ujian`  
**Controller:** `Superadmin\HasilUjianController::management()`

### Tabel yang Terlibat:
```
panritta_ujian (filter active)
panritta_ujian_peserta (where status = sedang_ujian)
```

### Query:

```php
$pesertaSedangUjian = $ujian->peserta()
    ->where('status', 'sedang_ujian')
    ->with('user', 'pesertaOffline', 'jawaban')
    ->latest('waktu_mulai')
    ->get()
    ->map(function ($peserta) use ($ujian) {
        return [
            'id' => $peserta->id,
            'nama' => $peserta->user?->name ?? $peserta->pesertaOffline?->nama_peserta,
            'sisa_detik' => $this->calculateSisaDetik($peserta, $ujian),
            'jumlah_jawaban' => $peserta->jawaban()->whereNotNull('jawaban')->count(),
            'total_soal' => $ujian->ujianSoals()->count(),
            'is_paused' => $peserta->is_paused,
        ];
    });
```

### UI Tampilkan:

```
MONITOR PESERTA SEDANG UJIAN - CPNS 2024 Batch 1

┌──────────────────────────────────────────────────────┐
│ Nama Peserta      │ Sisa Waktu │ Jawaban │ Aksi       │
├──────────────────────────────────────────────────────┤
│ Andi Pratama      │  1:32:45   │ 45/100  │ ⏸ ⏹      │
│ Budi Santoso      │  0:45:20   │ 80/100  │ ⏸ ⏹      │
│ Citra Dewi        │ ⏸ Paused   │ 30/100  │ ▶ ⏹      │
│ Doni Wijaya       │  2:10:00   │ 100/100 │ ⏸ ⏹      │
└──────────────────────────────────────────────────────┘

⏸ = Pause ujian  |  ▶ = Resume  |  ⏹ = Force Stop
```

---

## 6.2 Admin Pause / Resume Peserta

**Admin Action:** Klik tombol "⏸ Pause"  
**Controller:** `Superadmin\HasilUjianController::togglePause()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (update is_paused, paused_at, sisa_detik_saat_pause)
```

### Logic:

```php
if ($peserta->is_paused) {
    // Resume
    $peserta->update([
        'is_paused' => false,
        'paused_at' => null,
    ]);
} else {
    // Pause
    $sisaDetik = $this->calculateSisaDetik($peserta, $ujian);
    $peserta->update([
        'is_paused' => true,
        'paused_at' => now(),
        'sisa_detik_saat_pause' => $sisaDetik,
    ]);
}
```

### SQL Update:

```sql
-- Pause
UPDATE panritta_ujian_peserta
SET
    is_paused = TRUE,
    paused_at = '2026-11-16 10:30:00',
    sisa_detik_saat_pause = 3600
WHERE id = 1000;

-- Resume
UPDATE panritta_ujian_peserta
SET
    is_paused = FALSE,
    paused_at = NULL
WHERE id = 1000;
```

**Efek di Peserta:**
- Peserta paused → ketika login ditampilkan pesan "Ujian Anda di-pause, sisa waktu: 1 jam"
- Saat di-pause, timer tidak jalan (frozen)
- Saat resume, timer lanjut dari sisa waktu yang tersimpan

---

## 6.3 Admin Lihat Analisis Soal

**Admin Action:** Buka `/superadmin/hasil-ujian/{ujian}/soal-analysis`  
**Controller:** `Superadmin\HasilUjianController::soalAnalysis()`

### Tabel yang Terlibat:
```
panritta_ujian_soal (per soal)
panritta_ujian_jawaban (hitung distribusi jawaban)
```

### SQL Query:

```sql
-- Statistik jawaban untuk soal #1
SELECT 
    jawaban,
    COUNT(*) as total,
    SUM(CASE WHEN benar = 1 THEN 1 ELSE 0 END) as benar_count
FROM panritta_ujian_jawaban
WHERE ujian_soal_id = 1
  AND ujian_peserta_id IN (
      SELECT id FROM panritta_ujian_peserta
      WHERE ujian_id = 1 AND status = 'selesai'
  )
GROUP BY jawaban;

-- Result contoh:
┌─────────┬───────┬─────────────┐
│ jawaban │ total │ benar_count │
├─────────┼───────┼─────────────┤
│ A       │  45   │  42         │  ← Jawaban benar = 42/45 (93%)
│ B       │   3   │   0         │
│ C       │   2   │   0         │
│ D       │   0   │   0         │
│ E       │   0   │   0         │
└─────────┴───────┴─────────────┘
```

### UI Tampilkan:

```
ANALISIS SOAL - CPNS 2024 Batch 1

Soal #1: "Apa ibukota Indonesia?"
Kunci Jawaban: A (Jakarta)

Distribusi Jawaban (50 peserta selesai):
A: ▓▓▓▓▓▓▓▓▓ (45 orang, 90%) ✓ Benar: 42/45 (93%)
B: ▓ (3 orang, 6%) ✗ Benar: 0/3 (0%)
C: ▓ (2 orang, 4%) ✗ Benar: 0/2 (0%)

Status Soal: ✓ Baik (persentase jawab benar 93% > 70%)

---

Soal #25: "Ibu kota Jawa Tengah?"
Kunci Jawaban: B (Semarang)

Distribusi Jawaban:
A: ▓▓ (10 orang, 20%) ✗ Benar: 0/10 (0%)
B: ▓▓▓▓▓ (25 orang, 50%) ✓ Benar: 24/25 (96%)
C: ▓▓▓ (12 orang, 24%) ✗ Benar: 0/12 (0%)
D: ▓ (3 orang, 6%) ✗ Benar: 0/3 (0%)

Status Soal: ⚠ Perlu Review (distribusi tidak merata, 20% pilih A)
```

---

## 6.4 Admin Review Detail Peserta

**Admin Action:** Buka `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/review`  
**Controller:** `Superadmin\HasilUjianController::reviewPeserta()`

### Tabel yang Terlibat:
```
panritta_ujian_peserta (info peserta)
panritta_ujian_jawaban (jawaban per soal)
panritta_ujian_soal (info soal)
panritta_soal (konten soal, jawaban benar, pembahasan)
```

### UI Tampilkan:

```
REVIEW JAWABAN - Andi Pratama
CPNS 2024 Batch 1

Waktu Mulai: 16 Nov 2026, 10:00
Waktu Selesai: 16 Nov 2026, 11:45
Total Nilai: 350 / 500 (70%)
Status: ✓ LULUS

BREAKDOWN:
TWK: 200/250 (80%) ✓ Passing Grade 60%
TKP: 150/250 (60%) ✓ Passing Grade 55%

─────────────────────────────────────────────

Soal #1 (TWK - Pancasila):
Pertanyaan: Apa ibukota Indonesia?
Jawaban Peserta: A (Jakarta) ✓
Jawaban Benar: A (Jakarta) ✓
Nilai: 5 poin
Pembahasan: Jakarta adalah ibukota dan pusat pemerintahan negara...

─────────────────────────────────────────────

Soal #3 (TKP - Integritas):
Pertanyaan: Seberapa penting integritas dalam pekerjaan?
Jawaban Peserta: B (Penting) = 4 poin
Jawaban Benar: A (Sangat penting) = 5 poin [BEDA]
Nilai Peserta: 4 poin (dari max 5)
Pembahasan: Integritas adalah fondasi kepercayaan dalam organisasi...

─────────────────────────────────────────────

Soal #45 (TWK):
Pertanyaan: Pasal berapa UUD 1945 mengatur HAM?
Jawaban Peserta: [TIDAK DIJAWAB]
Jawaban Benar: B (Pasal 28)
Nilai: 0 poin
```

---

## 6.5 Lihat Ranking / Leaderboard

**User Action:** Buka `/ujian/{ujian}/leaderboard` atau Admin `/superadmin/hasil-ujian/{ujian}/final-score`  
**Controller:** `Peserta\UjianController::leaderboard()` atau `HasilUjianController::finalScore()`

### SQL Query:

```sql
SELECT 
    @rank := @rank + 1 as rank,
    p.id,
    COALESCE(u.name, po.nama_peserta) as nama,
    ujp.total_nilai,
    ujp.lulus,
    ujp.waktu_selesai
FROM panritta_ujian_peserta ujp
LEFT JOIN users u ON ujp.user_id = u.id
LEFT JOIN panritta_peserta_offline po ON ujp.peserta_offline_id = po.id
WHERE ujp.ujian_id = 1
  AND ujp.status = 'selesai'
  AND ujp.total_nilai IS NOT NULL
ORDER BY ujp.total_nilai DESC, ujp.waktu_selesai ASC
LIMIT 100;
```

### UI Tampilkan:

```
RANKING PESERTA - CPNS 2024 Batch 1

┌──────┬────────────────────┬───────────┬──────────┐
│ Rank │ Nama Peserta       │ Nilai     │ Status   │
├──────┼────────────────────┼───────────┼──────────┤
│  1   │ Doni Wijaya        │ 380 / 500 │ ✓ LULUS  │
│  2   │ Budi Santoso       │ 360 / 500 │ ✓ LULUS  │
│  3   │ Andi Pratama       │ 350 / 500 │ ✓ LULUS  │
│  4   │ Citra Dewi         │ 320 / 500 │ ✓ LULUS  │
│  5   │ Eka Wardani        │ 280 / 500 │ ✗ TIDAK  │
│  6   │ Fajar Imawan       │ 270 / 500 │ ✗ TIDAK  │
└──────┴────────────────────┴───────────┴──────────┘

Total Peserta: 50
Lulus: 40 (80%)
Tidak Lulus: 10 (20%)
```

---

# RINGKASAN TABEL & RELASI

## Tabel Utama

| Tabel | Primary Key | Purpose |
|-------|-------------|---------|
| `panritta_jenis_ujian` | id | Master jenis ujian (CPNS, ASN, dll) |
| `panritta_sub_jenis_ujian` | id | Sub jenis per jenis ujian (TWK, TIU, TKP) |
| `panritta_sub_indikator` | id | Kategori soal per sub jenis (Pancasila, UUD 1945, dll) |
| `panritta_soal` | id | Bank soal dengan konten, gambar, penilaian |
| `panritta_ujian` | id | Master ujian (nama, durasi, status, token offline) |
| `panritta_ujian_jenis_ujian` | (ujian_id, jenis_ujian_id) | Pivot: link jenis ujian ke ujian + passing grade |
| `panritta_ujian_soal` | id | Pivot: assembly soal ke ujian + urutan nomor |
| `panritta_ujian_peserta` | id | Attempt record: peserta + ujian + status + hasil |
| `panritta_ujian_jawaban` | id | Jawaban peserta per soal + nilai + benar/salah |
| `panritta_peserta_offline` | id | Peserta offline (nomor, nama, password, status) |
| `users` | id | User account (peserta, admin, pembuat soal) |

---

## ER Diagram (Simplified)

```
┌──────────────────┐
│ panritta_jenis_ujian
│ ├─ id
│ └─ nama
└──────────────────┘
         ▲
         │ (1:N)
         │
    ┌────────────────────┐
    │ panritta_sub_jenis_ujian
    │ ├─ id
    │ ├─ jenis_ujian_id (FK)
    │ ├─ nama_sub_jenis_ujian
    │ ├─ sistem_penilaian (benar_salah / tiap_jawaban_ada_poin)
    │ └─ nilai_benar
    └────────────────────┘
         ▲
         │ (1:N)
         │
    ┌──────────────────────────┐
    │ panritta_sub_indikator
    │ ├─ id
    │ ├─ sub_jenis_ujian_id (FK)
    │ └─ nama_sub_indikator
    └──────────────────────────┘
         ▲
         │ (1:N)
         │
    ┌──────────────────┐
    │ panritta_soal
    │ ├─ id
    │ ├─ sub_indikator_id (FK)
    │ ├─ soal (HTML, escaped)
    │ ├─ opsi_a-e
    │ ├─ kunci_jawaban / nilai_bobot_a-e
    │ ├─ gambar_soal (path)
    │ └─ pembahasan
    └──────────────────┘
         ▲
         │ (N:N via ujian_soal)
         │
    ┌──────────────────────────┐
    │ panritta_ujian_soal (Pivot)
    │ ├─ id
    │ ├─ ujian_id (FK)
    │ ├─ soal_id (FK)
    │ ├─ jenis_ujian_id (FK)
    │ └─ urutan (nomor soal)
    └──────────────────────────┘
         ▲
         │
         │ (1:N)
         │
    ┌──────────────────────────┐
    │ panritta_ujian
    │ ├─ id
    │ ├─ nama_ujian
    │ ├─ tipe_ujian (online / offline_kelas)
    │ ├─ status (draft / aktif / selesai)
    │ ├─ tanggal_ujian (offline)
    │ ├─ durasi_ujian
    │ ├─ token_ujian (offline)
    │ ├─ finalized_at
    │ └─ dibuat_oleh (FK users)
    └──────────────────────────┘
         ▲
         │ (N:N via ujian_jenis_ujian)
         │
    ┌──────────────────────────┐
    │ panritta_ujian_jenis_ujian (Pivot)
    │ ├─ ujian_id (FK)
    │ ├─ jenis_ujian_id (FK)
    │ └─ passing_grade
    └──────────────────────────┘

    ┌──────────────────────────┐
    │ panritta_ujian_peserta (Attempt)
    │ ├─ id
    │ ├─ ujian_id (FK)
    │ ├─ user_id (FK, NULL jika offline)
    │ ├─ peserta_offline_id (FK, NULL jika online)
    │ ├─ status (terdaftar / sedang_ujian / selesai)
    │ ├─ waktu_mulai
    │ ├─ waktu_selesai
    │ ├─ total_nilai
    │ ├─ lulus (boolean)
    │ ├─ is_paused
    │ └─ sisa_detik_saat_pause
    └──────────────────────────┘
         ▲
         │ (1:N)
         │
    ┌──────────────────────────┐
    │ panritta_ujian_jawaban
    │ ├─ id
    │ ├─ ujian_peserta_id (FK)
    │ ├─ ujian_soal_id (FK)
    │ ├─ soal_id (FK)
    │ ├─ jenis_ujian_id (FK)
    │ ├─ jawaban (A, B, C, D, E, atau NULL)
    │ ├─ nilai (poin didapat)
    │ └─ benar (boolean)
    └──────────────────────────┘

    ┌──────────────────────────┐
    │ panritta_peserta_offline
    │ ├─ id
    │ ├─ ujian_id (FK)
    │ ├─ nomor_peserta (001, 002, ...)
    │ ├─ nama_peserta
    │ ├─ password (bcrypt hash)
    │ ├─ is_active (boolean)
    │ └─ kode_akses (plaintext, sekali tampil)
    └──────────────────────────┘

    ┌──────────────────────────┐
    │ users
    │ ├─ id
    │ ├─ name
    │ ├─ email
    │ ├─ username
    │ ├─ password
    │ ├─ role_id (FK roles)
    │ └─ company_id (FK)
    └──────────────────────────┘
```

---

## Data Flow Summary

### Setup Phase (Admin)
```
1. Create Jenis Ujian (CPNS 2024)
2. Create Sub Jenis Ujian (TWK, TIU, TKP) → link to jenis
3. Create Sub Indikator (Pancasila, UUD 1945) → link to sub jenis
4. Create Soal (bank soal) → link to sub indikator
5. Create Ujian (master record)
6. Link Jenis Ujian to Ujian (pivot + passing grades)
7. Assemble Soal to Ujian (pivot + urutan)
8. Finalize Ujian (lock soal, set finalized_at)
9. Activate Ujian (status = aktif, generate token jika offline)
```

### Peserta Phase (Online)
```
1. Peserta register / login (users table)
2. Peserta buka daftar ujian (query: status = aktif)
3. Peserta klik "Mulai" → show konfirmasi
4. Peserta konfirmasi → create ujian_peserta (status = sedang_ujian)
5. Load soal dari ujian_soal (order by urutan)
6. Peserta pilih jawaban → AJAX saveAnswer → create ujian_jawaban + hitung poin
7. Heartbeat polling setiap 10 detik
8. Peserta klik Submit / timeout → finalize ujian
   - Update ujian_peserta: status = selesai, total_nilai, lulus
9. Peserta lihat hasil (breakdown per jenis ujian)
```

### Peserta Phase (Offline)
```
1. Admin buat peserta offline (nomor, nama, password)
   → kode_akses ditampilkan sekali saja
2. Peserta login ke portal offline (nomor + kode_akses)
3. Peserta lihat daftar ujian (khusus offline ujian)
4. Peserta mulai ujian (token validasi)
   → create ujian_peserta + peserta_offline_id
5. Peserta mengerjakan (same as online)
6. Submit → hasil tersimpan di ujian_peserta + ujian_jawaban
```

### Admin Monitor Phase
```
1. Admin buka hasil ujian dashboard
2. Admin lihat daftar ujian berlangsung
3. Admin klik ujian → lihat peserta sedang ujian
4. Admin bisa:
   - Monitor progress (jawaban count, sisa waktu)
   - Pause/Resume peserta
   - Force Stop peserta
   - Lihat analisis soal (statistik jawaban)
   - Review detail jawaban peserta
   - Lihat leaderboard ranking
```

---

## Contoh Data Lengkap (1 Skenario)

### Setup:
```
Jenis Ujian #1: "CPNS 2024"
├─ Sub Jenis #1: "TWK" (benar_salah)
│  ├─ Sub Indikator #1: "Pancasila"
│  │  ├─ Soal #101: "Ibukota Indonesia?" (5 opsi)
│  │  └─ Soal #102: "Pasal HAM di UUD?" (5 opsi)
│  └─ Sub Indikator #2: "UUD 1945"
│     └─ Soal #103: ...
├─ Sub Jenis #3: "TKP" (tiap_jawaban_ada_poin)
│  └─ Sub Indikator #10: "Integritas"
│     ├─ Soal #201: "Seberapa penting integritas?" (5 opsi = 1-5 poin)
│     └─ Soal #202: ...

Ujian #1: "CPNS 2024 Batch 1"
├─ tipe_ujian: online
├─ status: aktif
├─ Link Jenis #1 (passing_grade: 60)
├─ Assembly soal:
│  ├─ Soal #101 → urutan 1
│  ├─ Soal #102 → urutan 2
│  ├─ Soal #201 → urutan 3
│  └─ ... (100 soal total)
└─ finalized_at: 2026-11-15 15:00:00
```

### Peserta Login:
```
User #100: "andi.pratama@example.com"
├─ role: peserta
└─ Ujian attempt #1000:
   ├─ ujian_id: 1
   ├─ user_id: 100
   ├─ status: sedang_ujian
   ├─ waktu_mulai: 2026-11-16 10:00:00
   ├─ last_activity_at: 2026-11-16 10:05:30
   └─ [Jawaban]:
      ├─ Soal #101 (nomor 1): Jawab "A" → benar → 5 poin
      ├─ Soal #102 (nomor 2): Jawab "B" → benar → 5 poin
      ├─ Soal #201 (nomor 3): Jawab "B" → (TKP) 4 poin
      └─ ... (45 soal terjawab, 55 tidak)
```

### Submit & Result:
```
Ujian Peserta #1000:
├─ status: selesai
├─ waktu_selesai: 2026-11-16 11:45:30
├─ total_nilai: 350 / 500 (70%)
├─ lulus: TRUE
├─ Breakdown:
│  ├─ TWK: 200 / 250 (80%) ✓ passing_grade 60%
│  └─ TKP: 150 / 250 (60%) ✓ passing_grade 55%
└─ Ranking: #3 dari 50 peserta (top 3)
```

---

**END OF DOCUMENTATION**

*Semua tabel, field, relasi, dan flow sudah ter-dokumentasi lengkap dengan contoh data real.*
