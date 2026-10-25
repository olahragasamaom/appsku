# 📚 Dokumentasi Migrasi GajiPro ke Hostinger

Panduan lengkap untuk migrasi project GajiPro (Laravel 12) ke hosting Hostinger **tanpa akses SSH**.

## 📁 File-File Dokumentasi

Ada 3 file panduan yang telah dibuat untuk membantu migrasi Anda:

### 1. **PANDUAN_MIGRASI_HOSTINGER.md** (Format Markdown) ⭐ RECOMMENDED
- **Format:** Markdown (.md)
- **Ukuran:** ~16 KB
- **Kegunaan:** 
  - Mudah dibaca di GitHub, GitLab, atau text editor
  - Bisa di-copy-paste ke dokumentasi internal
  - Support untuk formatting tabel, code blocks, checklist
  - **Terbaik untuk:** Referensi lengkap saat migrasi
- **Cara buka:**
  - Buka di text editor (VS Code, Sublime, dll)
  - Atau buka di GitHub/GitLab preview
  - Atau buka di browser (save as HTML)

### 2. **PANDUAN_MIGRASI_HOSTINGER.html** (Format HTML - Print-Friendly)
- **Format:** HTML (.html)
- **Ukuran:** ~41 KB
- **Kegunaan:**
  - Bisa dibuka langsung di browser
  - Support untuk print ke PDF (Ctrl+P di browser)
  - Layout yang rapi & mudah dibaca
  - **Terbaik untuk:** Convert ke PDF atau print
- **Cara buka:**
  - Buka di browser (double-click file atau drag ke browser)
  - Print ke PDF: Ctrl+P → Save as PDF
  - Support Chrome, Firefox, Edge, Safari

### 3. **CHECKLIST_MIGRASI_HOSTINGER.txt** (Format Text - Printable Checklist)
- **Format:** Plain text (.txt)
- **Ukuran:** ~10 KB
- **Kegunaan:**
  - Checklist praktis untuk tracking progress migrasi
  - Bisa di-print langsung
  - Field untuk isi credentials & notes
  - **Terbaik untuk:** Tracking & dokumentasi selama migrasi
- **Cara buka:**
  - Buka di text editor
  - Print langsung ke kertas
  - Print ke PDF

---

## 🎯 Rekomendasi Penggunaan

### Skenario A: Saya ingin membaca dokumentasi secara lengkap
→ Gunakan **PANDUAN_MIGRASI_HOSTINGER.md** di text editor

### Skenario B: Saya ingin print dokumentasi ke kertas / PDF
→ Gunakan **PANDUAN_MIGRASI_HOSTINGER.html**, buka di browser, tekan Ctrl+P

### Skenario C: Saya sedang proses migrasi & butuh tracking checklist
→ Gunakan **CHECKLIST_MIGRASI_HOSTINGER.txt**, print atau isi digital

### Skenario D: Saya ingin semua file dalam format PDF
→ Buka **PANDUAN_MIGRASI_HOSTINGER.html** di browser → Ctrl+P → Save as PDF

---

## 📋 Isi Dokumentasi

Ketiga file ini berisi panduan lengkap dengan topik berikut:

1. **Persiapan & Persyaratan**
   - Yang dibutuhkan sebelum migrasi
   - Persyaratan server Hostinger

2. **Step 1: Backup Database**
   - Cara backup database lokal
   - 2 opsi: cPanel atau MySQL dump

3. **Step 2: Build NPM & Assets**
   - Mengcompile CSS & JS untuk production
   - Verifikasi build assets

4. **Step 3: Persiapan File Upload**
   - File/folder yang harus di-upload
   - File yang harus dihindari
   - Struktur folder di Hostinger

5. **Step 4: Upload ke Hostinger**
   - 2 opsi: File Manager cPanel atau FTP
   - Tips upload file besar

6. **Step 5: Konfigurasi .env**
   - Membuat & mengisi .env di Hostinger
   - Database configuration
   - APP_KEY generation

7. **Step 6: Konfigurasi cPanel & Database**
   - Set folder permissions
   - Setup public folder sebagai root
   - Restore database backup

8. **Step 7: Verifikasi & Testing**
   - Testing aplikasi running
   - Testing features
   - Common issues & solutions

9. **Troubleshooting**
   - Error messages & solutions
   - Database connection issues
   - Asset loading problems
   - Permission issues

10. **Quick Reference**
    - Commands penting
    - File konfigurasi
    - Folder penting

---

## 🚀 Quick Start

### Langkah-langkah singkat (lihat dokumentasi untuk detail):

1. **Di lokal (mesin Anda):**
   ```bash
   # Backup database
   mysqldump -u root -p database_name > backup_gajipro.sql
   
   # Build assets
   npm install && npm run build
   
   # Generate APP_KEY
   php artisan key:generate
   ```

2. **Upload ke Hostinger:**
   - Login ke cPanel File Manager atau FTP
   - Upload semua file ke `public_html/` (exclude node_modules & vendor)

3. **Setup di cPanel:**
   - Buat database & database user
   - Buat file `.env` dengan konfigurasi yang benar
   - Set folder permissions (755)
   - Point domain ke `public_html/public`
   - Restore database backup

4. **Verifikasi:**
   - Akses domain → aplikasi running?
   - Login & test features
   - Cek error logs jika ada masalah

---

## 📌 Important Notes

### ⚠️ Hostinger Tidak Punya SSH
Hostinger shared hosting tidak memiliki akses SSH, jadi:
- ❌ Tidak bisa menjalankan `php artisan` di server
- ❌ Tidak bisa menjalankan `npm` di server
- ✅ Semua build & compilation harus dilakukan di lokal DULU

### ✅ Pre-requisite
Sebelum start migrasi, pastikan:
- Project sudah berjalan normal di lokal
- `npm run build` sudah dijalankan
- Folder `public/build/` berisi assets yang compiled
- Database backup sudah ada

### 🔐 Security
- Jangan upload `.env` lokal ke server
- Jangan commit `.env` ke git
- Jangan simpan credentials di code comments
- APP_KEY harus di-generate baru untuk setiap environment

---

## 📞 Support & Resources

Jika menemui masalah:

1. **Cek dokumentasi** → Lihat bagian Troubleshooting
2. **Cek error logs:**
   - cPanel → Error Log
   - `storage/logs/laravel.log`
   - Browser DevTools Console
3. **Hubungi support:**
   - Hostinger Support: https://support.hostinger.com/
   - Laravel Docs: https://laravel.com/docs/12
   - Tailwind CSS: https://tailwindcss.com/docs

---

## 📄 Info File Dokumentasi

- **Dibuat:** Oktober 2026
- **Project:** GajiPro (Laravel 12 + Tailwind CSS 4 + Alpine.js)
- **Tech Stack:** PHP 8.3, MySQL, Vite, Tailwind CSS
- **Target:** Hostinger Shared Hosting (tanpa SSH)

---

## 🎯 Checklist Sebelum Mulai

- [ ] Baca dokumentasi minimal bagian "Persiapan & Persyaratan"
- [ ] Siapkan backup database lokal
- [ ] Siapkan credentials Hostinger (cPanel username & password)
- [ ] Siapkan FTP/File Manager Hostinger
- [ ] Jalankan `npm run build` di lokal
- [ ] Generate APP_KEY dengan `php artisan key:generate`
- [ ] Catat informasi penting (database credentials, domain, dll)
- [ ] Siapkan 1-2 jam untuk proses migrasi

---

**Selamat melakukan migrasi! 🚀**

Jika ada pertanyaan, refer ke dokumentasi lengkap di **PANDUAN_MIGRASI_HOSTINGER.md** atau **PANDUAN_MIGRASI_HOSTINGER.html**.
