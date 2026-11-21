# Mapping URL ke Controller Method

**Panduan:** Ketika dapat error error atau URL tertentu, cari di dokumentasi ini untuk tahu file controller mana yang perlu diperbaiki.

**Format:**
```
URL Contoh: GET /superadmin/ujian
Route Name: superadmin.ujian.index
Controller: Superadmin/UjianController.php
Method: index()
File: app/Http/Controllers/Superadmin/UjianController.php:24
```

---

# SUPERADMIN - UJIAN MANAGEMENT

## 1. JENIS UJIAN ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/jenis-ujian` | GET | `superadmin.jenis-ujian.index` | JenisUjianController | `index()` | line 43 |
| 2 | `/superadmin/jenis-ujian` | POST | `superadmin.jenis-ujian.store` | JenisUjianController | `store()` | line 66 |
| 3 | `/superadmin/jenis-ujian/{jenisUjian}` | PUT | `superadmin.jenis-ujian.update` | JenisUjianController | `update()` | line 85 |
| 4 | `/superadmin/jenis-ujian/{jenisUjian}` | DELETE | `superadmin.jenis-ujian.destroy` | JenisUjianController | `destroy()` | line 100 |

---

## 2. SUB JENIS UJIAN ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/sub-jenis-ujian` | GET | `superadmin.sub-jenis-ujian.index` | SubJenisUjianController | `index()` | line 40 |
| 2 | `/superadmin/sub-jenis-ujian` | POST | `superadmin.sub-jenis-ujian.store` | SubJenisUjianController | `store()` | line 56 |
| 3 | `/superadmin/sub-jenis-ujian/{subJenisUjian}` | PUT | `superadmin.sub-jenis-ujian.update` | SubJenisUjianController | `update()` | line 68 |
| 4 | `/superadmin/sub-jenis-ujian/{subJenisUjian}` | DELETE | `superadmin.sub-jenis-ujian.destroy` | SubJenisUjianController | `destroy()` | line 79 |

---

## 3. SUB INDIKATOR ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/sub-indikator` | GET | `superadmin.sub-indikator.index` | SubIndikatorController | `index()` | line 46 |
| 2 | `/superadmin/sub-indikator` | POST | `superadmin.sub-indikator.store` | SubIndikatorController | `store()` | line 68 |
| 3 | `/superadmin/sub-indikator/{subIndikator}` | PUT | `superadmin.sub-indikator.update` | SubIndikatorController | `update()` | line 79 |
| 4 | `/superadmin/sub-indikator/{subIndikator}` | DELETE | `superadmin.sub-indikator.destroy` | SubIndikatorController | `destroy()` | line 90 |

---

## 4. SOAL (BANK SOAL) ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/soal` | GET | `superadmin.soal.index` | SoalController | `index()` | line 57 |
| 2 | `/superadmin/soal/create` | GET | `superadmin.soal.create` | SoalController | `create()` | line 91 |
| 3 | `/superadmin/soal` | POST | `superadmin.soal.store` | SoalController | `store()` | line 127 |
| 4 | `/superadmin/soal/{soal}/edit` | GET | `superadmin.soal.edit` | SoalController | `edit()` | line 170 |
| 5 | `/superadmin/soal/{soal}` | PUT | `superadmin.soal.update` | SoalController | `update()` | line 190 |
| 6 | `/superadmin/soal/{soal}` | DELETE | `superadmin.soal.destroy` | SoalController | `destroy()` | line 242 |
| 7 | `/superadmin/soal/{soal}/preview` | GET | `superadmin.soal.preview` | SoalController | `preview()` | line 260 |
| 8 | `/superadmin/soal/upload-gambar-editor` | POST | `superadmin.soal.upload-editor` | SoalController | `uploadGambarEditor()` | line 295 |
| 9 | `/superadmin/soal/template-import/{subJenisUjian?}/{ujian?}/{subIndikator?}` | GET | `superadmin.soal.template` | SoalController | `downloadTemplate()` | line 323 |
| 10 | `/superadmin/soal/options/sub-jenis-ujian/{jenisUjian}` | GET | `superadmin.soal.options.sub-jenis-ujian` | SoalController | `subJenisUjianOptions()` | line 426 |
| 11 | `/superadmin/soal/options/sub-indikator/{subJenisUjian}` | GET | `superadmin.soal.options.sub-indikator` | SoalController | `subIndikatorOptions()` | line 439 |

---

## 5. UJIAN (MASTER UJIAN) ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/ujian` | GET | `superadmin.ujian.index` | UjianController | `index()` | line 24 |
| 2 | `/superadmin/ujian/create` | GET | `superadmin.ujian.create` | UjianController | `create()` | line 45 |
| 3 | `/superadmin/ujian` | POST | `superadmin.ujian.store` | UjianController | `store()` | line 57 |
| 4 | `/superadmin/ujian/{ujian}/edit` | GET | `superadmin.ujian.edit` | UjianController | `edit()` | line 70 |
| 5 | `/superadmin/ujian/{ujian}` | PUT | `superadmin.ujian.update` | UjianController | `update()` | line 91 |
| 6 | `/superadmin/ujian/{ujian}` | DELETE | `superadmin.ujian.destroy` | UjianController | `destroy()` | line 102 |
| 7 | `/superadmin/ujian/{ujian}/activate` | POST | `superadmin.ujian.activate` | UjianController | `activate()` | line 110 |
| 8 | `/superadmin/ujian/{ujian}/update-status` | POST | `superadmin.ujian.update-status` | UjianController | `updateStatus()` | line 123 |

---

## 6. UJIAN SOAL (KELOLA SOAL PER UJIAN) ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/ujian/{ujian}/soal` | GET | `superadmin.ujian.soal.index` | UjianSoalController | `index()` | line 33 |
| 2 | `/superadmin/ujian/{ujian}/soal/attach` | POST | `superadmin.ujian.soal.attach` | UjianSoalController | `attach()` | line 175 |
| 3 | `/superadmin/ujian/{ujian}/soal/reorder` | POST | `superadmin.ujian.soal.reorder` | UjianSoalController | `reorder()` | line 262 |
| 4 | `/superadmin/ujian/{ujian}/soal/import` | POST | `superadmin.ujian.soal.import` | UjianSoalController | `importExcel()` | line 94 |
| 5 | `/superadmin/ujian/{ujian}/soal/{ujianSoal}/detach` | DELETE | `superadmin.ujian.soal.detach` | UjianSoalController | `detach()` | line 285 |
| 6 | `/superadmin/ujian/{ujian}/finalisasi` | GET | `superadmin.ujian.soal.finalisasi` | UjianSoalController | `finalisasi()` | line 198 |
| 7 | `/superadmin/ujian/{ujian}/finalisasi` | POST | `superadmin.ujian.soal.finalize` | UjianSoalController | `finalize()` | line 234 |
| 8 | `/superadmin/ujian/{ujian}/finalisasi` | DELETE | `superadmin.ujian.soal.unfinalize` | UjianSoalController | `unfinalize()` | line 246 |
| 9 | `/superadmin/ujian/{ujian}/remaining` | GET | `superadmin.ujian.soal.remaining` | UjianSoalController | `remaining()` | line 167 |
| 10 | `/superadmin/ujian/{ujian}/bank-soal-options` | GET | `superadmin.ujian.soal.bank-options` | UjianSoalController | `bankSoalOptions()` | line 141 |

---

## 7. HASIL UJIAN (HASIL & MONITORING) ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/hasil-ujian` | GET | `superadmin.hasil-ujian.index` | HasilUjianController | `index()` | line 29 |
| 2 | `/superadmin/hasil-ujian/{ujian}` | GET | `superadmin.hasil-ujian.show` | HasilUjianController | `show()` | line 58 |
| 3 | `/superadmin/hasil-ujian/{ujian}/live-score` | GET | `superadmin.hasil-ujian.live-score` | HasilUjianController | `liveScore()` | line 82 |
| 4 | `/superadmin/hasil-ujian/{ujian}/final-score` | GET | `superadmin.hasil-ujian.final-score` | HasilUjianController | `finalScore()` | line 90 |
| 5 | `/superadmin/hasil-ujian/{ujian}/soal-analysis` | GET | `superadmin.hasil-ujian.soal-analysis` | HasilUjianController | `soalAnalysis()` | line 100 |
| 6 | `/superadmin/hasil-ujian/{ujian}/peserta` | GET | `superadmin.hasil-ujian.peserta-list` | HasilUjianController | `pesertaList()` | line 143 |
| 7 | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/review` | GET | `superadmin.hasil-ujian.review-peserta` | HasilUjianController | `reviewPeserta()` | line 193 |
| 8 | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-finalize` | POST | `superadmin.hasil-ujian.force-finalize` | HasilUjianController | `forceFinalize()` | line 220 |
| 9 | `/superadmin/hasil-ujian/{ujian}/management` | GET | `superadmin.hasil-ujian.management` | HasilUjianController | `management()` | line 238 |
| 10 | `/superadmin/hasil-ujian/{ujian}/management/data` | GET | `superadmin.hasil-ujian.management.data` | HasilUjianController | `getManagementData()` | line 248 |
| 11 | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/toggle-pause` | POST | `superadmin.hasil-ujian.toggle-pause` | HasilUjianController | `togglePause()` | line 323 |
| 12 | `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-stop` | POST | `superadmin.hasil-ujian.force-stop` | HasilUjianController | `forceStop()` | line 357 |

---

## 8. UJIAN PESERTA (PESERTA ONLINE) ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/ujian/{ujian}/peserta` | GET | `superadmin.ujian.peserta.index` | UjianPesertaController | `index()` | line 19 |
| 2 | `/superadmin/ujian/{ujian}/peserta/available` | GET | `superadmin.ujian.peserta.available` | UjianPesertaController | `available()` | line 37 |
| 3 | `/superadmin/ujian/{ujian}/peserta` | POST | `superadmin.ujian.peserta.store` | UjianPesertaController | `store()` | line 58 |
| 4 | `/superadmin/ujian/{ujian}/peserta/{peserta}/blokir` | PATCH | `superadmin.ujian.peserta.blokir` | UjianPesertaController | `toggleBlokir()` | line 89 |
| 5 | `/superadmin/ujian/{ujian}/peserta/{peserta}` | DELETE | `superadmin.ujian.peserta.destroy` | UjianPesertaController | `destroy()` | line 103 |
| 6 | `/superadmin/ujian/{ujian}/peserta/export/pdf` | GET | `superadmin.ujian.peserta.export.pdf` | UjianPesertaController | `exportPdf()` | line 112 |
| 7 | `/superadmin/ujian/{ujian}/peserta/export/excel` | GET | `superadmin.ujian.peserta.export.excel` | UjianPesertaController | `exportExcel()` | line 121 |

---

## 9. PESERTA OFFLINE ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/superadmin/ujian/{ujian}/peserta-offline` | GET | `superadmin.ujian.peserta-offline.index` | PesertaOfflineController | `index()` | line 27 |
| 2 | `/superadmin/ujian/{ujian}/peserta-offline` | POST | `superadmin.ujian.peserta-offline.store` | PesertaOfflineController | `store()` | line 34 |
| 3 | `/superadmin/ujian/{ujian}/peserta-offline/{pesertaOffline}` | DELETE | `superadmin.ujian.peserta-offline.destroy` | PesertaOfflineController | `destroy()` | line 44 |
| 4 | `/superadmin/ujian/{ujian}/peserta-offline/bulk-destroy` | DELETE | `superadmin.ujian.peserta-offline.bulk-destroy` | PesertaOfflineController | `bulkDestroy()` | line 53 |
| 5 | `/superadmin/peserta-offline/template` | GET | `superadmin.ujian.peserta-offline.template` | PesertaOfflineController | `template()` | line 69 |
| 6 | `/superadmin/ujian/{ujian}/peserta-offline/import` | GET | `superadmin.ujian.peserta-offline.import.form` | PesertaOfflineController | `importForm()` | line 74 |
| 7 | `/superadmin/ujian/{ujian}/peserta-offline/import` | POST | `superadmin.ujian.peserta-offline.import` | PesertaOfflineController | `import()` | line 81 |
| 8 | `/superadmin/ujian/{ujian}/peserta-offline/passwords` | GET | `superadmin.ujian.peserta-offline.passwords` | PesertaOfflineController | `showPasswords()` | line 107 |
| 9 | `/superadmin/ujian/{ujian}/peserta-offline/export` | GET | `superadmin.ujian.peserta-offline.export` | PesertaOfflineController | `export()` | line 114 |

---

# PESERTA ROUTES (STUDENT EXAM INTERFACE)

## PESERTA UJIAN ROUTES

| No | URL | HTTP | Route Name | Controller | Method | File Location |
|----|-----|------|-----------|-----------|--------|---------------|
| 1 | `/ujian/{ujian}` | GET | `peserta.ujian.show` | Peserta\UjianController | `show()` | line 24 |
| 2 | `/ujian/{ujian}/start` | POST | `peserta.ujian.start` | Peserta\UjianController | `start()` | line 39 |
| 3 | `/ujian/{ujian}/kerjakan` | GET | `peserta.ujian.kerjakan` | Peserta\UjianController | `kerjakan()` | line 86 |
| 4 | `/ujian/{ujian}/jawaban` | POST | `peserta.ujian.jawaban` | Peserta\UjianController | `saveAnswer()` | line 126 |
| 5 | `/ujian/{ujian}/heartbeat` | GET | `peserta.ujian.heartbeat` | Peserta\UjianController | `heartbeat()` | line 182 |
| 6 | `/ujian/{ujian}/submit` | POST | `peserta.ujian.submit` | Peserta\UjianController | `submit()` | - |
| 7 | `/ujian/{ujian}/hasil` | GET | `peserta.ujian.hasil` | Peserta\UjianController | `hasil()` | - |
| 8 | `/ujian/{ujian}/pembahasan` | GET | `peserta.ujian.pembahasan` | Peserta\UjianController | `pembahasan()` | - |
| 9 | `/ujian/{ujian}/leaderboard` | GET | `peserta.ujian.leaderboard` | Peserta\UjianController | `leaderboard()` | - |

---

# QUICK REFERENCE - ERROR TRACING

## Jika Error Muncul di URL

**Contoh 1: Error di `/superadmin/ujian`**
```
URL: GET /superadmin/ujian
Route Name: superadmin.ujian.index
File: app/Http/Controllers/Superadmin/UjianController.php
Method: index() (line 24)
Buka file: UjianController.php, lihat method index() mulai line 24
```

**Contoh 2: Error di `/superadmin/ujian/5/soal`**
```
URL: GET /superadmin/ujian/5/soal
Route Name: superadmin.ujian.soal.index
File: app/Http/Controllers/Superadmin/UjianSoalController.php
Method: index() (line 33)
Buka file: UjianSoalController.php, lihat method index() mulai line 33
```

**Contoh 3: Error di `/ujian/5/jawaban` (POST)**
```
URL: POST /ujian/5/jawaban
Route Name: peserta.ujian.jawaban
File: app/Http/Controllers/Peserta/UjianController.php
Method: saveAnswer() (line 126)
Buka file: Peserta/UjianController.php, lihat method saveAnswer() mulai line 126
```

---

# SEARCH SHORTCUTS

## Cari Route Berdasarkan URL Pattern

### `/superadmin/ujian*` → UjianController
- `/superadmin/ujian` → `index()`, `store()`
- `/superadmin/ujian/create` → `create()`
- `/superadmin/ujian/{ujian}` → `edit()`, `update()`, `destroy()`
- `/superadmin/ujian/{ujian}/activate` → `activate()`
- `/superadmin/ujian/{ujian}/update-status` → `updateStatus()`

### `/superadmin/ujian/{ujian}/soal*` → UjianSoalController
- `/superadmin/ujian/{ujian}/soal` → `index()`
- `/superadmin/ujian/{ujian}/soal/attach` → `attach()`
- `/superadmin/ujian/{ujian}/soal/import` → `importExcel()`
- `/superadmin/ujian/{ujian}/soal/reorder` → `reorder()`
- `/superadmin/ujian/{ujian}/soal/{ujianSoal}/detach` → `detach()`
- `/superadmin/ujian/{ujian}/finalisasi` → `finalisasi()`, `finalize()`, `unfinalize()`

### `/superadmin/soal*` → SoalController
- `/superadmin/soal` → `index()`, `store()`
- `/superadmin/soal/create` → `create()`
- `/superadmin/soal/{soal}` → `edit()`, `update()`, `destroy()`
- `/superadmin/soal/{soal}/preview` → `preview()`
- `/superadmin/soal/upload-gambar-editor` → `uploadGambarEditor()`
- `/superadmin/soal/template-import` → `downloadTemplate()`
- `/superadmin/soal/options/*` → `subJenisUjianOptions()`, `subIndikatorOptions()`

### `/superadmin/hasil-ujian*` → HasilUjianController
- `/superadmin/hasil-ujian` → `index()`
- `/superadmin/hasil-ujian/{ujian}` → `show()`
- `/superadmin/hasil-ujian/{ujian}/live-score` → `liveScore()`
- `/superadmin/hasil-ujian/{ujian}/final-score` → `finalScore()`
- `/superadmin/hasil-ujian/{ujian}/soal-analysis` → `soalAnalysis()`
- `/superadmin/hasil-ujian/{ujian}/peserta` → `pesertaList()`
- `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/review` → `reviewPeserta()`
- `/superadmin/hasil-ujian/{ujian}/management` → `management()`
- `/superadmin/hasil-ujian/{ujian}/management/data` → `getManagementData()`
- `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/toggle-pause` → `togglePause()`
- `/superadmin/hasil-ujian/{ujian}/peserta/{peserta}/force-stop` → `forceStop()`

### `/ujian*` → Peserta\UjianController
- `/ujian/{ujian}` → `show()`
- `/ujian/{ujian}/start` → `start()`
- `/ujian/{ujian}/kerjakan` → `kerjakan()`
- `/ujian/{ujian}/jawaban` → `saveAnswer()`
- `/ujian/{ujian}/heartbeat` → `heartbeat()`
- `/ujian/{ujian}/hasil` → `hasil()`
- `/ujian/{ujian}/pembahasan` → `pembahasan()`
- `/ujian/{ujian}/leaderboard` → `leaderboard()`

---

# FILE STRUCTURE REFERENCE

```
app/Http/Controllers/
├── Superadmin/
│   ├── UjianController.php
│   ├── SoalController.php
│   ├── UjianSoalController.php
│   ├── JenisUjianController.php
│   ├── SubJenisUjianController.php
│   ├── SubIndikatorController.php
│   ├── HasilUjianController.php
│   ├── UjianPesertaController.php
│   └── PesertaOfflineController.php
├── Peserta/
│   └── UjianController.php
└── ...
```

---

**Dokumentasi dibuat:** 2026-11-16  
**Total Routes Mapped:** 50+ ujian-related routes  
**Untuk debugging:** Gunakan "Quick Reference - Error Tracing" di atas
