# PRD: Panduan Menambah Modul Baru di GajiPro (Superadmin & Role Lainnya)

**Versi:** 1.0  
**Tanggal:** 2026-11-16  
**Untuk:** Developer yang akan menambah modul/fitur baru  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Struktur Direktori Existing](#struktur-direktori-existing)
3. [Step-by-Step: Menambah Modul di Superadmin](#step-by-step-menambah-modul-di-superadmin)
4. [Step-by-Step: Menambah Modul di Role Lain](#step-by-step-menambah-modul-di-role-lain)
5. [Template Checklist](#template-checklist)
6. [Common Mistakes](#common-mistakes)
7. [Testing & Validation](#testing--validation)

---

## Overview

### Arsitektur Multi-Tenant Role-Based

```
┌─────────────────────────────────────────────────────┐
│ GajiPro Application Architecture                    │
├─────────────────────────────────────────────────────┤
│ Superadmin (Kelola semua data)                      │
│ ├─ Ujian Management (sudah ada)                     │
│ ├─ Peserta Management                              │
│ ├─ Billing & Payment                               │
│ └─ [NEW MODUL] ← Add here                          │
│                                                     │
│ PembuatSoal (Buat & kelola soal & ujian)           │
│ ├─ Dashboard                                        │
│ ├─ Ujian (view only)                               │
│ ├─ Soal (CRUD)                                     │
│ └─ [NEW MODUL] ← Add here                          │
│                                                     │
│ Keuangan (Kelola pembayaran & peserta)             │
│ ├─ Dashboard                                        │
│ ├─ Peserta                                          │
│ ├─ Paket                                            │
│ ├─ Pembayaran                                       │
│ └─ [NEW MODUL] ← Add here                          │
│                                                     │
│ Pengajar (View soal & lihat hasil ujian)           │
│ ├─ Dashboard                                        │
│ ├─ Soal (view only)                                │
│ └─ [NEW MODUL] ← Add here                          │
│                                                     │
│ Peserta (Ujian & langganan)                        │
│ ├─ Dashboard                                        │
│ ├─ Ujian                                            │
│ ├─ Langganan                                        │
│ └─ [NEW MODUL] ← Add here                          │
└─────────────────────────────────────────────────────┘
```

### Hierarchy Role

| Role | Priority | Access | Auth Guard |
|------|----------|--------|-----------|
| Superadmin | 1 (Tertinggi) | Semua data | `superadmin` |
| PembuatSoal | 2 | Soal + Ujian | `pembuat_soal` |
| Keuangan | 3 | Peserta + Payment | `keuangan` |
| Pengajar | 4 | Lihat soal & hasil | `pengajar` |
| Peserta | 5 (Terendah) | Ujian sendiri | `peserta` |

---

## Struktur Direktori Existing

```
app/Http/Controllers/
├── Superadmin/                          ← Semua modul Superadmin
│   ├── UjianController.php
│   ├── SoalController.php
│   ├── HasilUjianController.php
│   ├── UjianPesertaController.php
│   ├── PesertaOfflineController.php
│   ├── PesertaController.php
│   ├── JenisUjianController.php
│   ├── SubJenisUjianController.php
│   ├── SubIndikatorController.php
│   ├── PaketController.php
│   ├── PaketUjianController.php
│   ├── PembayaranController.php        ← Payment mgmt
│   ├── PesertaOfflineKodeAksesController.php
│   └── [NewModulController.php]        ← ADD HERE
│
├── PembuatSoal/                         ← Semua modul PembuatSoal
│   ├── DashboardController.php
│   ├── UjianController.php
│   ├── SoalController.php
│   ├── UjianSoalController.php
│   ├── PesertaController.php
│   ├── ChangePasswordController.php
│   ├── AuthController.php
│   └── [NewModulController.php]        ← ADD HERE
│
├── Keuangan/                            ← Semua modul Keuangan
│   ├── DashboardController.php
│   ├── PesertaController.php
│   ├── PaketController.php
│   ├── PembayaranController.php
│   ├── ChangePasswordController.php
│   ├── AuthController.php
│   └── [NewModulController.php]        ← ADD HERE
│
├── Pengajar/                            ← Semua modul Pengajar
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── SoalController.php
│   ├── ChangePasswordController.php
│   └── [NewModulController.php]        ← ADD HERE
│
├── Peserta/                             ← Semua modul Peserta
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UjianController.php
│   ├── LanggananController.php
│   ├── NotificationController.php
│   ├── OAuthController.php
│   ├── OfflineController.php
│   ├── OfflinePortalController.php
│   └── [NewModulController.php]        ← ADD HERE
│
└── [NewTopLevelModule]/                 ← Jika baru semuanya
    ├── AuthController.php
    ├── DashboardController.php
    └── [NewModulController.php]
```

---

## Step-by-Step: Menambah Modul di Superadmin

### Use Case: Menambah "Laporan Penjualan" di Superadmin

#### STEP 1: Tentukan Requirements

```
Fitur: Laporan Penjualan Ujian
├─ Route: /superadmin/laporan-penjualan
├─ CRUD: READ ONLY (index, show)
├─ Model: LaporanPenjualan (read dari database)
├─ Permission: Superadmin only
├─ Auth: middleware('superadmin')
└─ Features:
   ├─ List laporan penjualan per ujian
   ├─ Filter by tanggal, ujian, sub jenis
   ├─ Export PDF & Excel
   └─ Detail per transaksi
```

#### STEP 2: Buat Model (jika belum ada)

```bash
php artisan make:model LaporanPenjualan
php artisan make:migration create_laporan_penjualan_table
```

**File:** `app/Models/LaporanPenjualan.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPenjualan extends Model
{
    protected $table = 'laporan_penjualan';
    
    protected $casts = [
        'tanggal' => 'date',
        'total_penjualan' => 'decimal:2',
    ];
    
    public function ujian()
    {
        return $this->belongsTo(Ujian::class);
    }
}
```

#### STEP 3: Buat Controller

**File:** `app/Http/Controllers/Superadmin/LaporanPenjualanController.php`

```php
<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\LaporanPenjualan;
use App\Models\Ujian;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanPenjualanController extends Controller
{
    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /superadmin/laporan-penjualan
     * 
     * Keterangan:
     * Menampilkan DAFTAR LAPORAN PENJUALAN dengan filter.
     * Filter: tanggal, ujian, sub jenis, status.
     * Paginasi 20 per halaman.
     * 
     * Form: Tidak ada, parameter GET saja (filter form di view)
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($laporan->toArray());
     */
    public function index(Request $request): View
    {
        $query = LaporanPenjualan::with('ujian')
            ->latest('tanggal');
        
        // Filter berdasarkan parameter GET
        if ($request->filled('ujian_id')) {
            $query->where('ujian_id', $request->ujian_id);
        }
        
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }
        
        $laporan = $query->paginate(20)->withQueryString();
        $ujians = Ujian::orderBy('nama_ujian')->get();
        
        return view('superadmin.laporan-penjualan.index', compact('laporan', 'ujians'));
    }
    
    /**
     * METHOD: show()
     * ===============
     * URL Contoh: GET /superadmin/laporan-penjualan/{laporan}
     * 
     * Keterangan:
     * Detail LAPORAN PENJUALAN: tampil transaksi, breakdown per kategori.
     * 
     * Form: Tidak ada, view only
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd($laporan->toArray());
     */
    public function show(LaporanPenjualan $laporan): View
    {
        $laporan->load('ujian');
        
        return view('superadmin.laporan-penjualan.show', compact('laporan'));
    }
    
    /**
     * METHOD: exportPdf()
     * ===================
     * URL Contoh: GET /superadmin/laporan-penjualan/export/pdf
     * 
     * Keterangan:
     * Download laporan penjualan dalam format PDF.
     * 
     * Form: Tidak ada, button download
     * 
     * Debug Mode (uncomment untuk trace):
     * // \Log::info('Export laporan PDF');
     */
    public function exportPdf()
    {
        $laporan = LaporanPenjualan::with('ujian')->latest('tanggal')->get();
        
        // Gunakan library PDF (misal Barryvdh\DomPDF)
        // $pdf = PDF::loadView('superadmin.laporan-penjualan.pdf', compact('laporan'));
        // return $pdf->download('laporan-penjualan.pdf');
        
        // Untuk sekarang, placeholder
        return response()->json(['message' => 'Export PDF'], 501);
    }
}
```

#### STEP 4: Buat Routes

**File:** `routes/web.php` (Superadmin section)

Cari section Superadmin, tambahkan di bawah ujian management:

```php
// Dalam Route::middleware(['auth', 'superadmin'])->group(function () { ... })

// laporan penjualan management
Route::get('laporan-penjualan', [LaporanPenjualanController::class, 'index'])->name('laporan-penjualan.index');
Route::get('laporan-penjualan/{laporan}', [LaporanPenjualanController::class, 'show'])->name('laporan-penjualan.show');
Route::get('laporan-penjualan/export/pdf', [LaporanPenjualanController::class, 'exportPdf'])->name('laporan-penjualan.export.pdf');
Route::get('laporan-penjualan/export/excel', [LaporanPenjualanController::class, 'exportExcel'])->name('laporan-penjualan.export.excel');
```

**Catatan Route:**
- Always gunakan prefix route name (misal `laporan-penjualan.index` bukan `index`)
- Method resource bisa dipakai untuk CRUD standard: `Route::resource('laporan-penjualan', LaporanPenjualanController::class)`

#### STEP 5: Buat View

**File:** `resources/views/superadmin/laporan-penjualan/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Laporan Penjualan</h1>
        <div class="space-x-2">
            <a href="{{ route('superadmin.laporan-penjualan.export.pdf') }}" class="btn btn-primary">
                Export PDF
            </a>
            <a href="{{ route('superadmin.laporan-penjualan.export.excel') }}" class="btn btn-primary">
                Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" class="mb-6">
        <div class="grid grid-cols-3 gap-4">
            <select name="ujian_id" class="form-control">
                <option value="">Pilih Ujian...</option>
                @foreach($ujians as $ujian)
                    <option value="{{ $ujian->id }}" {{ request('ujian_id') == $ujian->id ? 'selected' : '' }}>
                        {{ $ujian->nama_ujian }}
                    </option>
                @endforeach
            </select>
            <input type="month" name="bulan" class="form-control" value="{{ request('bulan') }}">
            <button type="submit" class="btn btn-secondary">Filter</button>
        </div>
    </form>

    <!-- Tabel Laporan -->
    <div class="overflow-x-auto">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Ujian</th>
                    <th>Total Penjualan</th>
                    <th>Jumlah Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                    <tr>
                        <td>{{ $item->tanggal->format('d-m-Y') }}</td>
                        <td>{{ $item->ujian->nama_ujian }}</td>
                        <td>Rp {{ number_format($item->total_penjualan, 2) }}</td>
                        <td>{{ $item->jumlah_transaksi }}</td>
                        <td>
                            <a href="{{ route('superadmin.laporan-penjualan.show', $item) }}" class="btn btn-sm btn-info">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    {{ $laporan->links() }}
</div>
@endsection
```

#### STEP 6: Add to Navigation

Edit file yang menampilkan navigation/sidebar (biasanya di layout):

```blade
<!-- Dalam sidebar atau navigation menu -->
<a href="{{ route('superadmin.laporan-penjualan.index') }}" class="nav-link">
    <i class="fas fa-chart-bar"></i> Laporan Penjualan
</a>
```

#### STEP 7: Add Import di Controller

```php
use App\Http\Controllers\Superadmin\LaporanPenjualanController; // di atas routes/web.php
```

---

## Step-by-Step: Menambah Modul di Role Lain

### Template untuk Setiap Role

#### Untuk PembuatSoal:

```
Route prefix: 'pembuat-soal'
Route name prefix: 'pembuat-soal.'
Auth middleware: middleware(['auth', 'pembuat_soal'])
Auth guard: 'web' (default)
Permission: Lihat soal & ujian milik user sendiri
```

#### Untuk Keuangan:

```
Route prefix: 'keuangan'
Route name prefix: 'keuangan.'
Auth middleware: middleware(['auth', 'keuangan'])
Auth guard: 'web' (default)
Permission: Lihat peserta & pembayaran
```

#### Untuk Pengajar:

```
Route prefix: 'pengajar'
Route name prefix: 'pengajar.'
Auth middleware: middleware(['auth', 'pengajar'])
Auth guard: 'web' (default)
Permission: Lihat soal & hasil ujian (read-only)
```

#### Untuk Peserta:

```
Route prefix: 'peserta'
Route name prefix: 'peserta.'
Auth middleware: middleware(['auth', 'peserta'])
Auth guard: 'web' (default)
Permission: Lihat ujian & langganan milik user sendiri
```

### Example: Menambah Modul di Keuangan

**Use Case:** Dashboard Keuangan dengan analytics

**Controller:** `app/Http/Controllers/Keuangan/AnalyticsController.php`

```php
<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\View\View;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * METHOD: index()
     * ===============
     * URL Contoh: GET /keuangan/analytics
     * 
     * Keterangan:
     * Dashboard analytics keuangan: penjualan hari ini, bulan ini, chart.
     * 
     * Form: Tidak ada
     * 
     * Debug Mode (uncomment untuk trace):
     * // dd(['hari_ini' => $hariIni, 'bulan_ini' => $bulanIni]);
     */
    public function index(): View
    {
        $hariIni = Pembayaran::whereDate('created_at', today())
            ->sum('jumlah');
        
        $bulanIni = Pembayaran::whereMonth('created_at', now()->month)
            ->sum('jumlah');
        
        return view('keuangan.analytics.index', compact('hariIni', 'bulanIni'));
    }
}
```

**Route (routes/web.php):**

```php
Route::prefix('keuangan')->name('keuangan.')->group(function () {
    // ... existing routes ...
    
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});
```

---

## Template Checklist

Ketika menambah modul BARU, ikuti checklist ini:

### ☐ Backend

- [ ] Create Model (jika perlu): `php artisan make:model NameModel`
- [ ] Create Migration (jika perlu): `php artisan make:migration create_name_table`
- [ ] Create Controller: `php artisan make:controller Role/NameController`
- [ ] Add methods dengan dokumentasi:
  - [ ] Method 1: index() [LIST]
  - [ ] Method 2: create() [FORM TAMBAH] - optional untuk read-only
  - [ ] Method 3: store() [SAVE] - optional untuk read-only
  - [ ] Method 4: edit() [FORM EDIT] - optional untuk read-only
  - [ ] Method 5: update() [SAVE EDIT] - optional untuk read-only
  - [ ] Method 6: show() [DETAIL]
  - [ ] Method 7: destroy() [DELETE] - optional untuk read-only
- [ ] Add inline comment di setiap method:
  - URL Contoh
  - Keterangan
  - Form (jika ada)
  - Debug mode
- [ ] Add Route: `routes/web.php`
  - [ ] Use correct prefix & name
  - [ ] Use correct auth middleware
  - [ ] All methods mapped correctly

### ☐ Frontend

- [ ] Create view folder: `resources/views/role/module-name/`
- [ ] Create views:
  - [ ] index.blade.php [LIST]
  - [ ] create.blade.php [FORM] - optional
  - [ ] edit.blade.php [FORM] - optional
  - [ ] show.blade.php [DETAIL]
- [ ] Add to navigation/sidebar
- [ ] Form action points to correct route
- [ ] Form method is correct (GET/POST/PUT/DELETE)

### ☐ Database

- [ ] Migration created & contains all fields
- [ ] Relationships defined in Model
- [ ] Run migration: `php artisan migrate`

### ☐ Testing

- [ ] Manual test: navigasi URL check redirect
- [ ] Manual test: CREATE (jika ada)
- [ ] Manual test: READ (list & detail)
- [ ] Manual test: UPDATE (jika ada)
- [ ] Manual test: DELETE (jika ada)
- [ ] Run test suite: `php artisan test --filter="NameModule"`

### ☐ Code Quality

- [ ] Run linter: `vendor/bin/pint --dirty --format agent`
- [ ] No unused imports
- [ ] Type hints lengkap
- [ ] Return types ditulis

---

## Common Mistakes

### ❌ MISTAKE 1: Lupa middleware

```php
// SALAH
Route::get('module', [ModuleController::class, 'index'])->name('module.index');

// BENAR
Route::get('module', [ModuleController::class, 'index'])
    ->middleware(['auth', 'superadmin'])
    ->name('module.index');
```

### ❌ MISTAKE 2: Route name tidak konsisten

```php
// SALAH (nama tidak sesuai prefix)
Route::get('laporan', [...]) ->name('index'); // Akan jadi route('index')

// BENAR
Route::get('laporan', [...]) ->name('laporan.index'); // Akan jadi route('laporan.index')
```

### ❌ MISTAKE 3: Lupa add import di routes

```php
// SALAH
Route::get('module', [ModuleController::class, ...]); // ERROR: class not found

// BENAR (di atas routes/web.php)
use App\Http\Controllers\Superadmin\ModuleController;
Route::get('module', [ModuleController::class, ...]);
```

### ❌ MISTAKE 4: View path tidak match route

```php
// Route: /superadmin/laporan
// SALAH view: resources/views/laporan/index.blade.php
// BENAR view: resources/views/superadmin/laporan/index.blade.php
```

### ❌ MISTAKE 5: HTML form submit ke route yang salah

```blade
<!-- SALAH -->
<form action="laporan" method="POST">
    <!-- kirim ke /superadmin/laporan tapi method POST -->
    <!-- tapi route hanya accept GET -->
</form>

<!-- BENAR -->
<form action="{{ route('superadmin.laporan.store') }}" method="POST">
    @csrf
    <!-- sesuaikan dengan route yang accept POST -->
</form>
```

### ❌ MISTAKE 6: Lupa CSRF token di form

```blade
<!-- SALAH -->
<form action="{{ route('superadmin.laporan.store') }}" method="POST">
    <input type="text" name="nama">
    <button>Simpan</button>
</form>

<!-- BENAR -->
<form action="{{ route('superadmin.laporan.store') }}" method="POST">
    @csrf
    <input type="text" name="nama">
    <button>Simpan</button>
</form>
```

### ❌ MISTAKE 7: Method documentation incomplete

```php
// SALAH
public function index()
{
    return view('laporan.index');
}

// BENAR
/**
 * METHOD: index()
 * ===============
 * URL Contoh: GET /superadmin/laporan
 * 
 * Keterangan:
 * Menampilkan DAFTAR LAPORAN dengan filter.
 * 
 * Form: Tidak ada, parameter GET saja
 * 
 * Debug Mode (uncomment untuk trace):
 * // dd($laporan->toArray());
 */
public function index(Request $request): View
{
    $laporan = Laporan::paginate(20);
    return view('superadmin.laporan.index', compact('laporan'));
}
```

---

## Testing & Validation

### Manual Test Checklist

```bash
# 1. Clear cache & config
php artisan config:clear
php artisan route:clear

# 2. Run migration jika ada
php artisan migrate

# 3. Test route exists
php artisan route:list | grep laporan-penjualan

# 4. Manual browser test
# Navigate: http://localhost:8000/superadmin/laporan-penjualan
# Expected: Daftar laporan tampil atau flash message

# 5. Test form submit (jika ada)
# Fill form, click submit, check if data saved

# 6. Test export
# Click export PDF, check if download

# 7. Run tests
php artisan test --filter="LaporanPenjualan"

# 8. Lint
vendor/bin/pint --dirty --format agent
```

### Automated Test Example

**File:** `tests/Feature/Superadmin/LaporanPenjualanTest.php`

```php
<?php

namespace Tests\Feature\Superadmin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_superadmin_dapat_akses_laporan_penjualan()
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        
        $response = $this->actingAs($superadmin)
            ->get('/superadmin/laporan-penjualan');
        
        $response->assertStatus(200);
        $response->assertViewIs('superadmin.laporan-penjualan.index');
    }
    
    public function test_non_superadmin_tidak_dapat_akses()
    {
        $user = User::factory()->create(['role' => 'peserta']);
        
        $response = $this->actingAs($user)
            ->get('/superadmin/laporan-penjualan');
        
        $response->assertStatus(403); // Forbidden
    }
}
```

---

## Architecture Decision Records (ADR)

### Pilihan: Buat Role Baru vs Pakai Role Existing?

**Jika fitur untuk:** → **Gunakan Role:**
- Manajemen ujian, soal, konfigurasi → Superadmin
- Buat & kelola soal → PembuatSoal
- Kelola pembayaran & peserta → Keuangan
- Lihat soal & hasil (read-only) → Pengajar
- Ujian & langganan → Peserta

**Buat role baru HANYA jika:**
- Fitur tidak cocok ke role existing
- Need custom permission set
- Isolated modul dengan auth sendiri

---

## Reference Links

- [URL_TO_CONTROLLER_MAPPING.md](./URL_TO_CONTROLLER_MAPPING.md) - Cari route existing
- [CONTROLLER_UJIAN_SUMMARY.md](./CONTROLLER_UJIAN_SUMMARY.md) - Contoh controller pattern
- [FLOW_DATA_UJIAN.md](./FLOW_DATA_UJIAN.md) - Contoh database design

---

**Versi:** 1.0  
**Last Updated:** 2026-11-16  
**Author:** OpenCode Documentation
