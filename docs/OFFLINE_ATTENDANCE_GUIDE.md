# Panduan Fitur Offline Participant Attendance

## Ringkasan Fitur

Fitur **Offline Participant Attendance** memungkinkan:
1. **Superadmin** untuk mengelola kehadiran peserta offline secara real-time
2. **Monitoring pengawas** untuk melihat status peserta yang sedang login/mengerjakan
3. **Antisipasi multi-device** untuk mencegah peserta login dari perangkat berbeda secara bersamaan

---

## Arsitektur & Data Model

### Tabel-tabel yang Terlibat

#### 1. `panritta_peserta_offline`
Master data peserta offline per ujian.

```
- id
- ujian_id (FK -> panritta_ujian)
- nomor_peserta (varchar 50) - identifier login
- nama_peserta (varchar 255)
- kode_akses (hashed) - one-time password
- ujian_peserta_id (nullable FK -> panritta_ujian_peserta)
- created_at, updated_at
```

#### 2. `panritta_peserta_offline_kehadiran` (Pivot)
Status kehadiran per peserta per ujian.

```
- id
- peserta_offline_id (FK -> panritta_peserta_offline)
- ujian_id (FK -> panritta_ujian)
- status_kehadiran enum('hadir', 'tidak_hadir') - default 'tidak_hadir'
- created_at, updated_at
- unique(peserta_offline_id, ujian_id)
```

#### 3. `offline_participant_sessions` (NEW - untuk monitoring)
Session tracking untuk multi-device prevention & monitoring pengawas.

```
- id
- peserta_offline_id (FK -> panritta_peserta_offline)
- ujian_id (FK -> panritta_ujian)
- session_token (unique) - untuk identify session unik
- status enum('logged_in', 'sedang_ujian', 'selesai', 'logout') 
- device_info (json) - IP, user agent, device id
- login_at (timestamp)
- logout_at (nullable timestamp)
- created_at, updated_at
```

---

## Alur Kerja

### 1. Peserta Offline Login

**Route:** `POST /peserta/ujian-offline/login`

**Flow:**
```
1. Peserta submit nomor_peserta + kode_akses
2. System validasi credentials
3. Cek apakah peserta sudah login di device lain
   - Jika YA → tolak dengan pesan "Sudah login di perangkat lain"
   - Jika TIDAK → lanjutkan
4. Create session baru di `offline_participant_sessions`
   - Capture device_info (IP, User-Agent)
   - Set status = 'logged_in'
   - Generate unique session_token
5. Set session keys:
   - offline_peserta_id
   - offline_session_token (untuk validasi pada request berikutnya)
6. Redirect ke daftar ujian
```

### 2. Peserta Lihat Daftar Ujian

**Route:** `GET /peserta/offline/daftar`

**Flow:**
```
1. Load ujian yang punya kehadiran record untuk peserta ini
2. Untuk setiap ujian, tampilkan:
   - Nama ujian
   - Status kehadiran (hadir/tidak_hadir) - dari tabel kehadiran
   - Durasi & jumlah soal
   - Tombol "Ikuti Ujian" (enabled jika hadir + aktif)
```

### 3. Peserta Mulai Ujian

**Route:** `POST /peserta/ujian/{ujian}/offline/mulai`

**Flow:**
```
1. Validasi peserta sudah login (session keys ada)
2. Cek session masih valid (tidak force-logout di device lain)
3. Cek attendance masih 'hadir'
4. Cek ujian status = 'aktif'
5. Create attempt (UjianPeserta)
6. Update session status = 'sedang_ujian'
7. Set session keys:
   - offline_ujian_id
   - offline_attempt_id
8. Redirect ke halaman ujian
```

### 4. Peserta Simpan Jawaban & Submit

**Validasi di StoreJawabanRequest:**
```
1. Cek session keys lengkap
2. Cek session_token valid (tidak di-logout dari device lain)
3. Cek attendance masih 'hadir'
4. Cek batas_waktu belum passed
5. Simpan jawaban
```

**Submit (POST /peserta/ujian/{ujian}/submit):**
```
1. Cek attendance masih 'hadir'
2. Finalize scoring
3. Update session status = 'selesai'
4. Clear session keys
```

---

## Fitur Monitoring Pengawas (Superadmin)

### Halaman Monitoring Real-time

**Route:** `GET /superadmin/ujian/{ujian}/monitoring`

**Menampilkan:**
```
- Jumlah peserta yang login (status = logged_in atau sedang_ujian)
- Jumlah peserta yang sedang mengerjakan (status = sedang_ujian)
- Jumlah peserta yang sudah selesai (status = selesai)

Tabel live peserta:
┌─────┬────────────────────┬──────────────┬─────────────────┬──────────────┐
│ No  │ Nomor Peserta      │ Nama Peserta │ Status Kehadiran │ Status Ujian │
├─────┼────────────────────┼──────────────┼─────────────────┼──────────────┤
│ 1   │ P001               │ John Doe     │ ● Hadir         │ ● Online     │
│ 2   │ P002               │ Jane Smith   │ ● Hadir         │ ○ Menunggu   │
│ 3   │ P003               │ Bob Wilson   │ ○ Tidak Hadir   │ ○ Offline    │
└─────┴────────────────────┴──────────────┴─────────────────┴──────────────┘

Legend:
● = Online (hijau)
○ = Offline (abu-abu)
```

### API Endpoint untuk Real-time Updates (AJAX/WebSocket)

**Route:** `GET /superadmin/ujian/{ujian}/monitoring/live`

**Response:**
```json
{
  "ujian_id": 1,
  "total_peserta": 10,
  "logged_in": 7,
  "sedang_ujian": 5,
  "selesai": 2,
  "participants": [
    {
      "peserta_offline_id": 1,
      "nomor_peserta": "P001",
      "nama_peserta": "John Doe",
      "status_kehadiran": "hadir",
      "status_ujian": "sedang_ujian",
      "login_at": "2026-09-05 16:45:30",
      "ip_address": "192.168.1.100",
      "last_activity": "2026-09-05 16:52:15"
    }
  ]
}
```

---

## Anti Multi-Device Login

### Mekanisme

#### 1. Session Token Validation
```php
// Di middleware OfflineParticipantAuth
$sessionToken = $request->session()->get('offline_session_token');
$session = OfflineParticipantSession::where('session_token', $sessionToken)
    ->where('status', '!=', 'logout')
    ->first();

if (!$session) {
    abort(403, 'Sesi Anda telah berakhir. Silakan login kembali.');
}
```

#### 2. Logout Force pada Device Lain
```php
// Saat peserta login di device baru
$existingSessions = OfflineParticipantSession::where('peserta_offline_id', $peserta->id)
    ->where('ujian_id', $ujian->id)
    ->where('status', '!=', 'logout')
    ->get();

foreach ($existingSessions as $session) {
    $session->update([
        'status' => 'logout',
        'logout_at' => now(),
        'logout_reason' => 'Login dari perangkat lain'
    ]);
}
```

#### 3. Deteksi Multi-Device pada Request
```php
// Di setiap request, cek apakah IP berubah
$sessionData = OfflineParticipantSession::find($sessionId);
$currentIP = $request->ip();

if ($sessionData->device_info['ip'] != $currentIP) {
    // IP berubah = perangkat berbeda
    $sessionData->update(['status' => 'logout']);
    abort(403, 'Perangkat yang Anda gunakan berbeda. Silakan login kembali.');
}
```

---

## Implementasi Step-by-Step

### Step 1: Create Model & Migration
```bash
php artisan make:model OfflineParticipantSession -mf
```

**Migration (create_offline_participant_sessions_table):**
```php
Schema::create('offline_participant_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('peserta_offline_id')
        ->constrained('panritta_peserta_offline')
        ->cascadeOnDelete();
    $table->foreignId('ujian_id')
        ->constrained('panritta_ujian')
        ->cascadeOnDelete();
    $table->string('session_token')->unique();
    $table->enum('status', ['logged_in', 'sedang_ujian', 'selesai', 'logout'])
        ->default('logged_in');
    $table->json('device_info')->nullable(); // {ip, user_agent, device_id}
    $table->timestamp('login_at')->nullable();
    $table->timestamp('logout_at')->nullable();
    $table->text('logout_reason')->nullable();
    $table->timestamps();
    
    $table->index(['peserta_offline_id', 'ujian_id']);
});
```

### Step 2: Update OfflinePortalController::login()
```php
public function login(LoginPesertaOfflineRequest $request)
{
    // ... existing validation ...
    
    // Force logout sessions dari device lain
    OfflineParticipantSession::where('peserta_offline_id', $peserta->id)
        ->where('status', '!=', 'logout')
        ->update([
            'status' => 'logout',
            'logout_at' => now(),
            'logout_reason' => 'Login dari perangkat lain'
        ]);
    
    // Create new session
    $sessionToken = Str::random(64);
    $session = OfflineParticipantSession::create([
        'peserta_offline_id' => $peserta->id,
        'ujian_id' => null, // belum di ujian spesifik
        'session_token' => $sessionToken,
        'status' => 'logged_in',
        'device_info' => [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_id' => $request->header('X-Device-ID') // client-provided
        ],
        'login_at' => now()
    ]);
    
    $request->session()->put('offline_session_token', $sessionToken);
    $request->session()->put('offline_peserta_id', $peserta->id);
    
    return redirect()->route('peserta.offline.exams');
}
```

### Step 3: Update Middleware OfflineParticipantAuth
```php
public function handle(Request $request, Closure $next)
{
    // Validasi session token
    $sessionToken = $request->session()->get('offline_session_token');
    if (!$sessionToken) {
        abort(403, 'Sesi tidak valid.');
    }
    
    $session = OfflineParticipantSession::where('session_token', $sessionToken)
        ->where('status', '!=', 'logout')
        ->first();
    
    if (!$session || $session->status === 'logout') {
        $request->session()->forget(['offline_peserta_id', 'offline_session_token']);
        abort(403, 'Sesi Anda telah berakhir. Silakan login kembali.');
    }
    
    // Cek IP (anti multi-device)
    if ($session->device_info['ip'] !== $request->ip()) {
        $session->update(['status' => 'logout', 'logout_at' => now()]);
        abort(403, 'Perangkat berbeda terdeteksi. Silakan login kembali.');
    }
    
    // Lanjutkan ke next middleware
    return $next($request);
}
```

### Step 4: Create Monitoring Controller
```bash
php artisan make:controller Superadmin/OfflineMonitoringController
```

**Methods:**
- `index(Ujian $ujian)` — Halaman monitoring
- `live(Ujian $ujian)` — API endpoint real-time
- `forceLogout(OfflineParticipantSession $session)` — Admin force logout

### Step 5: Add Routes
```php
// Monitoring routes (superadmin only)
Route::get('ujian/{ujian}/monitoring', [OfflineMonitoringController::class, 'index'])
    ->name('ujian.monitoring.index');
Route::get('ujian/{ujian}/monitoring/live', [OfflineMonitoringController::class, 'live'])
    ->name('ujian.monitoring.live');
Route::post('ujian/{ujian}/participant-session/{session}/force-logout', [OfflineMonitoringController::class, 'forceLogout'])
    ->name('ujian.monitoring.force-logout');
```

---

## UI/UX Monitoring Dashboard

### Halaman Monitoring (`resources/views/superadmin/ujian/monitoring.blade.php`)

**Features:**
- Live counter (peserta online, sedang ujian, selesai)
- Real-time table dengan status online/offline (hijau/abu-abu)
- Last activity timestamp
- IP address displayed
- Action button: "Force Logout" jika terdeteksi suspicious
- Auto-refresh setiap 5 detik (AJAX)

---

## Testing Strategy

### Tests untuk Multi-Device Prevention
```php
it('force logout previous session when login from new device')
it('reject request from different IP address')
it('accept request from same IP address')
it('clear session on logout')
```

### Tests untuk Monitoring
```php
it('show correct participant count on monitoring page')
it('live endpoint returns correct status for each participant')
it('force logout works correctly')
```

---

## Security Considerations

1. **Session Token**: 64-char random string, unique, stored in DB
2. **IP Tracking**: Prevent device switching (not foolproof but good deterrent)
3. **Device ID**: Optional header `X-Device-ID` untuk tracking perangkat
4. **Logout Reason**: Log why session was terminated (audit trail)
5. **Rate Limiting**: Limit login attempts (sudah ada di Laravel)

---

## Deployment Checklist

- [ ] Migration `offline_participant_sessions` created & tested
- [ ] Model `OfflineParticipantSession` dengan relationships
- [ ] Update `OfflinePortalController::login()` dengan session management
- [ ] Update `OfflineParticipantAuth` middleware dengan token & IP validation
- [ ] Create `OfflineMonitoringController` dengan live endpoint
- [ ] Add monitoring routes
- [ ] Create monitoring dashboard view
- [ ] Add tests untuk multi-device & monitoring
- [ ] Run full test suite
- [ ] Deploy & monitor

---

## Known Limitations & Future Improvements

1. **IP-based detection** tidak 100% reliable (proxy, dynamic IP)
   - Future: fingerprinting browser atau device ID dari client
   
2. **Real-time updates** menggunakan polling (AJAX setiap 5 detik)
   - Future: WebSocket untuk true real-time updates

3. **Logout reason** hanya teks sederhana
   - Future: structured enum dengan detailed tracking

---

## FAQ

**Q: Peserta login di 2 perangkat, apa yang terjadi?**
A: Perangkat pertama akan di-force logout. Saat user mencoba berinteraksi, akan muncul pesan "Sesi berakhir, login kembali".

**Q: IP berubah karena WiFi -> mobile hotspot, apa yang terjadi?**
A: Peserta akan di-force logout untuk keamanan. Mereka harus login ulang dari perangkat mobile.

**Q: Bagaimana jika peserta lupa logout?**
A: Session tetap aktif sampai admin force logout atau session expired (perlu set expiry time di future).

**Q: Monitoring dashboard update real-time?**
A: Polling setiap 5 detik via AJAX. WebSocket bisa di-implement untuk true real-time.

