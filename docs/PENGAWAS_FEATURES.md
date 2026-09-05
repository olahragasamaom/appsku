# Dokumentasi Fitur Pengawas Ujian Offline

## Ringkasan

Dokumen ini merangkum semua fitur superadmin/pengawas untuk mengelola dan memonitor ujian offline. Semua fitur ini terintegrasi di halaman **Monitoring Pengawas**.

**Akses:** Superadmin → Ujian → Peserta Offline → **Monitoring** (atau via Kelola Kehadiran → **Monitoring Pengawas**)

**URL:** `/superadmin/ujian/{ujian_id}/pengawas`

---

## Daftar Fitur

### 1. Real-time Monitoring Dashboard

**Deskripsi:** Dashboard live yang menampilkan status semua peserta ujian offline secara real-time.

**Fitur:**
- Auto-refresh setiap **5 detik** via AJAX polling
- 5 statistik utama:
  - **Total Peserta** — Semua peserta terdaftar
  - **Ditandai Hadir** — Peserta yang sudah dimark hadir
  - **Login** — Peserta yang aktif login sekarang
  - **Sedang Ujian** — Peserta yang sedang mengerjakan
  - **Selesai** — Peserta yang sudah submit
- Timestamp update terakhir
- Tombol "Refresh Sekarang" untuk manual refresh

**Status Indicator per Peserta:**
| Icon | Warna | Arti |
|------|-------|------|
| 🟢 | Hijau | Online (aktivitas <30 detik) |
| 🟡 | Kuning | Idle (aktivitas >30 detik) |
| ⚫ | Abu-abu | Offline (belum login) |

**Info yang Ditampilkan:**
- Nomor Peserta
- Nama Peserta
- Status Kehadiran (Hadir/Belum Hadir/Diblokir)
- Status Ujian (Belum Login / Login / Sedang Ujian)
- **IP Address** — untuk deteksi lokasi/perangkat
- **Last Activity** — kapan terakhir aktif

---

### 2. Anti Multi-Device Login

**Deskripsi:** Mencegah peserta login dari lebih dari 1 perangkat secara bersamaan.

**Mekanisme:**
1. Setiap login generate **session_token** unik (64 karakter random)
2. Saat peserta login dari device baru:
   - Session lama di database **otomatis di-force logout**
   - `logout_reason` tercatat sebagai "Login dari perangkat lain"
3. Middleware **validasi session_token** di setiap request
4. Jika token tidak valid → peserta di-kick dengan pesan:
   > "Sesi Anda telah berakhir karena login dari perangkat lain. Silakan login kembali."

**Data yang Tercatat:**
- Session token unik
- Device info: IP address, User-Agent
- Login time, last activity time
- Logout time & reason (jika ada)

---

### 3. Force Logout

**Deskripsi:** Kick session peserta secara paksa (tapi masih bisa login ulang).

**Kapan Digunakan:**
- Peserta suspicious tapi belum yakin
- Peserta lupa logout dan admin butuh reset sesinya
- Kondisi mendesak yang tidak permanent

**Cara Pakai:**
1. Klik tombol **"Logout"** (warna kuning) di baris peserta
2. Confirm dialog "Yakin ingin force logout peserta ini?"
3. Session peserta langsung terminated
4. Peserta bisa login lagi setelahnya

**Perbedaan dengan Blokir:**
- Force Logout = **sementara**, peserta masih bisa login
- Blokir = **permanent**, harus di-unblock dulu

---

### 4. Blokir Peserta (Permanent Block) ⛔

**Deskripsi:** Blokir peserta secara permanent - tidak bisa login lagi sampai di-unblock.

**Kapan Digunakan:**
- Cheating serius (nyontek, buka HP, dll)
- Pelanggaran berat lain
- Perlu tindakan tegas

**Cara Pakai:**
1. Klik tombol **"Blokir"** (warna merah) di baris peserta
2. Modal muncul dengan form:
   - **Alasan Blokir** (wajib, contoh: "Ketahuan menyontek")
3. Klik **"Blokir Peserta"**

**Efek:**
- Peserta **tidak bisa login lagi** — jika mencoba, muncul:
  > "Akun Anda telah diblokir. Alasan: {alasan}"
- Session aktif **otomatis di-force logout**
- Attempt yang sedang berjalan **di-block** (status = diblokir)
- Badge merah "⛔ Diblokir" muncul di kolom Kehadiran (dengan tooltip alasan)

**Cara Unblock:**
1. Klik tombol **"Unblock"** (warna hijau) yang muncul menggantikan tombol Blokir
2. Confirm dialog "Yakin ingin unblock peserta ini?"
3. Peserta bisa login kembali

**Audit Trail:**
```
panritta_peserta_offline:
- is_blocked: true
- blocked_at: 2026-09-05 17:30:00
- blocked_reason: "Ketahuan menyontek"
```

---

### 5. Time Extension (Tambah Waktu Ujian) ⏱️

**Deskripsi:** Menambah waktu ujian untuk peserta yang mengalami masalah teknis atau situasi darurat.

**Kapan Digunakan:**
- Peserta mengalami masalah koneksi internet
- Listrik padam sementara
- Masalah teknis lain di luar kendali peserta

**Batasan:**
- **Range:** 1-180 menit (max 3 jam)
- **Hanya untuk** peserta yang statusnya **sedang_ujian**
- Alasan **wajib** diisi

**Cara Pakai:**
1. Klik tombol **"+Waktu"** (warna biru) di baris peserta yang sedang ujian
2. Modal muncul dengan form:
   - **Tambahan Waktu (menit)** — misal 15
   - **Alasan** — misal "Peserta mengalami masalah koneksi internet"
3. Klik **"Tambah Waktu"**

**Efek:**
- `batas_waktu` peserta di-update langsung: `batas_waktu = batas_waktu + added_minutes`
- Peserta otomatis mendapat waktu tambahan
- Extension tercatat di tabel `panritta_time_extensions` untuk audit

**Audit Trail:**
```
panritta_time_extensions:
- ujian_peserta_id: 123
- peserta_offline_id: 45
- added_minutes: 15
- reason: "Masalah teknis internet"
- granted_by: 1 (admin user_id)
- granted_at: 2026-09-05 17:30:00
```

---

### 6. Reset Kode Akses 🔑

**Deskripsi:** Reset kode akses peserta karena lupa atau hilang. Kode lama akan tidak berlaku lagi.

**Kapan Digunakan:**
- Peserta lupa kode akses
- Kartu peserta hilang
- Ada kebocoran kode
- Perlu regenerate massal sebelum ujian

**Ada 2 Mode:**

**Mode 1: Reset Individual**
1. Ke halaman **Peserta Offline** (bukan Monitoring)
2. Klik tombol **"Reset Kode"** (warna biru) di baris peserta
3. Konfirmasi "Yakin ingin reset kode akses peserta ini?"
4. Kode baru muncul di **alert hijau** — catat/print sekarang!
5. Alert format: `Peserta P001: Kode Akses = ABC12XYZ`

**Mode 2: Bulk Reset (Semua Peserta di Ujian)**
1. Ke halaman **Peserta Offline**
2. Klik tombol **"Reset Semua Kode"** (warna kuning, di header)
3. Konfirmasi dengan warning yang jelas
4. Semua peserta di-reset sekaligus
5. Auto-redirect ke halaman **Cetak Kartu** untuk print ulang

**Audit Trail:**
```
panritta_peserta_offline:
- kode_akses: [new hash]
- kode_akses_plain: [new plaintext untuk print]
- kode_akses_reset_at: 2026-09-05 17:30:00
- kode_akses_reset_by: 1 (admin user_id)
```

---

### 7. Assign Peserta ke Multiple Ujian 👥

**Deskripsi:** Fleksibilitas untuk 1 peserta mengikuti beberapa ujian offline berbeda tanpa perlu bikin data peserta baru.

**Konsep:**
- Peserta punya **ujian_id** (ujian utama, "native")
- Bisa ditambahkan ke ujian lain via **kehadiran record** (`panritta_peserta_offline_kehadiran`)
- Peserta yang di-assign akan muncul di daftar ujian saat login

**Kapan Digunakan:**
- Peserta ikut multi-day exam yang berbeda
- Peserta cadangan yang perlu akses ke ujian sekaligus
- Reorganisasi peserta antar-kelas/sesi

**Cara Akses:**
1. Ke halaman **Peserta Offline**
2. Klik tombol **"Assign Peserta"** (warna abu-abu)
3. Halaman Assign muncul dengan 3 section:

**Section 1: Copy dari Ujian Lain**
- Dropdown pilih ujian sumber (dengan info jumlah peserta)
- Klik **"Copy Peserta"** → semua peserta dari ujian sumber di-copy
- Duplikat otomatis di-skip

**Section 2: Peserta yang Sudah Assigned**
- Daftar semua peserta yang bisa ikut ujian ini
- Ada label "Native" untuk peserta dengan `ujian_id` yang sama
- Peserta "Native" **tidak bisa dilepas** (harus hapus dari halaman utama)
- Peserta assigned dari ujian lain bisa **Lepas** (unassign)

**Section 3: Peserta Tersedia untuk di-Assign**
- Daftar peserta dari ujian offline LAIN yang belum di-assign
- Checkbox multi-select (dengan "Select All")
- Klik **"Assign Peserta Terpilih"** → assign massal

**Efek:**
- Assign membuat record baru di `panritta_peserta_offline_kehadiran`
- Status kehadiran default: `tidak_hadir` (perlu di-mark hadir manual)
- Unassign menghapus record kehadiran (bukan menghapus peserta)

---

## Perbandingan Fitur Aksi

| Fitur | Kondisi | Efek | Reversible? | Audit |
|-------|---------|------|-------------|-------|
| **Force Logout** | Kapan saja | Kick session | Ya (login ulang) | `offline_participant_sessions.logout_reason` |
| **Blokir** | Kapan saja | Permanent block + kick | Ya (via Unblock) | `panritta_peserta_offline.blocked_*` |
| **Extend Time** | Sedang ujian | Tambah `batas_waktu` | Tidak | `panritta_time_extensions` |

---

## Alur Kerja Pengawas

### Skenario 1: Peserta Suspicious
```
1. Pengawas lihat monitoring dashboard
2. Perhatikan peserta yang IP address-nya aneh (misal beda dari kelas)
3. Peringatan verbal → Jika tidak berhenti:
   a. Force Logout (kesempatan pertama)
   b. Jika lagi terjadi → Blokir dengan alasan jelas
```

### Skenario 2: Masalah Teknis Peserta
```
1. Peserta lapor koneksi internet putus di tengah ujian
2. Pengawas verifikasi (cek Last Activity)
3. Klik "+Waktu" untuk peserta tersebut
4. Isi waktu tambahan (misal 15 menit) + alasan
5. Peserta lanjut ujian dengan waktu extended
```

### Skenario 3: Peserta Cheating
```
1. Pengawas lihat peserta buka HP / bertanya ke teman
2. Klik "Blokir" untuk peserta tersebut
3. Isi alasan: "Ketahuan membuka HP saat ujian pukul XX:XX"
4. Peserta langsung di-kick + tidak bisa login lagi
5. Attempt di-mark diblokir → tidak bisa lanjut
```

---

## Database Schema Reference

### Tabel: `offline_participant_sessions`
Tracking session peserta untuk monitoring & anti multi-device.

```sql
- id
- peserta_offline_id (FK)
- ujian_id (nullable FK)
- session_token (unique, 64 chars)
- status enum('logged_in', 'sedang_ujian', 'selesai', 'logout')
- device_info json (ip, user_agent)
- login_at
- last_activity_at
- logout_at
- logout_reason
- timestamps
```

### Tabel: `panritta_peserta_offline` (kolom baru)
```sql
- is_blocked boolean default false
- blocked_at nullable timestamp
- blocked_reason nullable text
```

### Tabel: `panritta_time_extensions`
Audit trail untuk extension waktu.

```sql
- id
- ujian_peserta_id (FK)
- peserta_offline_id (nullable FK)
- added_minutes int
- reason text
- granted_by (FK users)
- granted_at
- timestamps
```

---

## Routes Reference

### Monitoring
```
GET  /superadmin/ujian/{ujian}/pengawas
     → Dashboard monitoring
     Name: superadmin.ujian.pengawas.index

GET  /superadmin/ujian/{ujian}/pengawas/live
     → JSON API untuk auto-refresh (setiap 5 detik)
     Name: superadmin.ujian.pengawas.live

POST /superadmin/ujian/{ujian}/pengawas/force-logout/{session}
     → Force logout session
     Name: superadmin.ujian.pengawas.force-logout
```

### Pengawas Actions
```
POST /superadmin/ujian/{ujian}/pengawas/peserta/{peserta}/block
     → Blokir peserta
     Body: reason (required, max 500 chars)
     Name: superadmin.ujian.pengawas.block

POST /superadmin/ujian/{ujian}/pengawas/peserta/{peserta}/unblock
     → Unblock peserta
     Name: superadmin.ujian.pengawas.unblock

POST /superadmin/ujian/{ujian}/pengawas/attempt/{attempt}/extend-time
     → Tambah waktu ujian
     Body: added_minutes (1-180), reason (required)
     Name: superadmin.ujian.pengawas.extend-time
```

### Reset Kode Akses
```
POST /superadmin/ujian/{ujian}/peserta-offline/{peserta}/reset-kode
     → Reset kode akses individual
     Name: superadmin.ujian.peserta-offline.reset-kode

POST /superadmin/ujian/{ujian}/peserta-offline/bulk-reset-kode
     → Reset semua kode akses di ujian (redirect ke cetak kartu)
     Name: superadmin.ujian.peserta-offline.bulk-reset-kode
```

### Assign Peserta ke Multiple Ujian
```
GET  /superadmin/ujian/{ujian}/peserta-offline/assign
     → Halaman manajemen assign
     Name: superadmin.ujian.peserta-offline.assign.index

POST /superadmin/ujian/{ujian}/peserta-offline/assign
     → Assign peserta terpilih (multi-select)
     Body: peserta_ids[] (array)
     Name: superadmin.ujian.peserta-offline.assign.store

DELETE /superadmin/ujian/{ujian}/peserta-offline/{peserta}/unassign
     → Lepas peserta dari ujian (tidak hapus data peserta)
     Name: superadmin.ujian.peserta-offline.assign.unassign

POST /superadmin/ujian/{ujian}/peserta-offline/copy-from
     → Copy semua peserta dari ujian lain
     Body: source_ujian_id (required)
     Name: superadmin.ujian.peserta-offline.assign.copy
```

---

## Testing

**Total tests:** 27 passed, 0 regression

**Coverage:**
- Migration tests
- Model relationship tests
- Service layer tests (`OfflineParticipantService`)
- Integration tests (login flow)

**Cara Run Tests:**
```bash
# All feature tests
php artisan test tests/Feature/Feature/

# Specific test
php artisan test tests/Feature/Feature/Peserta/OfflineLoginTest.php --compact
```

---

## FAQ

**Q: Apa bedanya Force Logout dan Blokir?**
A: Force Logout hanya kick session (peserta bisa login lagi). Blokir permanent (peserta tidak bisa login sampai di-unblock).

**Q: Bisakah extend waktu lebih dari sekali?**
A: Ya, bisa berkali-kali. Setiap extension tercatat di audit trail. Total tetap dibatasi 1-180 menit per extension.

**Q: Apakah pengawas bisa lihat siapa yang meng-extend waktu?**
A: Ya, di tabel `panritta_time_extensions` ada field `granted_by` yang mencatat user_id admin. (UI untuk view history akan ada di iterasi berikutnya)

**Q: Kalau IP peserta berubah karena WiFi → mobile hotspot?**
A: Currently middleware masih terima. Fitur strict IP-check bisa di-enable jika diperlukan (edit `OfflineParticipantAuth` middleware).

**Q: Session auto-expired setelah berapa lama?**
A: Currently tidak ada expiry otomatis. Session valid selama tidak di-force logout atau peserta manual logout. Future: bisa add TTL.

**Q: Bagaimana jika admin lupa unblock peserta yang di-block salah?**
A: Bisa unblock kapan saja dari halaman monitoring. Tidak ada audit log untuk unblock currently (bisa ditambahkan).

**Q: Apakah extension waktu berlaku untuk peserta online juga?**
A: Ya, extension bekerja untuk semua peserta yang sedang_ujian (baik online maupun offline), asalkan ada `ujian_peserta_id`.

---

## Roadmap Fitur Superadmin

### Selesai ✅
1. ✅ Monitoring Real-time
2. ✅ Anti Multi-Device Login
3. ✅ Force Logout
4. ✅ Blokir Peserta (Permanent)
5. ✅ Time Extension (Tambah Waktu)
6. ✅ Attendance Management (Individual + Bulk)
7. ✅ Reset Kode Akses Peserta (Individual + Bulk)
8. ✅ Assign Peserta ke Multiple Ujian (Copy from Ujian, Assign, Unassign)

### MEDIUM PRIORITY 📋
9. 📋 Riwayat Session Peserta (History UI)
10. 📋 Live Answer Preview (progress peserta)
11. 📋 Rekap & Export Kehadiran

### LOW PRIORITY 🔮
12. 🔮 Pause/Resume Ujian
13. 🔮 Notifikasi & Alert (WebSocket)
14. 🔮 Analytics Dashboard
15. 🔮 Audit Log lengkap
16. 🔮 Bulk Operations tambahan

---

## Changelog

**2026-09-05** — Reset Kode Akses & Assign Multiple Ujian
- Reset kode akses individual per peserta
- Bulk reset kode akses semua peserta di ujian (langsung ke halaman cetak kartu)
- Assign peserta existing (dari ujian lain) ke ujian ini via UI
- Unassign peserta dari ujian tertentu (tanpa hapus data peserta)
- Copy semua peserta dari ujian lain (bulk)
- Audit trail: kode_akses_reset_at, kode_akses_reset_by di panritta_peserta_offline
- New controllers: PesertaOfflineKodeAksesController, PesertaOfflineAssignController
- New view: assign.blade.php

**2026-09-05** — Blokir Peserta & Time Extension
- Add block/unblock peserta functionality
- Add time extension untuk peserta yang sedang ujian
- Update monitoring dashboard dengan tombol aksi baru
- Add modal Alpine.js untuk konfirmasi block/extend
- New tables: `panritta_time_extensions`
- Migration: add block fields to `panritta_peserta_offline`

**2026-09-05** — Monitoring Pengawas & Anti Multi-Device
- Real-time monitoring dashboard dengan auto-refresh
- Session tracking table: `offline_participant_sessions`
- Multi-device prevention via session token validation
- IP address & user agent tracking

**2026-09-05** — Attendance Management
- Tabel kehadiran: `panritta_peserta_offline_kehadiran`
- UI untuk mark attendance (individual + bulk)
- Integrasi dengan flow login peserta offline

---

## Dokumentasi Terkait

- `docs/PLAN.md` — Master plan implementasi
- `docs/TECH_SPEC.md` — Technical specification
- `docs/OFFLINE_ATTENDANCE_GUIDE.md` — Panduan lengkap attendance flow
- `docs/PENGAWAS_FEATURES.md` — Dokumen ini (fitur pengawas)
