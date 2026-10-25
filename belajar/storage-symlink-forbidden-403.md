# Mengatasi Error 403 Forbidden saat Akses File di `/storage/...`

## 🐛 Gejala Masalah

- Upload gambar via CKEditor / form berhasil (file masuk ke folder `storage/app/public/`)
- Tapi saat akses URL gambar via browser, misalnya:
  ```
  http://127.0.0.1:8000/storage/panritta/soal/editor/JHCowLtZPBjgCLOhTUeTBYpj7r0UJpD61fNCgqYA.png
  ```
  Browser menampilkan error **403 Forbidden**

---

## 🔍 Investigasi

### Cek 1: File benar-benar ter-upload atau tidak?

```powershell
Test-Path storage/app/public/panritta/soal/editor
```

Hasil: `True` → File **sudah ter-upload** ke lokasi yang benar.

### Cek 2: Symlink `public/storage` ada atau tidak?

```powershell
Test-Path public/storage
```

Hasil: `False` → **Symlink belum dibuat!** Ini biang keroknya.

---

## 🧠 Penjelasan Konsep

### Kenapa butuh symlink?

Laravel menyimpan file yang di-upload (via `->store('path', 'public')`) ke:
```
storage/app/public/...
```

Tapi folder `storage/` **berada di luar `public/`** sehingga **tidak bisa diakses langsung dari browser** (karena web server hanya expose folder `public/`).

Solusinya: Buat **symbolic link** (shortcut) dari `public/storage` → `storage/app/public`. Setelah symlink ada:
- File fisik: `storage/app/public/panritta/soal/editor/xxx.png`
- URL browser: `http://127.0.0.1:8000/storage/panritta/soal/editor/xxx.png` ✅

### Flow Request Browser

```
Browser request:  /storage/panritta/soal/editor/xxx.png
                        │
                        ▼
Web server lihat:  public/storage/panritta/soal/editor/xxx.png
                        │
                   (karena ada symlink)
                        │
                        ▼
Actual file:       storage/app/public/panritta/soal/editor/xxx.png
                        │
                        ▼
                   Return file ke browser ✅
```

Kalau symlink **tidak ada**, web server akan reject request → **403 Forbidden**.

---

## ✅ Solusi

Jalankan Artisan command berikut di root project:

```bash
php artisan storage:link
```

Output success:
```
INFO  The [C:\laragon\www\appsku\public\storage] link has been connected to [C:\laragon\www\appsku\storage\app/public].
```

Verifikasi symlink sudah dibuat:

```powershell
Test-Path public/storage
# Hasil: True

Get-ChildItem public/storage
# Harus muncul folder-folder seperti: panritta, dll
```

---

## 🧪 Testing

1. **Refresh browser** di halaman yang upload gambar
2. **Upload gambar baru** via CKEditor atau form
3. **URL gambar sudah bisa diakses** ✅

---

## 🚨 Catatan Penting untuk Deployment

**WAJIB** jalankan `php artisan storage:link` setelah:
- `git clone` di server production/staging
- Setelah fresh deployment
- Setelah hapus folder `public/storage` (jarang terjadi)

### Script Deployment Recommendation

Tambahkan di deployment script (`deploy.sh` atau `composer post-install-cmd`):

```bash
#!/bin/bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Atau di `composer.json`:

```json
"scripts": {
    "post-install-cmd": [
        "@php artisan storage:link"
    ]
}
```

---

## 🔧 Troubleshooting Tambahan

### Masih 403 setelah `php artisan storage:link`?

**Cek permission folder** (biasanya di Linux/Mac, Windows jarang):

```bash
# Linux/Mac
chmod -R 755 storage/
chmod -R 755 public/storage/
```

### Error "The [public/storage] link already exists"

Hapus symlink lama lalu buat ulang:

```bash
rm public/storage
php artisan storage:link
```

Di Windows (PowerShell):
```powershell
Remove-Item public/storage -Force
php artisan storage:link
```

### Symlink tidak bisa dibuat di Windows

Kadang Windows butuh **run PowerShell as Administrator** untuk create symlink.

**Workaround**: Edit `config/filesystems.php`, ubah `disks.public`:

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
],
```

Lalu copy manual folder atau gunakan junction:
```powershell
# Run as Administrator
cmd /c mklink /J "public\storage" "..\storage\app\public"
```

---

## 📚 Referensi

- [Laravel File Storage Documentation](https://laravel.com/docs/filesystem#the-public-disk)
- [Laravel Storage Link Command](https://laravel.com/docs/filesystem#the-public-disk)

---

## 📂 File-File Terkait

| File | Deskripsi |
|------|-----------|
| `config/filesystems.php` | Konfigurasi disk storage (local, public, s3) |
| `storage/app/public/` | Folder actual tempat file di-store |
| `public/storage/` | Symlink ke `storage/app/public/` (dibuat oleh `storage:link`) |

---

**Tanggal**: Investigasi error 403 saat akses file gambar yang di-upload via CKEditor  
**Status**: ✅ Resolved dengan `php artisan storage:link`
