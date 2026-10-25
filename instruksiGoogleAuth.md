# Instruksi Setup Google OAuth untuk Login/Register Peserta

Dokumen ini menjelaskan cara mengaktifkan **login & register via akun Google** untuk peserta online di aplikasi GajiPro/Panritta.

---

## 📋 Status Implementasi

Aplikasi sudah **100% ready**! Semua sudah ada:

- ✅ **Controller OAuth** (`app/Http/Controllers/Peserta/OAuthController.php`)
- ✅ **Routes** (`peserta.auth.google` & `peserta.auth.google.callback`)
- ✅ **UI Button Google** di halaman login peserta
- ✅ **Auto-register sebagai peserta** jika email belum terdaftar
- ✅ **Auto-verify email** dari Google
- ✅ **Config `services.google`** di `config/services.php`
- ✅ **`.env.example`** dengan `GOOGLE_CLIENT_ID` & `GOOGLE_CLIENT_SECRET`
- ✅ **Tests** (`tests/Feature/Peserta/OAuthTest.php`) - 8 test cases

Yang perlu Anda lakukan: **Setup Google OAuth credentials** di Google Cloud Console, lalu tambahkan ke file `.env`.

---

## 🔧 Step-by-Step Setup

### Step 1: Buat OAuth Credentials di Google Cloud Console

1. **Buka Google Cloud Console**
   - URL: https://console.cloud.google.com/

2. **Buat/Pilih Project**
   - Jika belum punya project: Klik **"Select a project"** di top bar → **"New Project"**
   - Beri nama project, misal: `GajiPro` atau `Panritta`
   - Klik **"Create"**

3. **Aktifkan Google+ API (jika diminta)**
   - Pergi ke **"APIs & Services"** → **"Library"**
   - Cari **"Google+ API"** atau **"People API"**
   - Klik **"Enable"**

4. **Setup OAuth Consent Screen**
   - Pergi ke **"APIs & Services"** → **"OAuth consent screen"**
   - Pilih **"External"** (untuk testing/production umum)
   - Klik **"Create"**
   - Isi form:
     - **App name**: `GajiPro` (atau nama aplikasi Anda)
     - **User support email**: Email Anda
     - **Developer contact information**: Email Anda
   - Klik **"Save and Continue"**
   - Scopes: Skip (default sudah cukup) → **"Save and Continue"**
   - Test users: Tambahkan email Google Anda untuk testing → **"Save and Continue"**

5. **Buat OAuth Client ID**
   - Pergi ke **"APIs & Services"** → **"Credentials"**
   - Klik **"+ Create Credentials"** → **"OAuth client ID"**
   - **Application type**: Pilih **"Web application"**
   - Isi form:
     - **Name**: `GajiPro Local` (atau sesuai keinginan)
     - **Authorized JavaScript origins**:
       ```
       http://127.0.0.1:8000
       http://localhost:8000
       ```
     - **Authorized redirect URIs**:
       ```
       http://127.0.0.1:8000/peserta/auth/google/callback
       http://localhost:8000/peserta/auth/google/callback
       ```
   - Klik **"Create"**

6. **Copy Credentials**
   - Setelah created, akan muncul popup dengan:
     - **Client ID** (format: `xxxxx.apps.googleusercontent.com`)
     - **Client Secret** (format: `GOCSPX-xxxxxxxxxxxxxxxx`)
   - **Copy** keduanya (atau klik **"Download JSON"**)
   - Simpan di tempat aman

---

### Step 2: Tambahkan Credentials ke `.env`

1. Buka file `.env` di root project (`C:\laragon\www\appsku\.env`)

2. Tambahkan di bagian **bawah** file:

   ```env
   # Google OAuth untuk login/register peserta
   GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=GOCSPX-your-client-secret-here
   ```

3. **Ganti** `your-client-id-here` dan `your-client-secret-here` dengan credentials yang Anda copy di Step 1.

4. **Save** file `.env`.

---

### Step 3: Clear Laravel Config Cache

Jalankan command berikut di terminal (di dalam folder project):

```bash
php artisan config:clear
php artisan cache:clear
```

---

### Step 4: Test Login dengan Google

1. **Jalankan server Laravel** (jika belum running):
   ```bash
   php artisan serve
   ```

2. **Buka browser**, akses halaman login peserta:
   ```
   http://127.0.0.1:8000/peserta/login
   ```

3. Scroll ke bawah, akan ada separator **"atau masuk dengan"**, lalu button **Google**.

4. **Klik button Google**:
   - Diarahkan ke halaman login Google
   - Pilih akun Google yang akan digunakan
   - Authorize aplikasi

5. **Setelah authorize**:
   - Kembali ke aplikasi
   - **Jika email belum terdaftar**: Auto-register sebagai peserta, lalu login otomatis
   - **Jika email sudah terdaftar**: Langsung login
   - Diarahkan ke `/peserta/dashboard`

---

## 🔍 Troubleshooting

### Error: "redirect_uri_mismatch"
- **Penyebab**: URL redirect di Google Console tidak sesuai dengan URL di aplikasi.
- **Solusi**: Pastikan **Authorized redirect URIs** di Google Console persis sama dengan:
  ```
  http://127.0.0.1:8000/peserta/auth/google/callback
  ```
  (Perhatikan: `127.0.0.1` vs `localhost`, protocol `http` vs `https`, port, trailing slash)

### Error: "This app isn't verified"
- **Penyebab**: OAuth consent screen masih dalam mode testing.
- **Solusi**:
  - Untuk testing: Klik **"Advanced"** → **"Go to [app name] (unsafe)"** → Lanjutkan
  - Untuk production: Submit app untuk verification di Google Console

### Button Google tidak muncul / Error 404
- **Penyebab**: `GOOGLE_CLIENT_ID` atau `GOOGLE_CLIENT_SECRET` di `.env` kosong.
- **Solusi**: Pastikan kedua variable sudah terisi di `.env`, lalu jalankan `php artisan config:clear`.

### Error: "State OAuth tidak valid"
- **Penyebab**: Session expired atau CSRF issue.
- **Solusi**: 
  - Hapus browser cookies untuk `127.0.0.1`
  - Coba ulang

### Error: "Email ini terdaftar sebagai akun lain"
- **Penyebab**: Email Google sudah terdaftar sebagai akun admin/pengajar (bukan peserta).
- **Solusi**: Gunakan email Google yang berbeda, atau hapus akun lama dari database.

---

## 🚀 Deployment ke Production

Saat deploy ke server production (misal: `https://gajipro.com`):

1. **Update Authorized redirect URIs** di Google Cloud Console:
   - Tambahkan URL production:
     ```
     https://gajipro.com/peserta/auth/google/callback
     ```

2. **Update Authorized JavaScript origins**:
   ```
   https://gajipro.com
   ```

3. **Update `.env` di server production** dengan credentials yang sama (atau bikin OAuth Client ID baru khusus production).

4. **Submit OAuth consent screen untuk verification** (opsional, tapi recommended untuk production):
   - Di Google Console → OAuth consent screen → **"Publish App"**
   - Isi semua detail yang diperlukan
   - Submit untuk review

---

## 📝 Flow Diagram

```
┌─────────────────┐
│  Peserta buka   │
│ /peserta/login  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Klik button    │
│    "Google"     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Redirect ke    │
│ Google OAuth    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  User authorize │
│   di Google     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Google redirect│
│  back ke app    │
└────────┬────────┘
         │
         ▼
    ┌────┴────┐
    │ Email   │
    │ exists? │
    └────┬────┘
         │
    ┌────┴────┐
   YES       NO
    │         │
    ▼         ▼
┌───────┐  ┌──────────────┐
│ Login │  │ Auto-register│
└───┬───┘  │  as peserta  │
    │      └──────┬───────┘
    │             │
    └──────┬──────┘
           │
           ▼
    ┌─────────────┐
    │  Redirect   │
    │ /dashboard  │
    └─────────────┘
```

---

## 🔗 Referensi

- [Google Cloud Console](https://console.cloud.google.com/)
- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Laravel Authentication Documentation](https://laravel.com/docs/authentication)

---

## 📂 File-File Terkait

| File | Deskripsi |
|------|-----------|
| `app/Http/Controllers/Peserta/OAuthController.php` | Controller untuk handle OAuth flow |
| `config/services.php` | Config Google credentials |
| `routes/web.php` (line ~883) | Routes OAuth |
| `resources/views/peserta/auth/login.blade.php` (line ~170) | Button Google di login page |
| `.env.example` | Template env dengan variable OAuth |

---

**Terakhir update**: Setup Google OAuth untuk Peserta Online
