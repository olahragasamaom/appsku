# Panrita Apps - Platform Ujian Online & Testing SaaS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind">
  <img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white" alt="Alpine.js">
  <img src="https://img.shields.io/badge/CKEditor-5.x-0066CC?style=for-the-badge&logo=ckeditor&logoColor=white" alt="CKEditor">
</p>

**Panrita Apps** adalah platform ujian online dan testing terpadu yang dirancang untuk institusi pendidikan, perusahaan, dan lembaga pemerintah di Indonesia. Dengan fitur lengkap dari pembuatan soal hingga analisis hasil, Panrita Apps menjadi solusi all-in-one untuk kebutuhan testing modern.

## Deskripsi Singkat

Panrita Apps menyediakan ekosistem ujian online yang komprehensif dengan dukungan:
- **Soal Dinamis**: Soal dengan formula LaTeX, gambar, dan HTML rendering
- **Ujian Fleksibel**: Mode offline di kelas dan online paket untuk akses mandiri
- **Analisis Mendalam**: Dashboard hasil ujian dengan breakdown per sub-jenis dan leaderboard
- **Multi-tenant**: Satu platform untuk banyak institusi dengan data terisolasi

---

## Alur Kerja Panrita Apps

```
┌──────────────────┐
│   Superadmin     │
│  Setup Jenis     │
│    Ujian         │
└────────┬─────────┘
         │
         ▼
┌──────────────────────────────────┐
│  Admin Institusi / Pengajar       │
│ • Buat soal & bank soal           │
│ • Kelola ujian (offline/online)   │
│ • Monitor peserta & hasil         │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│  Peserta Ujian                    │
│ • Akses ujian di app/web          │
│ • Kerjakan soal interaktif        │
│ • Lihat hasil & pembahasan        │
└──────────────────────────────────┘
```

---

## Fasilitas Utama

### 1. **Management Soal (Question Bank)**
- Soal dengan dukungan **HTML & LaTeX formula** (KaTeX rendering)
- Upload gambar untuk soal dan opsi jawaban
- Opsi display horizontal/vertikal
- Preview simulasi tampilan ujian
- Badge "Soal bergambar" untuk soal dengan attachment
- Support multiple choice (A-E)
- CKEditor 5 dengan toolbar lengkap
- Auto-escape HTML di database (keamanan)

### 2. **Management Ujian**
| Tipe | Deskripsi |
|------|-----------|
| **Offline di Kelas** | Ujian terjadwal dengan token akses, durasi tetap, batas keterlambatan |
| **Online Paket** | Ujian self-paced, akses member terbatas (Free/Basic/Pro/Platinum), tanpa jadwal |

**Status Ujian:**
- Draft (bisa edit)
- Aktif (sedang berjalan)
- Selesai (lock, tidak ada peserta baru)

### 3. **Jenis & Sub-Jenis Ujian**
Hierarchy struktur ujian untuk mapping soal:
- **Jenis Ujian** (CPNS, SNMPTN, TPS, Skripsi, dll)
  - **Sub-Jenis Ujian** (TWK, TIU, TKP, Bahasa, dll)
    - **Sub-Indikator** (Breakdown topik per sub-jenis)
- **Passing Grade** configurable per sub-jenis

### 4. **Ujian Offline di Kelas**
- Peserta login dengan username/password + PIN ujian
- Token akses verifikasi peserta
- Timer countdown real-time
- Navigasi full dengan nomor soal
- Highlight soal sudah dijawab
- Validasi sebelum submit final
- Offline session storage (aman jika koneksi putus)
- Review jawaban & pembahasan setelah selesai

### 5. **Ujian Online Paket**
- Peserta akses dari portal 24/7
- Self-paced, tidak ada deadline ketat
- Bisa keluar dan kembali lagi
- Auto-save progress setiap jawaban
- Langganan berdasarkan paket
- Link shareable

### 6. **Dashboard Hasil Ujian**
Per peserta, menampilkan:
- **Nilai per sub-jenis** (TWK, TIU, TKP, dll)
- **Passing grade comparison** per sub-jenis
- **Status LULUS/TIDAK LULUS** per breakdown
- **Progress bar** dengan color coding (≥75% hijau, 50-74% kuning, <50% merah)
- **Pembahasan soal** dengan jawaban benar & penjelasan
- **Leaderboard** ranking peserta dengan filter

### 7. **Management Peserta**
| Mode | Fitur |
|------|-------|
| **Online** | Registrasi email, login peserta, multi-ujian support, langganan tier |
| **Offline** | Generate QR code per peserta, PIN otomatis, session berbasis token |

**Tracking:**
- Peserta aktif vs non-aktif
- Status sudah/belum mengerjakan
- Waktu mulai & selesai ujian
- Absensi otomatis

### 8. **Monitoring & Reporting**
- **Live Score**: Monitoring nilai real-time saat ujian berlangsung
- **Hasil Ujian**: Tabel lengkap semua peserta dengan nilai, status, waktu
- **Absensi**: Tracking peserta yang sudah/belum mengerjakan
- **Pembahasan**: Per peserta bisa lihat jawaban & evaluasi
- **Export**: Unduh hasil ke Excel dengan formatting

### 9. **HTML & Formula Rendering**
- Soal disimpan di DB dengan HTML **escaped** (keamanan XSS)
- Render otomatis dengan **KaTeX** untuk formula:
  - Input: `[[\\frac{3}{5}]]`
  - Output: Fraction rendered dengan KaTeX
  - Error handling: Invalid formula tampil badge merah
- Support tag safe: `<p>`, `<b>`, `<i>`, `<img>`
- CKEditor 5 WYSIWYG dengan:
  - Image upload → `/public/uploads` disk
  - Toolbar: heading, bold, italic, lists, blockquote, link
  - Data flow: escape on save, unescape on edit/display
- KaTeX CSS + JS loaded synchronously

### 10. **Keamanan & Audit**
- **Multi-tenant isolation**: `company_id` di setiap tabel
- **Role-based access**: Superadmin, Admin Institusi, Pengajar, Peserta, Offline Participant
- **Offline participant auth**: Middleware verifikasi token + offline_session
- **Rate limiting**: Proteksi brute force login
- **Activity log**: Audit trail setiap perubahan soal/ujian
- **CSRF protection**: Token di form
- **SQL injection prevention**: Parameterized queries (Laravel Eloquent)

---

## Statistik Project

| Metrik | Jumlah | Keterangan |
|--------|--------|------------|
| **Models** | 28 | Soal, Ujian, UjianPeserta, JenisUjian, SubJenisUjian, dll |
| **Controllers (Web)** | 18 | Superadmin, Peserta, Pengajar dashboard |
| **Blade Views** | 85+ | Editor, preview, hasil ujian, monitoring |
| **Database Migrations** | 35+ | Schema ujian, peserta, hasil, tracking |
| **Test Files** | 16+ | Unit & feature tests (Pest PHP) |
| **Services** | 8+ | FormulaRenderer, UjianScoringService, OfflineParticipantService |
| **Database Tables** | 45+ | Multi-tenant architecture |

---

## Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.3.22 | Runtime |
| Laravel | 12.x | Framework |
| MySQL | 8.x | Database |
| Spatie Permission | 6.x | Role & Permission |
| Laravel Sanctum | 4.x | API Authentication |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| Blade | - | Template Engine |
| Tailwind CSS | 4.x | Styling |
| Alpine.js | 3.x | Interactivity |
| CKEditor 5 | 5.x | WYSIWYG Editor (self-hosted GPL) |
| KaTeX | Latest | Math formula rendering |
| Vite | 6.x | Asset bundling |

### Testing & Quality
| Technology | Version | Purpose |
|------------|---------|---------|
| Pest PHP | 3.x | Testing framework |
| Laravel Pint | 1.x | Code style linting |

---

## Instalasi & Setup

### Requirement
- PHP 8.3+
- MySQL 8.0+
- Composer
- Node.js 18+

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone <repo-url> appsku
   cd appsku
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed --class=SuperadminSeeder
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Jalankan server**
   ```bash
   php artisan serve
   ```

Akses: http://localhost:8000

---

## Login Default

| Role | Username | Password |
|------|----------|----------|
| Superadmin | superadmin | alauddin321 |

---

## Struktur Database

**Core Tables:**
- `panritta_soal` - Bank soal
- `panritta_ujian` - Ujian
- `panritta_ujian_soal` - Mapping ujian ↔ soal
- `panritta_ujian_peserta` - Hasil peserta per ujian
- `panritta_ujian_peserta_jawaban` - Jawaban detail per soal
- `panritta_jenis_ujian` - Jenis ujian (CPNS, dll)
- `panritta_sub_jenis_ujian` - Sub jenis (TWK, TIU, TKP)
- `panritta_sub_indikator` - Indikator topik
- `panritta_peserta_offline` - Data peserta offline
- `panritta_peserta_offline_jawaban` - Jawaban offline

---

## Testing

### Run Tests
```bash
php artisan test --compact
```

### Run Specific Test
```bash
php artisan test --compact --filter="Soal"
```

### Linting
```bash
vendor/bin/pint --dirty --format agent
```

---

## API Documentation

REST API tersedia di `/api/documentation` (Swagger UI)

**Total Endpoints:** 30+ untuk ujian, peserta, dan hasil

---

## Git Workflow

**Commit sebelum push:**
```bash
git status
git diff
git add -A
git commit -m "feat: deskripsi perubahan"
git push
```

**Never:**
- Force push
- Commit secrets/keys
- Amend setelah failed commit

---

## Support & Contact

- **Email**: support@panrita.com
- **Documentation**: [docs.panrita.com](https://docs.panrita.com)

---

<p align="center">
  <strong>Panrita Apps</strong> - Solusi Ujian Online Modern untuk Indonesia
</p>
