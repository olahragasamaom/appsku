# Panduan Membuat Modul CRUD dari Nol

> Panduan ini dibuat untuk pemula Laravel yang ingin membelajari alur CRUD di GajiPro.
> Menggunakan bahasa Indonesia agar mudah dipahami.

---

## Daftar Isi

1. [Persiapan & Konsep Dasar](#1-persiapan--konsep-dasar)
2. [Langkah 1: Database (Migration)](#2-langkah-1-database-migration)
3. [Langkah 2: Model](#3-langkah-2-model)
4. [Langkah 3: Factory (Dummy Data)](#4-langkah-3-factory-dummy-data)
5. [Langkah 4: Form Request (Validasi)](#5-langkah-4-form-request-validasi)
6. [Langkah 5: Controller](#6-langkah-5-controller)
7. [Langkah 6: Routes](#7-langkah-6-routes)
8. [Langkah 7: Views (Blade)](#8-langkah-7-views-blade)
9. [Langkah 8: Testing](#9-langkah-8-testing)
10. [Checklist Akhir](#10-checklist-akhir)

---

## 1. Persiapan & Konsep Dasar

### Apa itu CRUD?

CRUD = **Create, Read, Update, Delete** → operasi dasar untuk mengelola data.

### Alur Request di Laravel (dari URL sampai tampilan)

```
Browser (URL) 
   ↓
Route (routes/web.php) — mencocokkan URL dengan Controller
   ↓
Middleware (opsional) — cek akses (superadmin/peserta/dll)
   ↓
Form Request (validasi input) — cek apakah data yang dikirim valid
   ↓
Controller — logika bisnis (ambil/simpan/ubah/hapus data)
   ↓
Model — wakil tabel database (Eloquent ORM)
   ↓
View (Blade) — tampilkan HTML ke browser
```

### Contoh Kasus

Kita akan buat modul **"Status Karyawan"** (mis. "Tetap", "Kontrak", "Magang").

Tabel: `employee_statuses`
- `id` (primary key)
- `name` (nama status)
- `description` (keterangan)
- `is_active` (boolean)
- `timestamps` (created_at, updated_at)

---

## 2. Langkah 1: Database (Migration)

Migration adalah "blueprint" tabel. Laravel mencatat versi database lewat migration.

### Perintah Artisan

```bash
php artisan make:migration create_employee_statuses_table
```

File baru akan muncul di `database/migrations/2026_xx_xx_xxxxxx_create_employee_statuses_table.php`.

### Isi Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_statuses');
    }
};
```

**Penjelasan:**
- `id()` → primary key auto increment
- `string('name', 100)` → VARCHAR(100)
- `nullable()` → boleh kosong
- `default(true)` → nilai default
- `timestamps()` → menambahkan `created_at` & `updated_at` otomatis

### Jalankan Migration

```bash
php artisan migrate
```

Tabel `employee_statuses` sekarang ada di database.

---

## 3. Langkah 2: Model

Model adalah "wakil" tabel dalam bentuk objek PHP. Lewat model kita bisa:
- Baca data: `EmployeeStatus::all()`
- Simpan: `EmployeeStatus::create([...])`
- Ubah: `$model->update([...])`
- Hapus: `$model->delete()`

### Perintah Artisan

```bash
php artisan make:model EmployeeStatus
```

File baru: `app/Models/EmployeeStatus.php`

### Isi Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * MODEL: EmployeeStatus
 * Wakil tabel employee_statuses di database.
 */
class EmployeeStatus extends Model
{
    /**
     * HasFactory: supaya bisa bikin dummy data via factory.
     */
    use HasFactory;

    /**
     * Nama tabel (optional, Laravel bisa nebak sendiri tapi lebih aman ditulis).
     */
    protected $table = 'employee_statuses';

    /**
     * Kolom yang BOLEH diisi massal (mass assignment).
     * Kolom yang tidak ada di sini tidak bisa diisi via create()/update().
     * Ini pengaman agar kolom sensitif (id, timestamps) tidak bisa diisi sembarangan.
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    /**
     * Konversi tipe otomatis saat data dibaca dari database.
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // RELASI (jika punya):
    // Contoh: jika EmployeeStatus punya banyak Employee,
    // public function employees(): HasMany
    // {
    //     return $this->hasMany(Employee::class, 'status_id');
    // }
}
```

---

## 4. Langkah 3: Factory (Dummy Data)

Factory dipakai untuk:
- **Testing**: buat data dummy di test tanpa insert manual
- **Seeder**: isi database awal (mis. status default: Tetap, Kontrak, Magang)

### Perintah Artisan

```bash
php artisan make:factory EmployeeStatusFactory
```

File baru: `database/factories/EmployeeStatusFactory.php`

### Isi Factory

```php
<?php

namespace Database\Factories;

use App\Models\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeStatusFactory extends Factory
{
    protected $model = EmployeeStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    // State: variasi data (optional)
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
```

**Cara pakai di test atau tinker:**

```php
// Buat 1 record dummy
$status = EmployeeStatus::factory()->create();

// Buat 5 record
EmployeeStatus::factory()->count(5)->create();

// Buat dengan state 'inactive'
EmployeeStatus::factory()->inactive()->create();
```

---

## 5. Langkah 4: Form Request (Validasi)

Form Request = kelas khusus untuk VALIDASI input form SEBELUM masuk ke controller.

### Perintah Artisan

```bash
php artisan make:request EmployeeStatusRequest
```

File baru: `app/Http/Requests/EmployeeStatusRequest.php`

### Isi Request

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FORM REQUEST: EmployeeStatusRequest
 * Validasi input untuk tambah/edit Status Karyawan.
 */
class EmployeeStatusRequest extends FormRequest
{
    /**
     * Apakah user boleh melakukan aksi ini?
     * return true = semua yang lolos middleware boleh lanjut.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi per kolom.
     */
    public function rules(): array
    {
        // Ambil id dari URL (saat edit). Null saat create.
        $statusId = $this->route('employee_status')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // unique: tidak boleh duplikat, kecuali record ini sendiri (penting saat edit)
                Rule::unique('employee_statuses', 'name')->ignore($statusId),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Pesan error kustom (Bahasa Indonesia).
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama status wajib diisi.',
            'name.max' => 'Nama status maksimal 100 karakter.',
            'name.unique' => 'Nama status sudah digunakan.',
        ];
    }
}
```

**Cara kerja:**
- Controller pakai `EmployeeStatusRequest` sebagai parameter
- Laravel otomatis validasi SEBELUM isi method controller dijalankan
- Jika gagal → balik ke form dengan pesan error
- Jika lolos → data bisa diambil dengan `$request->validated()`

---

## 6. Langkah 5: Controller

Controller = jembatan antara route, model, dan view. Ini tempat logika bisnis.

### Perintah Artisan

```bash
php artisan make:controller Superadmin/EmployeeStatusController
```

File baru: `app/Http/Controllers/Superadmin/EmployeeStatusController.php`

### Isi Controller (CRUD Lengkap)

```php
<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeStatusRequest;
use App\Models\EmployeeStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * CONTROLLER: EmployeeStatusController
 * Menangani CRUD Status Karyawan untuk superadmin.
 *
 * PETA ROUTE -> METHOD:
 *   GET    /superadmin/employee-statuses              -> index()
 *   POST   /superadmin/employee-statuses              -> store()
 *   PUT    /superadmin/employee-statuses/{status}     -> update()
 *   DELETE /superadmin/employee-statuses/{status}     -> destroy()
 */
class EmployeeStatusController extends Controller
{
    /**
     * READ: tampilkan daftar.
     * Ambil semua data, urutkan, dan pecah jadi halaman (pagination).
     */
    public function index(): View
    {
        $statuses = EmployeeStatus::orderBy('name')->paginate(15);

        return view('superadmin.employee-status.index', compact('statuses'));
    }

    /**
     * CREATE: simpan data baru.
     * Parameter EmployeeStatusRequest otomatis menjalankan validasi.
     * Jika gagal validasi, method ini TIDAK dijalankan.
     */
    public function store(EmployeeStatusRequest $request): RedirectResponse
    {
        EmployeeStatus::create($request->validated());

        return redirect()->route('superadmin.employee-statuses.index')
            ->with('success', 'Status karyawan berhasil dibuat.');
    }

    /**
     * UPDATE: ubah data.
     * $employeeStatus otomatis dicari dari URL (route model binding).
     */
    public function update(EmployeeStatusRequest $request, EmployeeStatus $employeeStatus): RedirectResponse
    {
        $employeeStatus->update($request->validated());

        return redirect()->route('superadmin.employee-statuses.index')
            ->with('success', 'Status karyawan berhasil diupdate.');
    }

    /**
     * DELETE: hapus data.
     */
    public function destroy(EmployeeStatus $employeeStatus): RedirectResponse
    {
        $employeeStatus->delete();

        return redirect()->route('superadmin.employee-statuses.index')
            ->with('success', 'Status karyawan berhasil dihapus.');
    }
}
```

---

## 7. Langkah 6: Routes

Routes menghubungkan URL dengan Controller. File: `routes/web.php`

### Tambahkan di Grup Superadmin

Cari bagian middleware superadmin, lalu tambahkan:

```php
// Di dalam Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

    // ... route lain ...

    // Employee Status Management
    Route::resource('employee-statuses', EmployeeStatusController::class)->except(['show', 'create', 'edit']);
});
```

**Penjelasan:**
- `Route::resource()` otomatis membuat 7 route CRUD (index, create, store, show, edit, update, destroy)
- `->except([...])` mengecualikan 3 route karena kita pakai modal (tidak butuh halaman create/edit/show terpisah)
- `->name('superadmin.')` menambahkan prefix nama route, jadi `superadmin.employee-statuses.index`, dst.

**Route yang dihasilkan:**

| Method | URI | Name | Controller Method |
|--------|-----|------|-------------------|
| GET | /superadmin/employee-statuses | superadmin.employee-statuses.index | index() |
| POST | /superadmin/employee-statuses | superadmin.employee-statuses.store | store() |
| PUT | /superadmin/employee-statuses/{employee_status} | superadmin.employee-statuses.update | update() |
| DELETE | /superadmin/employee-statuses/{employee_status} | superadmin.employee-statuses.destroy | destroy() |

---

## 8. Langkah 7: Views (Blade)

Views adalah template HTML (menggunakan Blade, template engine Laravel).

### Struktur Folder

Buat folder: `resources/views/superadmin/employee-status/`

### File: `index.blade.php`

Halaman ini menampilkan daftar + modal untuk tambah/edit (mengikuti pola Alpine.js yang ada di project).

```blade
@extends('superadmin.layouts.app')

@section('title', 'Status Karyawan')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Status Karyawan</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Status Karyawan</h1>
            <p class="text-secondary-500 mt-1">Kelola status kepegawaian (Tetap, Kontrak, Magang)</p>
        </div>
        <button @click="$dispatch('status-form', { mode: 'create' })" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Status
        </button>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama Status</th>
                    <th class="px-6 py-3 text-left">Keterangan</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>
                @forelse($statuses as $status)
                    <tr>
                        <td class="px-6 py-4 font-medium text-secondary-900">{{ $status->name }}</td>
                        <td class="px-6 py-4 text-secondary-600">{{ $status->description ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($status->is_active)
                                <x-badge type="success">Aktif</x-badge>
                            @else
                                <x-badge type="secondary">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button @click="$dispatch('status-form', { mode: 'edit', data: {{ Js::from($status) }}, action: '{{ route('superadmin.employee-statuses.update', $status) }}' })" class="btn btn-ghost btn-sm text-primary-600">Edit</button>
                            
                            {{-- Tombol hapus pakai confirm-dialog global --}}
                            <button @click="$dispatch('confirm-dialog', { title: 'Hapus Status', message: 'Yakin hapus {{ $status->name }}?', confirmText: 'Ya, Hapus', type: 'danger', formAction: '{{ route('superadmin.employee-statuses.destroy', $status) }}' })" class="btn btn-ghost btn-sm text-danger-600">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center text-secondary-500">Belum ada data</td></tr>
                @endforelse
            </x-table>
        </div>
    </div>

    {{ $statuses->links() }}

    {{-- MODAL FORM (Tambah/Edit) dengan Alpine.js --}}
    <div x-data="{ 
            open: {{ $errors->any() ? 'true' : 'false' }}, 
            mode: '{{ old('_form_mode', 'create') }}', 
            action: '{{ old('_form_action', route('superadmin.employee-statuses.store')) }}', 
            form: { 
                name: {{ Js::from(old('name', '')) }}, 
                description: {{ Js::from(old('description', '')) }}, 
                is_active: {{ old('is_active') !== null ? (old('is_active') ? 'true' : 'false') : 'true' }} 
            },
            show(detail) {
                this.mode = detail.mode;
                this.action = detail.mode === 'edit' ? detail.action : '{{ route('superadmin.employee-statuses.store') }}';
                this.form = detail.mode === 'edit' ? detail.data : { name: '', description: '', is_active: true };
                this.open = true;
            }
        }" 
        x-on:status-form.window="show($event.detail)" 
        x-on:keydown.escape.window="open = false" 
        x-effect="document.body.style.overflow = open ? 'hidden' : ''" 
        x-show="open" 
        x-cloak 
        class="modal-backdrop">
        
        <div @click.outside="open = false" x-show="open" class="modal">
            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'edit' ? 'Edit Status' : 'Tambah Status'"></h3>
                <button @click="open = false" class="modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            {{-- Form dibungkus flex-col agar bisa scroll body --}}
            <form :action="action" method="POST" class="flex flex-col flex-1 min-h-0">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="_form_mode" :value="mode">
                <input type="hidden" name="_form_action" :value="action">
                
                <div class="modal-body space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Nama Status <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" class="input w-full @error('name') border-danger-500 @enderror" placeholder="Tetap" required>
                        @error('name')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-1">Keterangan</label>
                        <textarea name="description" x-model="form.description" rows="3" class="input w-full @error('description') border-danger-500 @enderror" placeholder="Deskripsi status (opsional)"></textarea>
                        @error('description')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-secondary-300">
                            <span class="text-sm text-secondary-700">Status Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Tambah'"></button>
                </div>
            </form>
        </div>
    </div>
@endsection
```

---

## 9. Langkah 8: Testing

Testing memastikan kode bekerja dengan benar dan tidak rusak jika dimodifikasi nanti. GajiPro menggunakan Pest PHP.

### Perintah Artisan

```bash
php artisan make:test Superadmin/EmployeeStatusTest --pest
```

File baru: `tests/Feature/Superadmin/EmployeeStatusTest.php`

### Isi Test

```php
<?php

use App\Models\EmployeeStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create([
        'is_superadmin' => true,
        'company_id' => null,
        'is_active' => true,
    ]);
    
    // Login sebagai superadmin sebelum tiap test berjalan
    $this->actingAs($this->superadmin);
});

describe('Employee Status CRUD', function () {
    it('displays the index page', function () {
        $response = $this->get(route('superadmin.employee-statuses.index'));
        
        $response->assertSuccessful();
        $response->assertViewIs('superadmin.employee-status.index');
    });

    it('creates a new status', function () {
        $response = $this->post(route('superadmin.employee-statuses.store'), [
            'name' => 'Tetap',
            'description' => 'Karyawan tetap',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('superadmin.employee-statuses.index'));
        $this->assertDatabaseHas('employee_statuses', ['name' => 'Tetap']);
    });

    it('updates an existing status', function () {
        $status = EmployeeStatus::factory()->create(['name' => 'Kontrak', 'is_active' => true]);

        $response = $this->put(route('superadmin.employee-statuses.update', $status), [
            'name' => 'Kontrak Edited',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('superadmin.employee-statuses.index'));
        expect($status->fresh()->name)->toBe('Kontrak Edited');
        expect($status->fresh()->is_active)->toBeFalse();
    });

    it('deletes a status', function () {
        $status = EmployeeStatus::factory()->create();

        $response = $this->delete(route('superadmin.employee-statuses.destroy', $status));

        $response->assertRedirect(route('superadmin.employee-statuses.index'));
        $this->assertDatabaseMissing('employee_statuses', ['id' => $status->id]);
    });

    it('validates required name field', function () {
        $response = $this->post(route('superadmin.employee-statuses.store'), [
            'name' => '', // kosong
        ]);

        $response->assertSessionHasErrors('name');
    });

    it('prevents duplicate names', function () {
        EmployeeStatus::factory()->create(['name' => 'Tetap']);

        $response = $this->post(route('superadmin.employee-statuses.store'), [
            'name' => 'Tetap', // mencoba membuat dengan nama yang sama
        ]);

        $response->assertSessionHasErrors('name');
    });
});
```

### Jalankan Test

```bash
php artisan test tests/Feature/Superadmin/EmployeeStatusTest.php
```

---

## 10. Checklist Akhir

Sebelum deploy / commit, pastikan mengecek hal-hal berikut:

- [ ] **Migration** sudah dieksekusi (`php artisan migrate`)
- [ ] **Model** sudah mendefinisikan relasi yang diperlukan
- [ ] **Form Request** aturan validasi sudah lengkap (unique, dll)
- [ ] **Controller** berisi logika dasar 4 method utama
- [ ] **Routes** terdaftar di `routes/web.php`
- [ ] **View (Blade)** bisa di-render (termasuk modal bisa tampil & tertutup)
- [ ] **Test (Pest)** lolos semua (`php artisan test`)
- [ ] **Pint** (code formatter) sudah dijalankan (`vendor/bin/pint --dirty`)

Selamat coding!
