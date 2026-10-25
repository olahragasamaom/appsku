# 🔧 Commands Reference - Migrasi GajiPro ke Hostinger

Daftar lengkap commands yang perlu dijalankan untuk migrasi.

---

## Step 1: Backup Database (Di Lokal)

### Menggunakan MySQL Command Line

```bash
mysqldump -u root -p database_name > backup_gajipro.sql
```

**Penjelasan:**
- `-u root` = MySQL username (ganti dengan username Anda)
- `-p` = Password will be prompted
- `database_name` = Nama database (ganti dengan nama database Anda)
- `> backup_gajipro.sql` = Output file untuk backup

**Contoh:**
```bash
mysqldump -u root -p gajipro > backup_gajipro.sql
```

Setelah command selesai, cek file `backup_gajipro.sql` di directory saat ini.

---

## Step 2: Build NPM Assets (Di Lokal)

### Install Dependencies

```bash
npm install
```

Jalankan di folder root project. Akan menginstall semua packages dari `package.json`.

### Build untuk Production

```bash
npm run build
```

Akan mengcompile:
- Tailwind CSS v4
- Vite assets
- JavaScript bundling
- Generate `public/build/manifest.json`

### Verifikasi Build Berhasil

```bash
dir public\build
```

atau untuk Linux/Mac:

```bash
ls -la public/build/
```

Output harus menunjukkan folder dengan file-file:
- `app.*.js`
- `app.*.css`
- `manifest.json`
- dll

---

## Step 3: Generate APP_KEY (Di Lokal)

### Generate Key untuk Hostinger

```bash
php artisan key:generate
```

Command ini akan:
- Generate random APP_KEY
- Otomatis update di file `.env` lokal
- Format: `base64:xxxxxxxxxxxxx`

### Lihat APP_KEY yang Dihasilkan

```bash
cat .env | grep APP_KEY
```

atau untuk Windows:

```powershell
Select-String "^APP_KEY=" .env
```

Copy value `APP_KEY` untuk dipaste ke `.env` di Hostinger.

---

## Step 4: Upload ke Hostinger

### A. Menggunakan FTP (FileZilla Command Line)

Jika menggunakan FTP client command line:

```bash
ftp ftp.nama-domain.com
```

Kemudian:
```
user: ftp_username
pass: ftp_password
cd /public_html
lcd C:\path\to\project
mput *
```

### B. Menggunakan SFTP (Jika Tersedia)

```bash
sftp username@nama-domain.com
cd /public_html
put -r .
```

### C. Manual via File Manager cPanel

- Login cPanel → File Manager
- Navigate ke `public_html/`
- Upload files via UI

---

## Step 5: Konfigurasi Database di Hostinger

### Tidak ada command khusus, gunakan phpMyAdmin:

1. Login cPanel
2. Buka phpMyAdmin
3. Pilih database
4. Tab "Import"
5. Upload file `backup_gajipro.sql`
6. Klik "Go"

---

## Step 6: Verify Installation (Di Lokal - Optional)

### Test Database Connection

```bash
php artisan migrate:status
```

Jika output menunjukkan list migrations, database connection berjalan normal.

### Jalankan Tests (Di Lokal - Optional)

```bash
php artisan test
```

atau untuk testing tertentu:

```bash
php artisan test --filter=TestName
```

---

## Troubleshooting Commands

### Clear Laravel Cache

Di Hostinger, jika tidak bisa via command, akses via Route atau cPanel:

**Lokal (untuk testing):**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Generate Sitemap (Jika Ada)

```bash
php artisan sitemap:generate
```

### Reset Database (HATI-HATI!)

**Lokal saja (JANGAN di production):**
```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### Check PHP Version

```bash
php --version
```

### Check Composer Version

```bash
composer --version
```

### Check npm Version

```bash
npm --version
```

---

## Database Commands (Di Lokal)

### Create Database

```bash
mysql -u root -p
mysql> CREATE DATABASE gajipro;
mysql> CREATE USER 'gajipro_user'@'localhost' IDENTIFIED BY 'password123';
mysql> GRANT ALL PRIVILEGES ON gajipro.* TO 'gajipro_user'@'localhost';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;
```

### Export Database

```bash
mysqldump -u gajipro_user -p gajipro > gajipro_backup.sql
```

### Import Database

```bash
mysql -u gajipro_user -p gajipro < gajipro_backup.sql
```

### Backup All Databases

```bash
mysqldump -u root -p --all-databases > all_databases_backup.sql
```

### Check Database Size

```bash
mysql -u root -p
mysql> SELECT table_schema, ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables GROUP BY table_schema;
```

---

## SSH Commands (Jika Hostinger Beri SSH Access - Jarang)

### Jalankan Migration

```bash
php artisan migrate --force
```

### Jalankan Seeder

```bash
php artisan db:seed
```

### Jalankan Spesific Seeder

```bash
php artisan db:seed --class=DatabaseSeeder
```

### Clear All Caches

```bash
php artisan optimize:clear
```

### Cache Configuration

```bash
php artisan config:cache
```

### Optimize Autoload

```bash
composer install --optimize-autoloader --no-dev
```

---

## Common Command Sequences

### Full Setup Sequence (Di Lokal Sebelum Deploy)

```bash
# Install dependencies
composer install --no-dev
npm install

# Generate APP_KEY
php artisan key:generate

# Build assets
npm run build

# Run migrations (di lokal untuk test)
php artisan migrate

# Backup database
mysqldump -u root -p database_name > backup_gajipro.sql
```

### Database Refresh (Di Lokal - DANGER)

```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### View Application Logs (Di Lokal)

```bash
# Follow logs
php artisan pail

# atau buka file langsung
tail -f storage/logs/laravel.log

# Windows
Get-Content storage/logs/laravel.log -Tail 50 -Wait
```

---

## File Editing Commands (Windows PowerShell)

### View File Content

```powershell
Get-Content .env
```

### Search in File

```powershell
Select-String "APP_KEY" .env
```

### Replace in File

```powershell
(Get-Content .env) -replace "old_value", "new_value" | Set-Content .env
```

### Create Backup of File

```powershell
Copy-Item .env .env.backup
```

---

## Port & Service Checks

### Check if Port 3306 (MySQL) is Open

```bash
netstat -an | findstr 3306
```

or Windows:

```powershell
netstat -ano | findstr ":3306"
```

### Check if Application Running on Port 8000

```bash
netstat -an | findstr 8000
```

---

## Git Commands (Version Control)

### Clone Repository

```bash
git clone https://github.com/username/repo.git
cd repo
```

### Check Status

```bash
git status
```

### View Logs

```bash
git log --oneline -10
```

### Create New Branch

```bash
git checkout -b feature-name
```

### Commit Changes

```bash
git add .
git commit -m "Message"
git push origin branch-name
```

---

## Useful One-Liners

### Check All Installed PHP Extensions

```bash
php -m
```

### Generate Random Password

```bash
php -r "echo bin2hex(random_bytes(16));"
```

or Windows:

```powershell
[System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes((Get-Random))) 
```

### Test Laravel Installation

```bash
php artisan tinker
>>> echo "Laravel works!";
>>> exit
```

### Count Lines of Code

```bash
find . -name "*.php" -type f -exec wc -l {} + | tail -1
```

---

## Quick Cheatsheet

| Task | Command |
|---|---|
| Backup database | `mysqldump -u root -p db > backup.sql` |
| Build assets | `npm run build` |
| Generate APP_KEY | `php artisan key:generate` |
| Clear cache | `php artisan cache:clear` |
| Run migrations | `php artisan migrate` |
| Run tests | `php artisan test` |
| Check Laravel version | `php artisan --version` |
| Start dev server | `php artisan serve` |
| Check PHP version | `php --version` |
| Check npm version | `npm --version` |
| List installed packages | `npm list` |
| Update npm packages | `npm update` |

---

## Notes

- 🔴 **JANGAN** jalankan destructive commands (`migrate:reset`, `migrate:fresh`) di production
- 🟡 **HATI-HATI** dengan commands yang mengubah database - selalu backup dulu
- 🟢 **SELALU** test commands di lokal development dulu sebelum production
- 📝 Hosting Hostinger biasanya tidak punya SSH, jadi kebanyakan commands hanya bisa di lokal
- 🔐 **JANGAN** commit `.env` atau passwords ke git repository

---

**Last Updated:** Oktober 2026
**For:** GajiPro Migration to Hostinger
