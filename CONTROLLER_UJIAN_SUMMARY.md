# Dokumentasi Controller Ujian - GajiPro

**Terakhir Update:** 2026-11-16  
**Status:** Semua method controller Ujian sudah ter-dokumentasi dengan komentar inline

---

## Panduan Membaca Dokumentasi

Setiap method di controller sudah dilengkapi dengan comment block berisi:
1. **METHOD:** nama method
2. **URL Contoh:** URL lengkap dengan contoh parameter
3. **Keterangan:** penjelasan singkat fungsi & logika
4. **Form:** info form (jika ada), action URL, method HTTP
5. **Debug Mode:** commented-out `dd()` atau `\Log` untuk trace

Untuk mengaktifkan debug: **hapus komentarnya** di code.

---

## List Semua Controller & Method

### 1. **Superadmin\UjianController**
**File:** `app/Http/Controllers/Superadmin/UjianController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/ujian` | GET | Daftar ujian + filter (search, tipe, status) |
| `create()` | `/superadmin/ujian/create` | GET | Form tambah ujian kosong |
| `store()` | `/superadmin/ujian` | POST | Simpan ujian baru + sync jenis ujian |
| `edit()` | `/superadmin/ujian/{ujian}/edit` | GET | Form edit ujian terisi |
| `update()` | `/superadmin/ujian/{ujian}` | PUT | Update ujian |
| `destroy()` | `/superadmin/ujian/{ujian}` | DELETE | Hapus ujian + cascade soal/peserta |
| `activate()` | `/superadmin/ujian/{ujian}/activate` | PATCH | Aktifkan ujian (validate finalized + token offline) |
| `updateStatus()` | `/superadmin/ujian/{ujian}/status` | PATCH | Ubah status: draft/aktif/selesai |

---

### 2. **Superadmin\SoalController**
**File:** `app/Http/Controllers/Superadmin/SoalController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/soal` | GET | Daftar soal + filter berjenjang |
| `create()` | `/superadmin/soal/create` | GET | Form tambah soal kosong |
| `store()` | `/superadmin/soal` | POST | Simpan soal + upload gambar |
| `edit()` | `/superadmin/soal/{soal}/edit` | GET | Form edit soal terisi |
| `update()` | `/superadmin/soal/{soal}` | PUT | Update soal + hapus/ganti gambar |
| `destroy()` | `/superadmin/soal/{soal}` | DELETE | Hapus soal + file gambar |
| `preview()` | `/superadmin/soal/{soal}/preview` | GET | Preview simulasi tampilan soal |
| `uploadGambarEditor()` | `/superadmin/soal/upload-gambar-editor` | POST | API: upload gambar inline dari editor (JSON) |
| `downloadTemplate()` | `/superadmin/soal/download-template` | GET | Download template Excel import soal (dynamic columns) |
| `subJenisUjianOptions()` | `/superadmin/soal/options/sub-jenis-ujian/{jenisUjian}` | GET | API: dependent dropdown sub jenis (JSON) |
| `subIndikatorOptions()` | `/superadmin/soal/options/sub-indikator/{subJenisUjian}` | GET | API: dependent dropdown sub indikator (JSON) |

---

### 3. **Superadmin\UjianSoalController**
**File:** `app/Http/Controllers/Superadmin/UjianSoalController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/ujian/{ujian}/soal` | GET | Kelola soal ujian: tab per jenis ujian + panel aksi |
| `importExcel()` | `/superadmin/ujian/{ujian}/soal/import-excel` | POST | Bulk import soal dari Excel |
| `bankSoalOptions()` | `/superadmin/ujian/{ujian}/soal/bank-options` | GET | API: search soal dari bank untuk attach (JSON) |
| `remaining()` | `/superadmin/ujian/{ujian}/soal/remaining` | GET | API: cek kapasitas ujian tersisa (JSON) |
| `attach()` | `/superadmin/ujian/{ujian}/soal/attach` | POST | Tambah soal existing ke ujian |
| `finalisasi()` | `/superadmin/ujian/{ujian}/soal/finalisasi` | GET | Halaman finalisasi: tampil+reorder soal |
| `finalize()` | `/superadmin/ujian/{ujian}/soal/finalize` | PATCH | Lock susunan soal (finalized_at = now) |
| `unfinalize()` | `/superadmin/ujian/{ujian}/soal/unfinalize` | PATCH | Batalkan finalisasi (unlock soal) |
| `reorder()` | `/superadmin/ujian/{ujian}/soal/reorder` | PATCH | AJAX: ubah urutan soal drag-drop (JSON) |
| `detach()` | `/superadmin/ujian/{ujian}/soal/{ujianSoal}` | DELETE | Hapus soal dari ujian (not delete soal) |

---

### 4. **Superadmin\JenisUjianController**
**File:** `app/Http/Controllers/Superadmin/JenisUjianController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/jenis-ujian` | GET | Daftar jenis ujian (CRUD paling sederhana) |
| `store()` | `/superadmin/jenis-ujian` | POST | Tambah jenis ujian modal |
| `update()` | `/superadmin/jenis-ujian/{jenisUjian}` | PUT | Edit jenis ujian |
| `destroy()` | `/superadmin/jenis-ujian/{jenisUjian}` | DELETE | Hapus jenis ujian |

---

### 5. **Superadmin\SubJenisUjianController**
**File:** `app/Http/Controllers/Superadmin/SubJenisUjianController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/sub-jenis-ujian` | GET | Daftar sub jenis ujian (grouped per jenis, eager load) |
| `store()` | `/superadmin/sub-jenis-ujian` | POST | Tambah sub jenis ujian modal |
| `update()` | `/superadmin/sub-jenis-ujian/{subJenisUjian}` | PUT | Edit sub jenis ujian |
| `destroy()` | `/superadmin/sub-jenis-ujian/{subJenisUjian}` | DELETE | Hapus sub jenis ujian |

---

### 6. **Superadmin\SubIndikatorController**
**File:** `app/Http/Controllers/Superadmin/SubIndikatorController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/sub-indikator` | GET | Daftar sub indikator (grouped 2-tier, eager load) |
| `store()` | `/superadmin/sub-indikator` | POST | Tambah sub indikator modal |
| `update()` | `/superadmin/sub-indikator/{subIndikator}` | PUT | Edit sub indikator |
| `destroy()` | `/superadmin/sub-indikator/{subIndikator}` | DELETE | Hapus sub indikator |

---

### 7. **Superadmin\HasilUjianController**
**File:** `app/Http/Controllers/Superadmin/HasilUjianController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/hasil-ujian` | GET | Daftar ujian berlangsung (filtering window waktu) |
| `show()` | `/superadmin/hasil-ujian/{ujian}` | GET | Dashboard ringkasan hasil ujian |
| `liveScore()` | `/superadmin/hasil-ujian/{ujian}/live-score` | GET | Ranking realtime sedang ujian |
| `finalScore()` | `/superadmin/hasil-ujian/{ujian}/final-score` | GET | Ranking final selesai ujian |
| `soalAnalysis()` | `/superadmin/hasil-ujian/{ujian}/soal-analysis` | GET | Statistik jawaban per soal (distribusi) |
| `pesertaList()` | `/superadmin/hasil-ujian/{ujian}/peserta` | GET | Daftar peserta offline + status |
| `reviewPeserta()` | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/review` | GET | Detail jawaban satu peserta per soal |
| `management()` | `/superadmin/hasil-ujian/{ujian}/management` | GET | Monitor peserta sedang ujian (realtime) |
| `getManagementData()` | `/superadmin/hasil-ujian/{ujian}/management-data` | GET | API: data peserta sedang ujian (AJAX poll, JSON) |
| `forceFinalize()` | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-finalize` | POST | Finalisasi ujian peserta paksa |
| `togglePause()` | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/toggle-pause` | PATCH | Pause/resume ujian peserta |
| `forceStop()` | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-stop` | DELETE | Hentikan ujian peserta permanent |

---

### 8. **Superadmin\UjianPesertaController**
**File:** `app/Http/Controllers/Superadmin/UjianPesertaController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/ujian/{ujian}/peserta` | GET | Daftar peserta online terdaftar |
| `available()` | `/superadmin/ujian/{ujian}/peserta/available` | GET | Daftar peserta belum terdaftar (untuk attach) |
| `store()` | `/superadmin/ujian/{ujian}/peserta` | POST | Bulk attach multiple peserta ke ujian |
| `toggleBlokir()` | `/superadmin/ujian/{ujian}/peserta/{peserta}/toggle-blokir` | PATCH | Toggle blok/unlock peserta |
| `destroy()` | `/superadmin/ujian/{ujian}/peserta/{peserta}` | DELETE | Hapus peserta dari ujian |
| `exportPdf()` | `/superadmin/ujian/{ujian}/peserta/export/pdf` | GET | Download daftar peserta + akun PDF |
| `exportExcel()` | `/superadmin/ujian/{ujian}/peserta/export/excel` | GET | Download daftar peserta + akun Excel |

---

### 9. **Superadmin\PesertaOfflineController**
**File:** `app/Http/Controllers/Superadmin/PesertaOfflineController.php`

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `index()` | `/superadmin/ujian/{ujian}/peserta-offline` | GET | Daftar peserta offline terdaftar |
| `store()` | `/superadmin/ujian/{ujian}/peserta-offline` | POST | Tambah peserta offline satu per satu |
| `destroy()` | `/superadmin/ujian/{ujian}/peserta-offline/{pesertaOffline}` | DELETE | Hapus satu peserta offline |
| `bulkDestroy()` | `/superadmin/ujian/{ujian}/peserta-offline/bulk-destroy` | DELETE | Hapus multiple peserta offline |
| `template()` | `/superadmin/peserta-offline/template` | GET | Download template Excel import peserta |
| `importForm()` | `/superadmin/ujian/{ujian}/peserta-offline/import-form` | GET | Tampilkan form import peserta offline |
| `import()` | `/superadmin/ujian/{ujian}/peserta-offline/import` | POST | Bulk import peserta dari Excel |
| `showPasswords()` | `/superadmin/ujian/{ujian}/peserta-offline/passwords` | GET | Tampilkan daftar password (sensitive!) |
| `export()` | `/superadmin/ujian/{ujian}/peserta-offline/export` | GET | Download kartu peserta PDF |

---

### 10. **Peserta\UjianController**
**File:** `app/Http/Controllers/Peserta/UjianController.php`

(Parsial - controller peserta, interface ujian dari sisi penjawab)

| Method | URL | HTTP | Fungsi |
|--------|-----|------|--------|
| `show()` | `/ujian/{ujian}` | GET | Halaman konfirmasi sebelum mulai ujian |
| `start()` | `/ujian/{ujian}/start` | POST | Validasi & start ujian (token offline, blokir check) |
| `kerjakan()` | `/ujian/{ujian}/kerjakan` | GET | Halaman ujian: tampil soal, timer, navigasi |
| `saveAnswer()` | `/ujian/{ujian}/jawaban` | POST | API: simpan jawaban (JSON) |
| `heartbeat()` | `/ujian/{ujian}/heartbeat` | GET | API: polling deadline & update activity (JSON) |
| `submit()` | `/ujian/{ujian}/submit` | POST | Submit ujian (finalize, hitung skor) |
| `hasil()` | `/ujian/{ujian}/hasil` | GET | Lihat hasil ujian (score, breakdown, lulus/tidak) |
| `pembahasan()` | `/ujian/{ujian}/pembahasan` | GET | Lihat pembahasan soal-soal |
| `leaderboard()` | `/ujian/{ujian}/leaderboard` | GET | Lihat ranking peserta |

---

## Form Action Summary

### Create/Store Forms
```
Form Soal Create/Edit:
  POST /superadmin/soal              (store)
  PUT  /superadmin/soal/{id}         (update)

Form Ujian Create/Edit:
  POST /superadmin/ujian             (store)
  PUT  /superadmin/ujian/{id}        (update)

Form JenisUjian Modal:
  POST /superadmin/jenis-ujian       (store, modal)
  PUT  /superadmin/jenis-ujian/{id}  (update, modal)

Form SubJenisUjian Modal:
  POST /superadmin/sub-jenis-ujian       (store, modal)
  PUT  /superadmin/sub-jenis-ujian/{id}  (update, modal)

Form SubIndikator Modal:
  POST /superadmin/sub-indikator       (store, modal)
  PUT  /superadmin/sub-indikator/{id}  (update, modal)

Form Peserta Offline Manual Add:
  POST /superadmin/ujian/{ujian}/peserta-offline  (store, modal)

Form Peserta Online Bulk Attach:
  POST /superadmin/ujian/{ujian}/peserta         (store, modal)
```

### Import/Export Forms
```
Import Soal Excel:
  POST /superadmin/ujian/{ujian}/soal/import-excel

Import Peserta Offline Excel:
  POST /superadmin/ujian/{ujian}/peserta-offline/import

Export Peserta PDF:
  GET /superadmin/ujian/{ujian}/peserta/export/pdf

Export Peserta Excel:
  GET /superadmin/ujian/{ujian}/peserta/export/excel

Export Peserta Offline Kartu PDF:
  GET /superadmin/ujian/{ujian}/peserta-offline/export

Export Peserta Offline Daftar Password:
  GET /superadmin/ujian/{ujian}/peserta-offline/passwords
```

---

## Debug Mode - Aktivasi

Di setiap method sudah ada commented debug code. Untuk aktivasi:

**Contoh:**
```php
// Debug Mode (uncomment untuk trace):
// dd($ujians->map(...)->toArray());
// \Log::info('Store ujian', ['nama' => $request->nama_ujian]);
```

**Untuk aktifkan:**
1. Buka file controller
2. Cari `// Debug Mode` section
3. Hapus `//` dari line yang ingin di-trace
4. Akses halaman yang trigger method
5. Lihat output di terminal atau storage/logs

---

## Catatan Penting

1. **Route Model Binding**: Parameter `{ujian}`, `{soal}`, dll otomatis resolve by ID
2. **Validasi Form**: Semua input divalidasi via custom Request classes (SoalRequest, UjianRequest, dll)
3. **Authorization**: Middleware protect routes (cek role admin/supervisor)
4. **Transaction**: Store/update ujian wrap dalam `DB::transaction()` untuk atomic
5. **Eager Loading**: Query selalu gunakan `->with()` untuk avoid N+1 problem
6. **Cascade Delete**: Foreign keys punya `on_delete=cascade`, delete otomatis anak relations
7. **Pagination**: Index views paginasi 15-20 per halaman, gunakan `withQueryString()` keep filter

---

## File Controller Reference

| Nama Controller | Path | Primary Purpose |
|-----------------|------|-----------------|
| UjianController | `Superadmin/UjianController.php` | Main CRUD ujian |
| SoalController | `Superadmin/SoalController.php` | Bank soal management + upload |
| UjianSoalController | `Superadmin/UjianSoalController.php` | Assemble soal ke ujian |
| JenisUjianController | `Superadmin/JenisUjianController.php` | CRUD jenis ujian |
| SubJenisUjianController | `Superadmin/SubJenisUjianController.php` | CRUD sub jenis ujian |
| SubIndikatorController | `Superadmin/SubIndikatorController.php` | CRUD sub indikator |
| HasilUjianController | `Superadmin/HasilUjianController.php` | Results + monitoring + management |
| UjianPesertaController | `Superadmin/UjianPesertaController.php` | Peserta online management |
| PesertaOfflineController | `Superadmin/PesertaOfflineController.php` | Peserta offline management |
| Peserta\UjianController | `Peserta/UjianController.php` | Student exam interface |

---

**Dokumentasi dibuat:** 2026-11-16  
**Oleh:** OpenCode CLI  
**Bahasa:** Bahasa Indonesia  
**Versi Aplikasi:** GajiPro v1.0
