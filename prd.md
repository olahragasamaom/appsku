# Product Requirement Document (PRD) Lanjutan: Manajemen Ujian, Peserta, dan Live Scoring (Fase 2)

**Nama Dokumen:** PRD - Extension Manajemen Ujian & Live Scoring
**Framework Backend/Frontend:** Laravel
**Status:** In Development (Fase 2)

---

## 1. Manajemen Ujian (Exam Management)

Modul ini digunakan oleh admin untuk membuat dan mengatur jadwal serta konfigurasi pelaksanaan ujian.

### 1.1. Parameter Utama (General Setup)
Admin menginputkan konfigurasi dasar ujian:
- **Nama Ujian:** Judul sesi ujian (misal: "Tryout CPNS Nasional Gelombang 1").
- **Tipe Ujian:** Pilihan `Offline di Kelas` atau `Online Paket`.
- **Jenis Ujian:** Checkbox multi-select dari tabel `jenis_ujian` (misal: Checklist [x] SKB, [x] SKD, [ ] TPA).
- **Jumlah Soal:** Kapasitas maksimal soal dalam ujian tersebut.
- **Mode Pengacakan Soal:** Boolean (`Acak` / `Tidak Acak`).
- **Tampilkan Hasil:** Boolean (`Ya` / `Tidak`) untuk menentukan apakah skor langsung terlihat oleh peserta setelah selesai.

### 1.2. Konfigurasi Khusus Berdasarkan Tipe Ujian
Sistem akan menampilkan input dinamis berdasarkan **Tipe Ujian** yang dipilih:

**A. Jika Tipe Ujian = `Offline di Kelas`**
- `tanggal_ujian`: Datetime (Tanggal & Jam mulai ujian).
- `durasi_ujian`: Integer (dalam menit).
- `batas_keterlambatan`: Datetime (Toleransi keterlambatan peserta).
- `token_ujian`: String (Dihasilkan otomatis atau custom oleh admin, dibagikan di kelas).

**B. Jika Tipe Ujian = `Online Paket`**
- `akses_member`: Multi-select jenis keanggotaan yang diizinkan (misal: Free, Basic, Pro, Platinum).

### 1.3. Penentuan Passing Grade (Nilai Ambang Batas)
Setelah admin memilih **Jenis Ujian** (misal SKB dan SKD), sistem akan memunculkan form input lanjutan:
- Passing Grade **SKB**: [ Input Angka ]
- Passing Grade **SKD**: [ Input Angka ]
*(Ini akan digunakan di akhir ujian untuk menentukan status LULUS / TIDAK LULUS per jenis ujian).*

---

## 2. Manajemen Soal di Ujian

Modul untuk memasukkan butir soal ke dalam ujian yang telah dibuat.

### 2.1. Alur Kerja (Workflow)
1. Admin memilih Ujian yang ingin dikelola soalnya.
2. Karena Ujian bisa terdiri dari beberapa **Jenis Ujian**, sistem akan merender **Tab Navigasi / Tombol Kategori** (misal: Tab [ SKB ] | Tab [ SKD ]).
3. Saat admin mengklik Tab **SKB**:
   - Tampil daftar soal khusus SKB di ujian ini.
   - Tersedia tombol **Tambah Soal Manual**, **Pilih dari Bank Soal (Filter SKB)**, dan **Import/Export Soal (Default SKB)**.
   - Form input manual akan secara otomatis mengunci/pre-fill field Jenis Ujian ke "SKB".

---

## 3. Master Peserta / Member

Manajemen user terbagi menjadi dua jalur utama:

### 3.1. Peserta Kolektif (Didaftarkan Admin - Offline)
- **Fungsi:** Untuk peserta yang mengikuti ujian serentak di kelas/lab.
- **Input:** Admin menambahkan via Form (Nama Lengkap, Username, Password) atau Import Excel/CSV.
- **Penyimpanan:** Data masuk ke bank data peserta utama (`users` table dengan role peserta).

### 3.2. Peserta Mandiri (Registrasi Online)
- **Registrasi & Verifikasi:** User mendaftar via Email / No. HP / Social Login (Google OAuth), dilanjutkan verifikasi akun.
- **Pemilihan Paket:** User memilih paket langganan (Free, Basic, Platinum, dll).
  - *Metadata Paket:* Menentukan jumlah/akses ujian, dan fitur ekstra (Video Pembahasan, Analitik, Sertifikat).
- **Pembayaran:** Terintegrasi dengan payment gateway (QRIS, Virtual Account/Transfer).
- **Aktivasi:** Otomatis aktif via *Webhook* setelah pembayaran sukses.
- **Dashboard Peserta Online:** Menampilkan riwayat tryout, paket tersedia, leaderboard nasional, dan informasi CPNS.

---

## 4. Manajemen Peserta Ujian (Alokasi Peserta ke Sesi Offline)

- **Pemilihan Peserta:** Admin membuka ujian tipe `Offline`, mengklik "Tambah Peserta". Muncul **Modal Box** berisi daftar peserta dari Master Data.
- **Checklist & Pengecualian:** Admin mencentang peserta. Peserta yang *sudah masuk* di ujian tersebut tidak akan muncul lagi di Modal Box.
- **Distribusi Akun:** Terdapat tombol **"Cetak Daftar Akun (PDF/Excel)"** yang berisi kolom Nama, Username, dan Password untuk dibagikan ke peserta di ruangan ujian.

---

## 5. Alur Peserta Masuk Ujian (Test Execution)

1. **Login:** Peserta login menggunakan kredensial (dari admin) atau akun mandiri.
2. **Dashboard Ujian:** Sistem menampilkan Daftar Ujian yang berstatus "Aktif" dan dapat diikuti.
3. **Validasi Ujian:** Peserta memilih ujian.
   - Jika `Offline`: Wajib memasukkan **Token** dari pengawas.
   - Jika `Online`: Langsung mulai jika memiliki akses/paket yang sesuai.
4. **Pengerjaan (Test Engine):** Tampilan ujian interaktif. Waktu berjalan mundur (countdown timer) sesuai durasi ujian. Jika durasi habis, ujian tersubmit otomatis.

---

## 6. Dashboard Pengawas & Live Scoring (Proctoring)

Modul kritikal untuk pemantauan sesi ujian (terutama Offline).

### 6.1. Kontrol Akses & Absensi
- **Daftar Peserta:** Admin/Pengawas melihat daftar peserta di dalam ujian tersebut.
- **Blokir & Absensi:** Pengawas dapat menonaktifkan/mendiskualifikasi peserta yang tidak hadir, sehingga akun mereka tidak bisa ujian.
- **Re-aktivasi:** Jika peserta terlambat dan masih dalam batas waktu, pengawas dapat melakukan "Unlock / Aktifkan" agar peserta bisa memulai ujian.

### 6.2. Live Scoring (Real-time Leaderboard)
- Halaman yang me-refresh secara berkala (AJAX/WebSockets) menampilkan peringkat berjalan dari peserta yang sedang mengerjakan ujian.

### 6.3. Analisis Hasil Detail per Peserta
Pengawas dapat mengklik nama peserta (misal: "Peserta A") untuk melihat lembar jawaban digital secara mendetail.
Sistem akan mencocokkan jawaban sesuai **Sistem Penilaian** (lihat PRD Fase 1):

**Format Tampilan Review:**
```text
No 1. [Soal Text / Gambar]
Kategori: SKB - Hukum Materil (Sistem: Benar-Salah)
------------------------------------------------
A. 2 (Kunci Jawaban)
B. 3 (Jawaban Peserta)
C. 5
D. 8
------------------------------------------------
[ Hasil: SALAH (0 Point) ]
```

```text
No 2. [Soal Text / Gambar]
Kategori: SKD - TKP (Sistem: Tiap Jawaban Ada Poin)
------------------------------------------------
A. 1 (Point 1)
B. 2 (Point 2)
C. 3 (Point 3) -> (Jawaban Peserta)
D. 5 (Point 5) -> (Poin Tertinggi/Kunci)
E. 7 (Point 6)
------------------------------------------------
[ Hasil: Point 3 ]
```

### 6.4. Post-Exam (Pasca Ujian)
- **Perangkingan:** Otomatis mengurutkan nilai kumulatif tertinggi.
- **Status Kelulusan:** Sistem membandingkan nilai per Jenis Ujian (SKD, SKB) dengan **Passing Grade** yang dikonfigurasi di poin 1.3. Jika ada satu jenis ujian di bawah passing grade, peserta dinyatakan "TIDAK LULUS" secara keseluruhan, atau sesuai logika kelulusan instansi.
