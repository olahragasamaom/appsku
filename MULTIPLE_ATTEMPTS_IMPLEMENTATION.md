# Multiple Exam Attempts Implementation - Session 2026-11-04

## Overview
Implementasi sistem ujian offline dengan multiple attempts (max 10 kali per exam per participant) dengan randomisasi soal dan opsi jawaban per attempt.

---

## Requirements (dari Client)

1. **Peserta offline bisa mengerjakan ujian yang sama berkali-kali** (max 10 kali) selama dalam rentang tanggal ujian
2. **Urutan nomor soal teracak** per attempt, tapi **struktur sub-jenis ujian tetap** (misal: Pancasila nomor 1-5 tetap pancasila, hanya posisi nomor dalam group yang berubah)
3. **Urutan opsi jawaban (a/b/c/d/e) teracak** per attempt, tapi **jawaban yang benar tetap benar**
4. **Ranking** hanya menampilkan **best attempt** (nilai tertinggi) per peserta, dengan indikator "attempt ke-X"
5. **History ujian** tercatat per attempt dengan daftar jawaban dan nilai-nilainya

---

## Architecture Design

### Data Model
**Tambahan field di `panritta_ujian_peserta`:**
- `attempt_number` (int, default 1) - nomor usaha 1-10
- `attempt_seed` (int nullable) - seed random untuk reproducible shuffle
- `question_order` (json nullable) - mapping `{soal_id: new_position}` untuk urutan teracak
- `option_mapping` (json nullable) - mapping `{soal_id: {a: orig_a, b: orig_c, ...}}` untuk opsi teracak
- `peserta_offline_id` (foreignId) - reference ke peserta offline untuk multiple attempts tracking

**Jawaban disimpan sebagai opsi asli saja** (A/B/C/D/E pre-shuffle) agar scoring consistent.

---

## Implementation Details

### 1. Database Migrations

**File:** `2026_11_03_173155_add_attempt_fields_to_ujian_peserta_table.php`
- Tambah kolom untuk attempt tracking dan randomization mapping

**File:** `2026_11_03_175648_add_peserta_offline_id_to_ujian_peserta_table.php`
- Tambah foreign key ke peserta offline untuk relasi multiple attempts

### 2. Services

#### `ExamRandomizationService` (app/Services/ExamRandomizationService.php)
**Purpose:** Generate randomisasi soal dan opsi per attempt dengan deterministic seeding

**Methods:**
- `generateAttemptRandomization(UjianPeserta $ujianPeserta)` 
  - Buat seed random dari timestamp + peserta ID
  - Panggil shuffle soal per sub-jenis
  - Panggil shuffle opsi per soal
  - Simpan mapping ke DB

- `shuffleQuestionsBySubJenis(Collection $questions, int $seed)` (private)
  - Group soal berdasarkan sub-jenis ujian
  - Shuffle posisi soal DALAM grup (preserve sub-jenis grouping)
  - Return: `{soal_id: new_position}`

- `deterministicShuffle(array $items, string $seedStr)` (private)
  - Gunakan seed string untuk generate PHP mt_srand reproducible
  - Shuffle array preserving determinism

- `generateOptionMapping(Collection $questions, int $seed)` (private)
  - Loop setiap soal
  - Shuffle opsi a/b/c/d/e
  - Return: `{soal_id: {a: original_option, b: original_option, ...}}`

- `getOriginalOption(UjianPeserta, $soalId, $shuffledLetter)` 
  - Konversi: user pilih "B" → dapat opsi original "E"
  - Used untuk save jawaban

- `getShuffledOption(UjianPeserta, $soalId, $originalLetter)`
  - Konversi kebalikan: original "E" → dapat shuffled "B"
  - Used untuk display pembahasan

#### `ExamAttemptService` (app/Services/ExamAttemptService.php)
**Purpose:** Manage lifecycle attempt baru - validasi, create record, trigger randomization

**Methods:**
- `createOfflineAttempt(Ujian $ujian, PesertaOffline $pesertaOffline)`
  - **Validasi 1:** Ujian dalam rentang tanggal (tanggal_ujian ≤ now ≤ batas_keterlambatan)
  - **Validasi 2:** Peserta belum capai max 10 attempts
  - Create record baru `UjianPeserta` dengan `attempt_number = count+1`
  - Set `status = 'sedang_ujian'`, `waktu_mulai = now()`
  - Set `batas_waktu = now + durasi`
  - Call `ExamRandomizationService->generateAttemptRandomization()` untuk shuffle
  - Return: `UjianPeserta` baru

- `getBestAttempt(Ujian, PesertaOffline)` 
  - Query `UjianPeserta` dengan nilai tertinggi
  - Used untuk display ranking & hasil

- `getAllAttempts(Ujian, PesertaOffline)`
  - Ambil semua attempts history
  - Used untuk tampil attempt list

### 3. Models

#### `UjianPeserta`
**Fillable:** Tambah `attempt_number`, `attempt_seed`, `question_order`, `option_mapping`, `peserta_offline_id`

**Casts:**
```php
'attempt_number' => 'integer',
'attempt_seed' => 'integer',
'question_order' => 'array',
'option_mapping' => 'array',
```

---

## Controller Updates

### `OfflinePortalController` (Peserta\OfflinePortalController.php)

#### Method: `start(Request $request, Ujian $ujian)`
**Old Logic:** Buat satu record ujian_peserta, resume jika paused

**New Logic:**
1. Cek apakah ada attempt sedang berjalan (`status = 'sedang_ujian'`) atau paused
2. Jika paused → resume (set `is_paused = false`)
3. Jika ada attempt sedang berjalan → lanjut saja
4. Jika tidak ada → **call `ExamAttemptService->createOfflineAttempt()`**
   - Ini akan:
     - Validate max 10 attempts
     - Create record baru dengan `attempt_number++`
     - Call randomization service untuk shuffle
     - Return attempt baru

**Error handling:** Jika max attempts atau di luar rentang tanggal → back with error message

#### Method: `exams(Request $request)`
**Old Logic:** Query ujian, set `myAttempt` ke `pesertaOffline->ujianPeserta`

**New Logic:**
1. Loop setiap ujian
2. Query **all attempts** untuk ujian itu: `UjianPeserta->where('ujian_id', $ujian->id)->where('peserta_offline_id', $peserta->id)`
3. Set `$ujian->attempts` = collection semua attempts
4. Set `$ujian->myAttempt` = attempt yang sedang berjalan ATAU attempt terakhir selesai
5. Set `$ujian->totalAttempts` = count(attempts)
6. Set `$ujian->remainingAttempts` = max(0, 10 - totalAttempts)

### `UjianController` (Peserta\UjianController.php)

#### Method: `kerjakan(Request $request, Ujian $ujian)`
**Old Logic:** Load soal, shuffle jika `ujian->acak_soal`, pass ke view

**New Logic:**
```php
// Load ujianSoals (tetap sama)
$ujianSoals = $ujian->ujianSoals()->with('soal')->orderBy('urutan')->orderBy('id')->get();

// Handle randomization
if ($peserta instanceof UjianPeserta && $peserta->question_order) {
    // Offline peserta dengan attempt randomization → apply shuffled order
    $ujianSoals = $this->applyShuffledOrder($ujianSoals, $peserta->question_order);
    $shuffledOptions = $peserta->option_mapping;
} elseif ($ujian->acak_soal && ! ($peserta instanceof UjianPeserta)) {
    // Online peserta dengan ujian acak_soal → shuffle seperti biasa
    $ujianSoals = $ujianSoals->shuffle();
    $shuffledOptions = null;
} else {
    $shuffledOptions = null;
}

// Pass ke view
return view('peserta.ujian.kerjakan', compact(..., 'shuffledOptions'));
```

**Helper method:**
```php
private function applyShuffledOrder(Collection $ujianSoals, array $questionOrder): Collection
{
    return $ujianSoals->sortBy(fn($us) => $questionOrder[$us->soal_id] ?? $us->id);
}
```

#### Method: `saveAnswer(StoreJawabanRequest $request, Ujian $ujian)`
**Old Logic:** Simpan jawaban langsung seperti user input

**New Logic:**
```php
$validated = $request->validated(); // user jawaban yang sudah shuffled
$ujianSoal = ...; // soal yang dijawab

// Convert shuffled → original option
$jawaban = $validated['jawaban'] ?? null;
if ($peserta instanceof UjianPeserta && $peserta->option_mapping) {
    $originalJawaban = $this->randomization->getOriginalOption($peserta, $ujianSoal->soal_id, $jawaban);
    if ($originalJawaban) {
        $jawaban = strtoupper($originalJawaban); // Convert ke original opsi
    }
}

// Scoring menggunakan original jawaban (consistent)
$score = $this->scoring->scoreAnswer($ujianSoal->soal, $jawaban);

// Simpan jawaban original (bukan shuffled)
$peserta->jawaban()->updateOrCreate([...], [
    'jawaban' => $jawaban,  // Original opsi
    'nilai' => $score['nilai'],
    'benar' => $score['benar'],
]);
```

### `UjianScoringService` (app/Services/Ujian/UjianScoringService.php)

#### Method: `rank(Ujian $ujian)` - **UPDATED**
**Old Logic:** Return semua peserta selesai, sort by nilai

**New Logic:**
```php
// Get all peserta selesai
$peserta = $ujian->peserta()
    ->with('user', 'pesertaOffline')
    ->where('status', 'selesai')
    ->get();

// Filter: hanya best attempt per peserta (group by peserta_offline_id atau user_id)
$bestAttempts = $peserta->groupBy(function($item) {
    return $item->peserta_offline_id ?? $item->user_id;
})->map(function($attempts) {
    // Ambil attempt dengan nilai tertinggi
    return $attempts->sortByDesc('total_nilai')->first();
})->values();

// Sort by nilai descending
return $bestAttempts->sortBy([
    fn($a, $b) => $b['total_nilai'] <=> $a['total_nilai'] ?: $a['waktu_selesai'] <=> $b['waktu_selesai']
])->values();
```

**Effect:** Ranking hanya tampil best attempt, bukan semua attempts

---

## View Updates

### `soal-options-display.blade.php`
**Old:** Hardcode loop `['A', 'B', 'C', 'D', 'E']`

**New:**
```blade
@props(['ujianSoal', 'jawaban', 'shuffledOptions' => null])

@php
    // Tentukan urutan opsi yang ditampilkan
    if ($shuffledOptions && isset($shuffledOptions[$soal->id])) {
        // Gunakan shuffled order dari attempt
        $displayOrder = array_keys($shuffledOptions[$soal->id]); // ['b', 'e', 'a', 'c', 'd']
        $displayOrder = array_map('strtoupper', $displayOrder); // ['B', 'E', 'A', 'C', 'D']
    } else {
        // Default: urutan normal A, B, C, D, E
        $displayOrder = ['A', 'B', 'C', 'D', 'E'];
    }
@endphp

{{-- Loop menggunakan $displayOrder (teracak atau normal) --}}
@foreach($displayOrder as $opsi)
    {{-- Render opsi dengan label teracak --}}
    <label>
        <input type="radio" 
               name="soal_{{ $ujianSoal->id }}"
               value="{{ $opsi }}"  {{-- Send shuffled letter (B) --}}
               x-model="jawaban[{{ $ujianSoal->id }}]"
               @change="save({{ $ujianSoal->id }}, '{{ $opsi }}')">
        <span>{{ $opsi }}.</span> {{-- Display B --}}
        <span>{!! render($soal->{'opsi_'.strtolower($opsi)}) !!}</span> {{-- Content dari original B --}}
    </label>
@endforeach
```

**Effect:** 
- Jika shuffled: opsi ditampilkan dalam urutan teracak (B, E, A, C, D) tapi label tetap A, B, C, D (posisi user select)
- User pilih "A" (posisi pertama) → kirim "B" ke controller
- Controller convert "B" → "E" (original)

### `kerjakan.blade.php`
**Change:**
```blade
{{-- Old: --}}
<x-soal-options-display :ujianSoal="$ujianSoal" :jawaban="$jawaban" />

{{-- New: pass shuffledOptions --}}
<x-soal-options-display :ujianSoal="$ujianSoal" :jawaban="$jawaban" :shuffledOptions="$shuffledOptions" />
```

### `hasil.blade.php`
**Add attempt indicator:**
```blade
<h1>{{ $ujian->nama_ujian }}</h1>

@if($peserta->attempt_number)
    <p class="text-xs text-slate-500 mt-2">Usaha ke-{{ $peserta->attempt_number }} dari 10</p>
@endif

<p class="text-4xl font-extrabold">{{ $peserta->total_nilai ?? 0 }}</p>
```

### `exams.blade.php` (offline portal)
**Updates:**
1. Change `$ujianPeserta = $peserta->ujianPeserta` → `$ujianPeserta = $ujian->myAttempt`
2. Add remaining attempts check: `$ujian->remainingAttempts > 0`
3. Display attempt number: `Selesai (Usaha {{ $ujianPeserta->attempt_number }}/10)`
4. Show "Ujian Lagi" button jika `remainingAttempts > 0`
5. Display counter: `Sisa {{ $ujian->remainingAttempts }}/10`
6. Disable "Mulai Ujian" jika `remainingAttempts == 0`

---

## Flow - User Perspective

### Scenario: User Pertama Kali Ujian
1. User login → dashboard offline exams
2. Lihat ujian "Test CPNS 1" → tombol "Mulai Ujian" aktif
3. Click "Mulai Ujian" → modal warning konfirmasi
4. Confirm → redirect ke `/ujian/{id}/kerjakan`
5. Controller `start()` → create attempt baru:
   - Set `attempt_number = 1`
   - Trigger randomization → shuffle soal + opsi
   - Store mapping di DB
6. View `kerjakan.blade.php` load dengan:
   - Soal dalam urutan teracak (dari `question_order`)
   - Opsi dalam urutan teracak (dari `option_mapping`)
7. User jawab soal → save answer:
   - User pilih opsi "B" (bisa jadi opsi "E" asli)
   - Controller convert → original opsi "E"
   - Scoring pakai "E"
8. User submit ujian → status `selesai`, nilai computed
9. Dashboard → button "Lihat Hasil" + "Ujian Lagi (9/10)"

### Scenario: User Ujian Kedua Kali
1. Dashboard → click "Ujian Lagi"
2. Modal warning → confirm
3. `start()` → create attempt baru:
   - Set `attempt_number = 2`
   - New randomization seed → soal & opsi urutan berbeda dari attempt 1
4. Ujian kedua dengan susunan berbeda
5. Submit → nilai 2 tersimpan
6. Dashboard → best attempt tampil (nilainya dibanding, ambil tertinggi)

### Scenario: Ranking
- Ranking query filter: hanya best attempt per peserta
- Misal user lakukan 3 attempts dengan nilai 70, 85, 60
- Ranking tampil: user dengan nilai 85 (attempt 2)
- Text di ranking: "Usaha ke-2 dari 3 yang dilakukan"

---

## Key Design Decisions

### 1. Simpan Jawaban Sebagai Original Opsi
**Decision:** Jawaban disimpan sebagai opsi asli (A/B/C/D/E pre-shuffle)

**Reasoning:**
- Scoring logic tetap consistent
- Pembahasan soal tidak perlu convert
- Ranking/leaderboard pakai nilai final yang fair
- Tidak perlu kompleksitas tracking "user lihat opsi X tapi pilih Y"

### 2. Deterministic Seeding
**Decision:** Gunakan `seed = crc32(seedStr) % 2147483647` untuk reproducible shuffle

**Reasoning:**
- Jika user refresh browser → soal order tetap sama (tidak re-shuffle)
- Bisa debug/audit: given seed, bisa generate ulang shuffle yang sama
- Safety: tidak dependent pada PHP rand() yang non-deterministic cross-request

### 3. Preserve Sub-Jenis Grouping
**Decision:** Group soal per sub-jenis, shuffle DALAM grup, bukan cross-grup

**Reasoning:**
- Client requirement: "Pancasila tetap nomor 1-5, tapi posisi dalam group acak"
- UX: user tahu TWK soal 1-50, TIU soal 51-100, TKP soal 101-140 (atau teracak tapi grouped)
- Fairness: setiap peserta tetap lihat sub-jenis balanced, hanya urutan beda

### 4. Max 10 Attempts Per Exam
**Decision:** Hardcode limit 10, validate di `ExamAttemptService->createOfflineAttempt()`

**Reasoning:**
- Client requirement: max 10 kali
- Simple: tidak perlu config dynamic per exam
- Safety: prevent abuse (repeat ujian unlimited)

---

## Testing Checklist (Manual)

### Basic Flow
- [ ] Login offline peserta
- [ ] Dashboard tampil ujian dengan tombol "Mulai Ujian"
- [ ] Click mulai → attempt 1 dibuat
- [ ] Lihat soal → urutan teracak (vs peserta lain berbeda)
- [ ] Lihat opsi → urutan teracak
- [ ] Jawab soal → opsi original tersimpan
- [ ] Submit ujian → nilai computed, attempt 1 selesai
- [ ] Dashboard → button "Ujian Lagi (9/10)"

### Multiple Attempts
- [ ] Click "Ujian Lagi" → attempt 2 dibuat dengan seed baru
- [ ] Soal urutan berbeda dari attempt 1
- [ ] Opsi urutan berbeda dari attempt 1
- [ ] Complete attempt 2 dengan nilai lebih rendah
- [ ] Dashboard → best attempt (attempt 1) tetap ditampil
- [ ] Hasil page → show "Usaha ke-1 dari 10"

### Limit Enforcement
- [ ] Create 10 attempts → semua selesai
- [ ] Click "Ujian Lagi" → error "Batas maksimal 10 kali ujian"
- [ ] Dashboard button disabled

### Ranking
- [ ] 3 peserta dengan 2+ attempts masing-masing
- [ ] Ranking hanya tampil best attempt per peserta
- [ ] Order by nilai tertinggi
- [ ] Text menunjukkan "Attempt X/total_attempts_done"

### Edge Cases
- [ ] Resume paused attempt → opsi/soal tetap sama (consistent seed)
- [ ] Page refresh di tengah ujian → soal/opsi tidak berubah
- [ ] Multiple browser tabs → conflict handling (last window menang)

---

## Files Modified

### Services
- `app/Services/ExamRandomizationService.php` (NEW)
- `app/Services/ExamAttemptService.php` (NEW)
- `app/Services/Ujian/UjianScoringService.php` (UPDATED - rank method)

### Controllers
- `app/Http/Controllers/Peserta/OfflinePortalController.php` (UPDATED - start, exams)
- `app/Http/Controllers/Peserta/UjianController.php` (UPDATED - kerjakan, saveAnswer)

### Models
- `app/Models/UjianPeserta.php` (UPDATED - fillable, casts)

### Migrations
- `database/migrations/2026_11_03_173155_add_attempt_fields_to_ujian_peserta_table.php` (NEW)
- `database/migrations/2026_11_03_175648_add_peserta_offline_id_to_ujian_peserta_table.php` (NEW)

### Views
- `resources/views/components/soal-options-display.blade.php` (UPDATED)
- `resources/views/peserta/ujian/kerjakan.blade.php` (UPDATED)
- `resources/views/peserta/ujian/hasil.blade.php` (UPDATED)
- `resources/views/peserta/ujian/offline/exams.blade.php` (UPDATED)

---

## Database Schema Changes

### panritta_ujian_peserta - New Columns
```
- attempt_number (INT, default 1)
- attempt_seed (INT, nullable)
- question_order (JSON, nullable) - {soal_id: position, ...}
- option_mapping (JSON, nullable) - {soal_id: {a: orig, b: orig, ...}, ...}
- peserta_offline_id (BIGINT UNSIGNED, nullable, FK)
```

---

## Status

✅ **Completed:**
- Database migrations
- Service layer (randomization + attempt management)
- Controller updates (multiple attempt creation, conversion logic)
- View updates (display shuffled questions/options, attempt counter)
- Ranking query (best attempt filter)

⏳ **Ready for Testing:**
- Manual UI testing of complete flow
- Edge case validation
- Performance check (shuffle logic for large exams)

📝 **Not Yet Implemented:**
- Unit tests for randomization logic
- History detail view (attempt-by-attempt scores)
- Export/audit trail per attempt
- Admin interface untuk view semua attempts peserta

---

## Notes for Next Session

1. **Before committing:** Manual test all scenarios above
2. **If issues found:**
   - Check `attempt_seed` storage (ensure integer)
   - Verify `question_order`/`option_mapping` JSON format
   - Test offline session persistence across attempts
3. **Potential improvements:**
   - Add `regenerate_seed` button (admin) untuk re-shuffle tertentu
   - Attempt history view detail page
   - Analytics: compare scores across attempts per peserta
   - Accessibility: ensure shuffled opsi still accessible with ARIA labels

