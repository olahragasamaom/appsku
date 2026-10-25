# 🚀 Panduan Migrasi GajiPro ke Hostinger

**Migrasi Project Laravel 12 tanpa akses SSH**

---

## 📋 Persiapan & Persyaratan

### Yang Anda Butuhkan:
- ✅ Akses cPanel Hostinger (admin username & password)
- ✅ File Manager atau FTP Client (FileZilla, WinSCP, dll)
- ✅ MySQL/MariaDB Management (phpMyAdmin di cPanel)
- ✅ Text Editor untuk mengedit file .env
- ✅ Backup lengkap dari project lokal

### ⚠️ Penting
Hostinger shared hosting **TIDAK memiliki akses SSH**, sehingga Anda tidak bisa menjalankan `php artisan` commands secara langsung di server. **Semua persiapan harus dilakukan di mesin lokal Anda terlebih dahulu.**

### ℹ️ Persyaratan Server Hostinger
Pastikan Hostinger Anda mendukung:
- PHP 8.3 atau lebih tinggi
- MySQL 5.7+ atau MariaDB
- Node.js (untuk build npm - jika tersedia)
- Composer (untuk install dependencies - jika tersedia)

---

## Step 1️⃣ Backup Database

### Opsi A: Menggunakan cPanel (Hostinger) - Jika sudah punya database di Hostinger

#### 1. Login ke cPanel Hostinger
- Buka `https://nama-domain.com:2083` atau URL cPanel yang diberikan Hostinger
- Masukkan username & password cPanel
- Cari "phpMyAdmin" di dashboard cPanel

#### 2. Export Database
Di phpMyAdmin:
1. Pilih database aplikasi di panel kiri
2. Klik tab "Export"
3. Format: SQL (default)
4. Klik "Go" untuk download file .sql

**Simpan file backup ini di folder aman!**

### Opsi B: Menggunakan MySQL Dump (Windows/Local)

```bash
mysqldump -u root -p database_name > backup_gajipro.sql
```

Ganti `database_name` dengan nama database lokal Anda. Password akan diminta setelah menjalankan command.

### ✅ Backup Complete
Anda sudah memiliki file `backup_gajipro.sql` yang siap di-restore.

---

## Step 2️⃣ Build NPM & Assets

Karena Hostinger tidak punya akses SSH, **Anda harus mengcompile semua assets (CSS, JS) di mesin lokal SEBELUM upload.**

### Build Frontend Assets

```bash
npm install
npm run build
```

Perintah ini akan:
- Install dependencies dari `package.json`
- Build Tailwind CSS v4
- Build Vite assets
- Generate manifest file

### ⚠️ Penting
**Verifikasi folder `public/build` sudah berisi hasil build sebelum upload. Jika kosong, build gagal.**

### Verifikasi Build Assets

Setelah build, pastikan folder berikut ada:
- `public/build/` - Folder CSS & JS yang sudah di-compile
- `public/build/manifest.json` - File manifest untuk Vite

### ✅ Assets Ready
Semua assets sudah siap di-upload ke Hostinger.

---

## Step 3️⃣ Persiapan File untuk Upload

### File & Folder yang HARUS Di-Upload

| File/Folder | Keterangan | Upload? |
|---|---|---|
| `app/` | Source code aplikasi | ✅ Ya |
| `bootstrap/` | Bootstrap files Laravel | ✅ Ya |
| `config/` | Konfigurasi aplikasi | ✅ Ya |
| `database/` | Migrations & Seeders | ✅ Ya |
| `lang/` | Language files | ✅ Ya |
| `public/` | Public assets (termasuk build/) | ✅ Ya |
| `resources/` | Views & CSS source | ✅ Ya |
| `routes/` | Route definitions | ✅ Ya |
| `storage/` | Logs & uploads | ✅ Ya |
| `vendor/` | Composer dependencies | ⚠️ Optional* |
| `node_modules/` | NPM dependencies | ❌ JANGAN |
| `.env` | Environment variables | ❌ JANGAN (buat baru) |
| `composer.json`, `composer.lock` | Dependency definitions | ✅ Ya |
| `artisan` | Artisan CLI | ✅ Ya |

**\*vendor/** bersifat optional karena ukurannya sangat besar (~100MB+). Jika Hostinger punya Composer, lebih baik upload hanya composer.json dan jalankan `composer install` melalui cPanel.

### File yang HARUS DI-ABAIKAN

- `node_modules/` - **JANGAN UPLOAD** (terlalu besar, rebuild di server jika perlu)
- `.env` - **JANGAN UPLOAD** (buat baru di Hostinger)
- `.git/` - **JANGAN UPLOAD** (kontrol versi lokal)
- `tests/` - Tidak perlu (testing files)
- `.editorconfig`, `.gitignore` - Tidak perlu

### Struktur Upload ke Hostinger

Jika domain Anda `gajipro.hostinger.com`, upload semua file ke:

```
public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── lang/
├── public/          (ini adalah root web)
├── resources/
├── routes/
├── storage/
├── vendor/          (optional)
├── composer.json
├── composer.lock
├── artisan
├── package.json
└── .env.example     (untuk referensi)
```

---

## Step 4️⃣ Upload ke Hostinger

### Opsi A: Menggunakan File Manager cPanel (Recommended - Termudah)

#### 1. Login ke cPanel Hostinger
Buka `https://nama-domain.com:2083` dengan username & password cPanel.

#### 2. Buka File Manager
Di dashboard cPanel, cari & klik "File Manager".

#### 3. Navigasi ke public_html
Klik "public_html" di panel kiri untuk membuka folder root aplikasi.

#### 4. Upload Files
Gunakan fitur "Upload" untuk upload file/folder. Untuk folder besar, lebih baik zip terlebih dahulu:
- Zip folder project lokal (exclude node_modules & vendor)
- Upload file .zip
- Extract di File Manager cPanel

### Opsi B: Menggunakan FTP Client (FileZilla)

#### 1. Dapatkan FTP Credentials
Di cPanel Hostinger:
- Cari "FTP Accounts"
- Lihat FTP username & password untuk akun default
- Host: `ftp.nama-domain.com`

#### 2. Setup FileZilla
Open FileZilla:
- **Host:** `ftp.nama-domain.com`
- **Username:** FTP username dari cPanel
- **Password:** FTP password
- **Port:** 21
- Klik "Quickconnect"

#### 3. Upload via FileZilla
Di panel kanan (remote), navigasi ke `/public_html`. Drag & drop folder project dari local ke remote panel.

### ⏱️ Upload Time
Upload bisa memakan waktu lama tergantung ukuran file & kecepatan internet. **Pastikan koneksi stabil.**

---

## Step 5️⃣ Konfigurasi .env di Hostinger

### Buat File .env Baru

Menggunakan File Manager cPanel:

#### 1. Buka File Manager
Navigasi ke folder `public_html` (root aplikasi).

#### 2. Create File
Klik kanan di area kosong → "Create New File" → Beri nama `.env`.

#### 3. Edit .env
Klik file `.env` → "Edit" → Copy-paste konfigurasi di bawah:

```env
APP_NAME=GajiPro
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://nama-domain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (sesuaikan dengan database Hostinger)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=email@domain.com
MAIL_PASSWORD=password_email
MAIL_FROM_ADDRESS="noreply@domain.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

### Konfigurasi Database Hostinger

Dapatkan database credentials dari cPanel:

#### 1. Buka cPanel → MySQL Databases
Lihat database yang sudah dibuat atau buat database baru di sini.

#### 2. Catat Database Credentials
Catat informasi:
- Database Name
- Database User
- Database Password
- Host (biasanya `localhost`)

#### 3. Update .env
Isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` di file .env dengan info dari cPanel.

### Generate APP_KEY

Untuk generate APP_KEY, ada 2 opsi:

#### Opsi A: Generate Lokal (Recommended)

```bash
php artisan key:generate
```

Copy nilai `APP_KEY` dari `.env` lokal Anda dan paste ke `.env` di Hostinger.

#### Opsi B: Generate Manual

Jika tidak bisa generate lokal, gunakan value yang di-generate secara random:

```
base64:8hP9GbJ2k3L4mN5oP6qR7sT8uV9wX0yZ1aB2cD3eF4gH5iJ
```

Bisa generate dengan membuka: `https://www.random.org/strings/` dan ambil random string 32 karakter, lalu format menjadi `base64:` + string.

### ⚠️ Penting
Ganti semua placeholder di .env dengan informasi sebenarnya:
- `APP_URL` → URL domain Anda di Hostinger
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` → credentials database
- `APP_KEY` → base64 key (jangan kosong!)
- `MAIL_*` → konfigurasi email dari Hostinger

---

## Step 6️⃣ Konfigurasi cPanel & Database

### Set Permissions Folder

Laravel membutuhkan folder tertentu bisa write oleh web server. Di File Manager cPanel:

#### 1. Ubah Permission storage/
Klik folder `storage` → Properties → Set permission ke `755` (atau biarkan default).

#### 2. Ubah Permission bootstrap/cache/
Klik folder `bootstrap/cache` → Properties → Set permission ke `755`.

### Setup Public Folder sebagai Root

Di cPanel, web server harus point ke folder `public`:

#### 1. Buka cPanel → Addon Domains atau Domains
Cari domain Anda di list.

#### 2. Edit Document Root
Ubah Document Root dari `public_html` menjadi `public_html/public`.

**⚠️ Penting:** Jika cPanel tidak memiliki opsi mengubah document root, gunakan .htaccess di folder public_html untuk redirect ke public/index.php.

### Restore Database

#### 1. Buka phpMyAdmin
Di cPanel, klik "phpMyAdmin".

#### 2. Pilih Database
Klik database yang sudah dibuat di panel kiri.

#### 3. Import Data
Klik tab "Import" → Pilih file `backup_gajipro.sql` → Klik "Go".

Tunggu proses import selesai (bisa beberapa menit untuk database besar).

### ✅ Database Restored
Data sudah di-restore ke database Hostinger.

### Optional: Jalankan Database Migrations

Jika ada migration baru yang belum dijalankan:

**⚠️ Catatan:** Karena tidak punya akses SSH, Anda tidak bisa menjalankan `php artisan migrate` di Hostinger. Solusi alternatif:
- **Opsi A:** Jalankan migration di lokal, lalu backup & restore database.
- **Opsi B:** Hubungi Hostinger support untuk menjalankan command artisan (beberapa paket menyediakan fitur ini).
- **Opsi C:** Buat route khusus untuk menjalankan migration (tidak recommended untuk production).

---

## Step 7️⃣ Verifikasi & Testing

### Cek Apakah Aplikasi Running

#### 1. Akses Domain
Buka browser dan akses domain Anda: `https://nama-domain.com`

Jika melihat halaman default Laravel atau landing page aplikasi → ✅ Aplikasi running!

#### 2. Cek Error Logs
Jika ada error, cek file log di cPanel:
- cPanel → "Error" atau "Error Log"
- Atau buka file: `storage/logs/laravel.log` via File Manager

### Testing Features

- [ ] Login page muncul dengan benar
- [ ] Database query berjalan (cek di phpMyAdmin)
- [ ] Assets CSS & JS dimuat (check browser DevTools → Network tab)
- [ ] Form submission bekerja
- [ ] Database insert/update/delete bekerja
- [ ] Email notification terkirim (jika ada)
- [ ] File upload bekerja (jika ada feature upload)

### Common Issues & Solutions

| Error | Penyebab | Solusi |
|---|---|---|
| 500 Internal Server Error | APP_KEY kosong atau config error | Cek .env, pastikan APP_KEY ada. Cek error log. |
| SQLSTATE[HY000]: General error | Database tidak tersambung | Cek DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env |
| Assets (CSS/JS) tidak dimuat | public/build/ tidak ada atau manifest error | Pastikan npm run build sudah dijalankan lokal sebelum upload |
| Blank page (no error) | APP_DEBUG=false tapi tidak ada error log | Ubah APP_DEBUG=true sementara, cek error message di browser |
| Permission denied pada storage/ | Folder permission terlalu ketat | Set folder permission ke 755, file ke 644 |

---

## 🔧 Troubleshooting

### Error: "No 'Access-Control-Allow-Origin' header"

**Penyebab:** CORS issue (jarang untuk single-domain app)

**Solusi:**
- Pastikan `APP_URL` di .env sesuai dengan domain yang diakses
- Jika ada API endpoint, tambahkan CORS middleware di `app/Http/Kernel.php`

### Error: "Class not found"

**Penyebab:** Autoloader tidak ter-refresh atau vendor belum ada

**Solusi:**
- Upload folder `vendor/` (jika belum)
- Atau minta Hostinger support menjalankan `composer install`

### Error: "Symfony\Component\Debug\Exception\FlattenException"

**Penyebab:** Error saat runtime, lihat detail error di log

**Solusi:**
- Ubah APP_DEBUG=true untuk melihat detail error
- Cek `storage/logs/laravel.log`
- Cek cPanel Error Log

### Database tidak connect

**Penyebab:** Credentials database salah atau database belum ter-create

**Solusi Checklist:**
- [ ] Buka phpMyAdmin di cPanel
- [ ] Verifikasi database exist (lihat di panel kiri)
- [ ] Verifikasi database user ada permission (cPanel → MySQL Databases → MySQL Users)
- [ ] Cek .env: `DB_HOST` biasanya `localhost`
- [ ] Test connection via phpMyAdmin (bisa login?)

### Build assets hilang (CSS/JS tidak muncul)

**Penyebab:** `npm run build` tidak dijalankan lokal sebelum upload

**Solusi:**
1. Buka project lokal
2. Jalankan `npm install && npm run build`
3. Verifikasi folder `public/build/` ada
4. Re-upload folder `public/` (atau gunakan FTP sync)
5. Clear browser cache (Ctrl+Shift+Del) atau gunakan Incognito mode

### Storage folder tidak bisa write

**Penyebab:** Permission folder terlalu ketat

**Solusi:**
- File Manager → folder `storage` → Properties → Permission 755
- Folder `bootstrap/cache` → Properties → Permission 755

### 404 Not Found untuk semua route

**Penyebab:** Document root tidak pointing ke `public` folder

**Solusi:**
- cPanel → Addon Domains → Edit Domain → Ubah Document Root ke `public_html/public`
- Atau gunakan .htaccess rewrite rules (lihat di bawah)

### .htaccess untuk URL Rewrite (Jika Tidak Bisa Ubah Document Root)

Jika cPanel tidak mengizinkan ubah document root, buat file `.htaccess` di `public_html/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## ✅ Checklist Final Migrasi

Sebelum menganggap migrasi selesai, pastikan semua item di bawah sudah dicek:

- [ ] Database backup sudah dibuat & disimpan aman
- [ ] `npm run build` sudah dijalankan lokal
- [ ] Folder `public/build/` ada & berisi assets
- [ ] Semua file sudah di-upload ke public_html (kecuali node_modules & vendor jika dipilih)
- [ ] File `.env` sudah dibuat dengan config yang benar
- [ ] `APP_KEY` sudah di-set di .env
- [ ] Database sudah di-create & database user sudah ada di cPanel
- [ ] Database credentials di .env sudah sesuai
- [ ] Database backup sudah di-restore via phpMyAdmin
- [ ] Permission `storage/` & `bootstrap/cache/` sudah di-set ke 755
- [ ] Domain sudah pointing ke `public_html/public` (atau .htaccess sudah dibuat)
- [ ] Domain bisa diakses & halaman muncul
- [ ] Login berhasil & dapat mengakses data dari database
- [ ] CSS & JS assets sudah dimuat dengan benar
- [ ] Form submission & CRUD operations bekerja

### 🎉 Selesai!
Aplikasi GajiPro sudah berhasil di-migrasi ke Hostinger!

---

## 📌 Quick Reference

### Commands yang Penting (Jalankan Lokal)

| Perintah | Tujuan |
|---|---|
| `composer install` | Install dependencies PHP |
| `npm install` | Install dependencies JavaScript |
| `npm run build` | Build CSS & JS untuk production |
| `php artisan key:generate` | Generate APP_KEY |
| `php artisan migrate` | Jalankan database migrations |
| `mysqldump -u root -p db > backup.sql` | Backup database MySQL |

### File Konfigurasi Penting

| File | Fungsi |
|---|---|
| `.env` | Environment variables (database, app key, dll) |
| `config/app.php` | Konfigurasi aplikasi |
| `bootstrap/app.php` | Konfigurasi kernel & middleware (Laravel 12) |
| `config/database.php` | Konfigurasi database |

### Folder Penting

| Folder | Fungsi |
|---|---|
| `public/` | Public folder (web root) - HARUS point ke sini |
| `public/build/` | Assets hasil build (CSS, JS) |
| `storage/` | Log files & uploads - harus writable |
| `bootstrap/cache/` | Cache files - harus writable |
| `database/` | Migrations & seeders |

---

## 💬 Support & Bantuan Lanjutan

### Jika Masih Ada Error:

1. **Cek Error Log**
   - cPanel → Error Log untuk PHP errors
   - `storage/logs/laravel.log` untuk Laravel-specific errors

2. **Debug Mode**
   - Ubah APP_DEBUG=true di .env (untuk testing saja, ubah kembali ke false setelah selesai)
   - Lihat detail error di browser

3. **Hubungi Hostinger Support**
   - Jika ada masalah dengan server/database/PHP version
   - Minta bantuan untuk: composer install, permissions, document root config

### Resources Tambahan:
- Laravel Documentation: https://laravel.com/docs/12
- Hostinger cPanel Guide: https://support.hostinger.com/
- Tailwind CSS Docs: https://tailwindcss.com/docs

---

## ℹ️ Catatan Akhir

Panduan ini untuk GajiPro (Laravel 12 + Tailwind CSS 4 + Alpine.js). Jika ada perubahan struktur aplikasi di masa depan, beberapa langkah mungkin perlu disesuaikan.

**Panduan Migrasi GajiPro ke Hostinger | Dibuat Oktober 2026**

Untuk pertanyaan lebih lanjut, hubungi tim development.
