<!-- markdownlint-disable -->
# Implementation Plan (PLAN) — CPNS Online Exam (CAT)

**Document Name:** Implementation Plan — HRIS to CPNS Online Exam Platform (CAT)
**Backend:** Laravel 12.x (PHP 8.2+)
**Frontend:** Blade + Alpine.js + Tailwind CSS 4
**Testing:** Pest 3 (Two-Layer Mandate — micro per task + macro per phase)
**Status:** Draft (Phase Plan)
**Author:** Planner Architect (SDLC `/sdlc-plan-tasks`)
**Upstream:** `docs/TECH_SPEC.md` (self-assessed Readiness 95/100), `docs/PRD.md`

---

## 0. How to Read This Plan

- Work is grouped into **Phases (P1..P7)**. Each phase ends with a **macro gate**: the full Pest
  suite MUST pass before starting the next phase.
- Each phase contains **micro-tasks** with a stable ID (e.g., `P1-T3`). Each micro-task lists:
  - **Goal** — the single outcome of the task.
  - **Files** — exact paths to create/modify (surgical scope).
  - **Spec Ref** — the Tech Spec section/decision it implements.
  - **Tests** — the Pest test(s) that MUST accompany the task (micro-level mandate).
- **Artisan-first:** create files via `php artisan make:*` (see each task). Pass `--no-interaction`.
- **Surgical edits only:** modify the minimum lines. Do not refactor untouched HRIS code.
- **Formatting gate:** run `vendor/bin/pint --dirty --format agent` before finalizing each task.

---

## 1. Codebase Reality Check (As-Is vs Spec Delta)

> This plan was written after inspecting the actual codebase. Several building blocks already exist;
> the plan targets the **delta** only. The following table is authoritative for scoping.

| Spec Artifact | Codebase Status | Delta Action |
| ------------- | --------------- | ------------ |
| `panritta_ujian` table | Exists (`create_ujians_table`) — **no** `sub_jenis_ujian_id` | **ADD column** (P1) |
| `panritta_ujian_peserta` table | Exists — **HAS `unique(ujian_id,user_id)`** named `ujian_peserta_unique` | **DROP unique** for re-take (P1) — conflicts with AD-9 |
| `panritta_ujian_peserta` lifecycle fields | Missing `langganan_id`,`batas_waktu`,`auto_submitted` | **ADD columns** (P1) |
| `panritta_paket_ujian` pivot | **Missing** — online exams currently linked via `panritta_ujian.akses_member` (json) | **CREATE pivot** + migrate away from json (P1/P3) |
| `panritta_ujian_peserta_kategori` | **Missing** | **CREATE table** (P1) |
| `panritta_peserta_offline` | **Missing** | **CREATE table** (P1) |
| `Ujian` model | Exists with `jenisUjians`,`soals`,`peserta`,`ujianSoals` | **ADD** `subJenisUjian`,`pakets`,`pesertaOffline` relations (P2) |
| `UjianScoringService` | **Exists** — `scoreAnswer`,`finalize`,`breakdownPerJenis`,`rank` | **EXTEND**: persist per-kategori rows; align method names (P3) |
| `PesertaLanggananService` | **Exists** — subscribe/markPaid/markFailed; sets `sisa_kuota_ujian = paket->kuota_ujian` | **EXTEND**: `null` = unlimited (M-AU-4) (P3) |
| `Peserta\UjianController` | **Exists** — start/kerjakan/saveAnswer/submit/hasil; offline uses token | **REFACTOR** to use `AttemptService` + dual-guard + snapshot deadline (P5) |
| `AttemptService` | **Missing** | **CREATE** (P3) |
| `ExamAssemblyService` | **Missing** (assembly logic partly in `Superadmin\UjianSoalController`) | **CREATE** service (P3) |
| `OfflineParticipantService` | **Missing** | **CREATE** (P3) |
| `TokenService` | **Missing** (token logic inline in controller) | **CREATE** (P3) |
| `OfflineParticipantAuth` middleware | **Missing** | **CREATE** + register alias (P4) |
| `UjianRequest` | **Exists** — uses `akses_member`, no `sub_jenis_ujian_id` | **EXTEND** rules (P5) |
| `PaketRequest` | **Exists** | **EXTEND** `kuota_ujian` null semantics (P5) |
| Scheduled jobs | **None** for exam auto-submit | **CREATE** `AutoSubmitExpiredAttempts`,`ExpireSubscriptions` (P6) |
| Existing tests | `UjianEngineTest`, `UjianScoringServiceTest`, etc. | **MUST stay green**; extend, do not delete |

> **Conflict flags carried into execution:**
> - **CF-1 (AD-9 vs schema):** existing `unique(ujian_id,user_id)` blocks re-takes. P1-T2 drops it.
>   Any existing test asserting the unique constraint must be updated (not deleted) with rationale.
> - **CF-2 (AD-5 vs `akses_member` json):** online-exam→package linkage currently lives in a json
>   column. P3-T7 migrates reads to the new pivot while keeping the column nullable for one release
>   (no destructive drop) to honor AGENTS "do no harm".

---

## 2. Phase Overview & Macro Gates

| Phase | Theme | Macro Gate (exit condition) |
| ----- | ----- | --------------------------- |
| **P1** | Schema migrations (additive + 1 drop-unique) | `php artisan migrate:fresh` runs clean; migration tests green |
| **P2** | Models, casts, relationships | Model/relationship tests green |
| **P3** | Service layer (assembly, scoring persist, attempt, offline, token, quota) | Service unit tests green |
| **P4** | Offline participant auth (session + middleware) | Middleware tests green |
| **P5** | Controllers, routes, form requests (wire services) | Feature/HTTP tests green |
| **P6** | Scheduled jobs (auto-submit, expire subscriptions) | Job tests green |
| **P7** | Macro regression + traceability closure | **Full suite green**, zero failures |

---

## P1 — Schema Migrations

> All migrations are **additive** except **P1-T2** (drops a unique constraint per AD-9). Prefix
> `panritta_`. Follow existing migration style (anonymous class, `Schema::create/table`, explicit
> FK with `cascadeOnDelete`). Verify parent dir `database/migrations/` before creating.

### P1-T1 — Add `sub_jenis_ujian_id` to `panritta_ujian`

- **Goal:** Support the online-exam sub-type field (PRD §4.3.2).
- **Create:** `php artisan make:migration add_sub_jenis_ujian_id_to_panritta_ujian_table --no-interaction`
- **Change:** Add nullable FK `sub_jenis_ujian_id` → `panritta_sub_jenis_ujian.id`, `nullOnDelete`.
- **Spec Ref:** §3 baseline, §4.5, AD-7 (C-AU-2).
- **Tests:** `tests/Feature/Migration/UjianSchemaTest.php` — asserts column exists, is nullable,
  FK present.

### P1-T2 — Drop `ujian_peserta_unique` from `panritta_ujian_peserta` (CF-1 / AD-9)

- **Goal:** Allow re-takes — multiple attempts per `(ujian_id, user_id)`.
- **Create:** `php artisan make:migration drop_unique_from_panritta_ujian_peserta_table --no-interaction`
- **Change:** `$table->dropUnique('ujian_peserta_unique');` in `up()`; re-add in `down()`.
- **Spec Ref:** §4.4 re-take, AD-9 (C-AU-2).
- **Risk:** Existing code assumes at most one row per pair (e.g., `->where('user_id',...)->first()`).
  P5 refactors these reads to fetch the **latest active/relevant** attempt. Flag any query relying
  on uniqueness during P5.
- **Tests:** `UjianSchemaTest` — two `UjianPeserta` rows with same `ujian_id`+`user_id` can be
  inserted without violation.

### P1-T3 — Add attempt lifecycle fields to `panritta_ujian_peserta`

- **Goal:** Support auto-submit + quota linkage + snapshot deadline (AD-6/AD-10).
- **Create:** `php artisan make:migration add_lifecycle_fields_to_panritta_ujian_peserta_table --no-interaction`
- **Change:** Add `langganan_id` nullable FK → `panritta_peserta_langganan.id` (`nullOnDelete`);
  `batas_waktu` nullable dateTime; `auto_submitted` boolean default false.
- **Spec Ref:** §4.2, AD-10 (C-AU-1/C-AU-5/C-AU-6).
- **Tests:** `UjianSchemaTest` — columns exist with correct nullability/default.

### P1-T4 — Create `panritta_paket_ujian` pivot (AD-5)

- **Goal:** Relational link package ↔ online exam.
- **Create:** `php artisan make:migration create_panritta_paket_ujian_table --no-interaction`
- **Change:** `id`, `paket_id` FK → `panritta_paket` (cascade), `ujian_id` FK → `panritta_ujian`
  (cascade), timestamps, `unique(paket_id, ujian_id)`.
- **Spec Ref:** §4.1, AD-5.
- **Tests:** `tests/Feature/Migration/PaketUjianSchemaTest.php` — unique enforced; FKs cascade.

### P1-T5 — Create `panritta_ujian_peserta_kategori` (AD-4)

- **Goal:** Persist per-category score for pass evaluation and result display.
- **Create:** `php artisan make:migration create_panritta_ujian_peserta_kategori_table --no-interaction`
- **Change:** `id`, `ujian_peserta_id` FK (cascade), `jenis_ujian_id` FK → `panritta_jenis_ujian`
  (cascade), `nilai_kategori` decimal(8,2) default 0, `passing_grade` decimal(6,2) nullable,
  `lulus_kategori` boolean nullable, timestamps, `unique(ujian_peserta_id, jenis_ujian_id)`.
- **Spec Ref:** §4.3, AD-4.
- **Tests:** `tests/Feature/Migration/UjianPesertaKategoriSchemaTest.php` — unique enforced.

### P1-T6 — Create `panritta_peserta_offline` (C-4)

- **Goal:** Offline participant master (independent of `users`).
- **Create:** `php artisan make:migration create_panritta_peserta_offline_table --no-interaction`
- **Change:** `id`, `ujian_id` FK (cascade), `nomor_peserta` varchar(50), `nama_peserta`
  varchar(255), `kode_akses` varchar(255) *(bcrypt hash)*, `ujian_peserta_id` nullable FK
  (`nullOnDelete`), timestamps, `unique(ujian_id, nomor_peserta)`.
- **Spec Ref:** §4.4, C-4.
- **Tests:** `tests/Feature/Migration/PesertaOfflineSchemaTest.php` — unique per `ujian_id`.

### P1 — Macro Gate

- `php artisan migrate:fresh --seed` completes without error.
- All `tests/Feature/Migration/*` green. Existing migration-dependent tests still green.

---

## P2 — Models, Casts & Relationships

> New models via `php artisan make:model {Name} -f` (with factory). Follow existing model style:
> `protected $table = 'panritta_*'`, `casts()` method (not `$casts` property), explicit return
> types on relations. Reuse existing `Ujian`, `Paket`, `UjianPeserta` models — **extend**, do not
> replace.

### P2-T1 — New model `PaketUjian` (or belongsToMany on Paket/Ujian)

- **Goal:** Expose package ↔ online-exam pivot as relations.
- **Create:** `php artisan make:model PaketUjian --no-interaction` *(only if a dedicated pivot model
  is needed; otherwise use belongsToMany without a model)*.
- **Change (existing models):**
  - `app/Models/Ujian.php` — add `pakets(): BelongsToMany` on `panritta_paket_ujian`.
  - `app/Models/Paket.php` — add `ujians(): BelongsToMany` on `panritta_paket_ujian`.
- **Spec Ref:** §5.1, §5.2, AD-5.
- **Tests:** `tests/Feature/Model/PaketUjianRelationTest.php` — attach/detach; pivot round-trips.

### P2-T2 — New model `UjianPesertaKategori`

- **Goal:** ORM access to per-category result rows.
- **Create:** `php artisan make:model UjianPesertaKategori -f --no-interaction`
  (`$table = 'panritta_ujian_peserta_kategori'`).
- **Change:** `app/Models/UjianPeserta.php` — add `kategori(): HasMany` and
  `langganan(): BelongsTo` (nullable).
- **Spec Ref:** §5.1, §5.2, AD-4.
- **Tests:** `tests/Feature/Model/UjianPesertaKategoriRelationTest.php`.

### P2-T3 — New model `PesertaOffline`

- **Goal:** ORM access to offline participant master.
- **Create:** `php artisan make:model PesertaOffline -f --no-interaction`
  (`$table = 'panritta_peserta_offline'`; `kode_akses` hidden; `casts()` as needed).
- **Change:**
  - Relations: `PesertaOffline::ujian(): BelongsTo`, `PesertaOffline::ujianPeserta(): BelongsTo`.
  - `app/Models/Ujian.php` — add `pesertaOffline(): HasMany`.
- **Spec Ref:** §5.1, §5.2, C-4.
- **Tests:** `tests/Feature/Model/PesertaOfflineRelationTest.php`.

### P2-T4 — Extend `Ujian` with `subJenisUjian` + fillable/casts

- **Goal:** Wire the new `sub_jenis_ujian_id` column.
- **Change:** `app/Models/Ujian.php` — add `sub_jenis_ujian_id` to `$fillable`, integer cast, and
  `subJenisUjian(): BelongsTo`.
- **Spec Ref:** §4.5, AD-7.
- **Tests:** `tests/Feature/Model/UjianSubJenisRelationTest.php`.

### P2-T5 — Extend `UjianPeserta` fillable/casts for lifecycle fields

- **Goal:** Make `langganan_id`,`batas_waktu`,`auto_submitted` mass-assignable + cast.
- **Change:** `app/Models/UjianPeserta.php` — add to `$fillable`; casts `batas_waktu:datetime`,
  `auto_submitted:boolean`, `langganan_id:integer`.
- **Spec Ref:** §4.2, AD-10.
- **Tests:** covered in `UjianPesertaKategoriRelationTest` + attempt tests (P3).

### P2 — Macro Gate

- All `tests/Feature/Model/*` green. Existing model tests still green.

---

## P3 — Service Layer

> Services hold all business logic (thin controllers). New services under `app/Services/Ujian/`
> to match the existing `UjianScoringService`/`PesertaLanggananService` namespace. Create via
> `php artisan make:class Services/Ujian/{Name} --no-interaction`. Constructor property promotion
> for dependencies. Explicit return types.

### P3-T1 — `ExamAssemblyService` (capacity-first assembly)

- **Goal:** Centralize question assembly with a live remaining counter (PRD §4.3.1).
- **Create:** `app/Services/Ujian/ExamAssemblyService.php`
- **Methods:** `remainingSlots(Ujian): int`, `addQuestions(Ujian, int $jenisUjianId, array $soalIds): void`,
  `removeQuestion(Ujian, int $soalId): void`, `assertFinalizable(Ujian): void`.
- **Behavior:** `remainingSlots = jumlah_soal - count(ujianSoals)`; `addQuestions` rejects if it
  would exceed `jumlah_soal`, sets `jenis_ujian_id` + next `urutan`; `assertFinalizable` throws
  `ValidationException` when `remainingSlots > 0` OR pool shortage (R4).
- **Spec Ref:** §6.1.
- **Tests:** `tests/Feature/Ujian/ExamAssemblyServiceTest.php` — remaining math; over-capacity
  rejected; finalizable blocks on shortage.

### P3-T2 — Extend `UjianScoringService` to persist per-category rows

- **Goal:** Align existing service with §6.2 (`aggregateCategories`/`evaluatePass`) and write
  `panritta_ujian_peserta_kategori`. Existing `finalize()` computes pass in-memory but does NOT
  persist category rows.
- **Change:** `app/Services/Ujian/UjianScoringService.php`
  - Add `aggregateCategories(UjianPeserta): void` — upsert category rows (idempotent).
  - Add `evaluatePass(UjianPeserta): void` — set `lulus_kategori`, `UjianPeserta.lulus = all-pass`
    (AD-4), `total_nilai = sum`.
  - Refactor `finalize()` to call `aggregateCategories` then `evaluatePass` (preserve existing
    signature to avoid breaking `Peserta\UjianController` and `UjianScoringServiceTest`).
  - Keep `scoreAnswer(Soal, ?string)` signature as-is (already correct per C-3 chain).
- **Spec Ref:** §6.2, AD-4 (C-2/C-3).
- **Tests:** extend `tests/Feature/Superadmin/UjianScoringServiceTest.php` — category rows persisted;
  upsert idempotent (re-scoring same attempt yields same rows); all-pass vs single-fail.

### P3-T3 — `AttemptService` — start / submit / auto-submit / snapshot deadline

- **Goal:** Own the attempt lifecycle including snapshot `batas_waktu` (AD-10) and quota (AD-6).
- **Create:** `app/Services/Ujian/AttemptService.php`
- **Methods:**
  - `start(Ujian, int $userId): UjianPeserta` — status `sedang_ujian`, `waktu_mulai=now`; compute
    `batas_waktu` (offline = `now()+durasi`; online = snapshot `langganan->berakhir_pada`); online
    quota decrement in `DB::transaction` + `lockForUpdate()` on langganan; reject if
    `sisa_kuota_ujian <= 0` **unless** `kuota_ujian === null` (unlimited, M-AU-4); re-take creates a
    NEW row (AD-9).
  - `startOffline(string $nomor, string $kode, Ujian): UjianPeserta` — `Hash::check`; create attempt;
    link `PesertaOffline.ujian_peserta_id`.
  - `submit(UjianPeserta): void` — idempotent no-op if `selesai`; else run scoring pipeline
    (`aggregateCategories`→`evaluatePass`), set `selesai`/`waktu_selesai`.
  - `autoSubmitExpired(): void` — process rows where `now >= batas_waktu` AND `status='sedang_ujian'`
    (single source = `batas_waktu`, C-AU-6); set `auto_submitted=true`; call `submit()`.
- **Spec Ref:** §6.3, AD-6/AD-9/AD-10 (C-AU-1/C-AU-5/C-AU-6, M-AU-4, C-3, C-4).
- **Tests:** `tests/Feature/Ujian/AttemptServiceTest.php` —
  - snapshot deadline offline vs online; mid-attempt renewal does NOT extend (C-AU-6);
  - concurrent `start` consumes quota exactly once (lock);
  - unlimited quota bypass (M-AU-4);
  - re-take creates new row (AD-9);
  - `submit` idempotency; `autoSubmitExpired` processes each row once, no quota refund (R3).

### P3-T4 — `OfflineParticipantService` — admin CRUD + credential generation

- **Goal:** Manage offline participant master data (C-4).
- **Create:** `app/Services/Ujian/OfflineParticipantService.php`
- **Methods:** `create(Ujian, array): PesertaOffline` (validates `offline_kelas`; generates
  plaintext `kode_akses` returned once; stores `Hash::make`), `bulkCreate(Ujian, array): Collection`,
  `blockParticipant(PesertaOffline): void` (sets linked `UjianPeserta.status=diblokir`).
- **Spec Ref:** §6.4, C-4.
- **Tests:** `tests/Feature/Ujian/OfflineParticipantServiceTest.php` — create on non-offline exam
  rejected; plaintext returned once; hash stored (not plaintext); block sets status.

### P3-T5 — `TokenService` — offline token generation & uniqueness

- **Goal:** Extract token logic from controller (PRD §4.3.1).
- **Create:** `app/Services/Ujian/TokenService.php`
- **Methods:** `ensureToken(Ujian): string` — if `token_ujian` null, generate unique token across
  `offline_kelas` exams.
- **Spec Ref:** §6.5.
- **Tests:** `tests/Feature/Ujian/TokenServiceTest.php` — null generates; uniqueness enforced.

### P3-T6 — Extend `PesertaLanggananService` for unlimited-quota semantics (M-AU-4)

- **Goal:** When `paket->kuota_ujian === null`, set `sisa_kuota_ujian = null` (unlimited), not 0.
- **Change:** `app/Services/Ujian/PesertaLanggananService.php` — in `subscribe()` and `markPaid()`,
  keep `sisa_kuota_ujian = $paket->kuota_ujian` (already null-passing) but add an explicit test to
  lock the contract that `null` means unlimited (no code change if already null-safe; add guard/
  comment only if needed).
- **Spec Ref:** §6.3 quota block, AD-8 (M-AU-4).
- **Tests:** extend subscription test — `kuota_ujian=null` → `sisa_kuota_ujian` stays null.

### P3-T7 — Package↔online-exam sync via new pivot (CF-2 / AD-5)

- **Goal:** Move package→exam linkage from `akses_member` json to `panritta_paket_ujian`.
- **Create/Change:** helper on `PaketController` path (wired in P5) reading/writing the pivot; a
  small `PaketUjianSyncService` OR method `Paket::ujians()->sync()` guarded to online-only exams.
- **Non-destructive:** keep `akses_member` column nullable for one release; new reads use pivot.
- **Spec Ref:** §4.1, §6 (guard online-only), AD-5.
- **Tests:** `tests/Feature/Ujian/PaketUjianSyncTest.php` — offline exam rejected on attach; pivot
  round-trips; quota/duration unaffected.

### P3 — Macro Gate

- All `tests/Feature/Ujian/*` service tests green. `UjianScoringServiceTest` (extended) green.
  Existing `UjianEngineTest` still green (may need minor updates flagged in P5, not here).

---

## P4 — Offline Participant Authentication

> Offline participants have no `users` row and no Spatie role. Their session is session-key based
> and isolated from the `auth` guard (§8.3). Middleware registered in `bootstrap/app.php`
> (Laravel 12 style — `withMiddleware(...)->alias([...])`).

### P4-T1 — Create `OfflineParticipantAuth` middleware

- **Goal:** Guard offline-participant routes (§8.3).
- **Create:** `php artisan make:middleware OfflineParticipantAuth --no-interaction`
- **Behavior:**
  1. `session('offline_peserta_id')` set → else abort 403.
  2. `session('offline_ujian_id') === (int) route param 'ujian'` → else abort 403.
  3. linked `UjianPeserta` status `sedang_ujian`; if `selesai` → redirect to result when
     `tampilkan_hasil`, else abort 403.
- **Spec Ref:** §8.3 (C-AU-2).
- **Tests:** `tests/Feature/Auth/OfflineParticipantAuthTest.php` — missing key → 403; ujian mismatch
  → 403; selesai + `tampilkan_hasil` → redirect; selesai + hidden → 403.

### P4-T2 — Register `offline.auth` alias in `bootstrap/app.php`

- **Goal:** Wire the middleware alias.
- **Change:** `bootstrap/app.php` — `->withMiddleware(fn ($m) => $m->alias(['offline.auth' => ...]))`.
  Surgical: add to existing `withMiddleware` closure if present; do not restructure other middleware.
- **Spec Ref:** §8.2, §8.3.
- **Tests:** covered by P4-T1 route-level assertions (alias resolves).

### P4 — Macro Gate

- `tests/Feature/Auth/OfflineParticipantAuthTest.php` green. Existing auth tests still green.

---

## P5 — Controllers, Routes & Form Requests

> Wire services into HTTP. Admin controllers live under `App\Http\Controllers\Superadmin`; participant
> under `App\Http\Controllers\Peserta`. Reuse existing controllers where present — **extend**. Use
> `Route::resource` per existing convention. Middleware stacks per §8.2.

### P5-T1 — Extend `UjianRequest` for `sub_jenis_ujian_id` (C-AU-2)

- **Goal:** Validate online sub-type field.
- **Change:** `app/Http/Requests/UjianRequest.php` — add
  `sub_jenis_ujian_id` → `['nullable','required_if:tipe_ujian,online_paket','prohibited_if:tipe_ujian,offline_kelas', Rule::exists('panritta_sub_jenis_ujian','id')]`
  + message. Keep existing rules intact (surgical).
- **Spec Ref:** §10 `StoreUjianRequest`, §4.5 (C-AU-2).
- **Tests:** `tests/Feature/Superadmin/UjianManagementTest.php` (extend) — online requires sub-type;
  offline forbids it.

### P5-T2 — Extend `PaketRequest` for unlimited-quota semantics (M-AU-4)

- **Goal:** Allow `kuota_ujian` null = unlimited; if provided, integer ≥ 1.
- **Change:** `app/Http/Requests/PaketRequest.php` — `kuota_ujian` →
  `['nullable','integer','min:1']` (+ message clarifying null = unlimited). Surgical.
- **Spec Ref:** §10 `StorePaketRequest`, AD-8 (M-AU-4).
- **Tests:** `tests/Feature/Superadmin/PaketManagementTest.php` (extend/create) — null accepted;
  0 rejected.

### P5-T3 — `SyncPaketUjianRequest` + `PaketUjianController@sync` (AD-5)

- **Goal:** Admin attaches online exams to a package (online-only guard).
- **Create:** `php artisan make:request SyncPaketUjianRequest --no-interaction`;
  `php artisan make:controller Superadmin/PaketUjianController --no-interaction`.
- **Change:** `routes/web.php` — `PUT paket/{paket}/ujian` under admin stack.
- **Spec Ref:** §9.1, §10, §4.1, AD-5.
- **Tests:** `tests/Feature/Superadmin/PaketUjianSyncControllerTest.php` — offline exam rejected;
  attach/detach persists to pivot.

### P5-T4 — Assembly endpoints wired to `ExamAssemblyService`

- **Goal:** Add-question / remaining-counter / activate flow (PRD §4.3.1).
- **Change:** `app/Http/Controllers/Superadmin/UjianSoalController.php` (exists) — delegate to
  `ExamAssemblyService`; add `remaining()` JSON endpoint. `Superadmin/UjianController@activate` calls
  `assertFinalizable`.
- **Routes:** `POST ujian/{ujian}/soal`, `DELETE ujian/{ujian}/soal/{soal}`,
  `GET ujian/{ujian}/remaining`, `POST ujian/{ujian}/activate`.
- **Spec Ref:** §6.1, §9.1.
- **Tests:** extend `tests/Feature/Superadmin/UjianSoalManagementTest.php` — remaining JSON; activate
  blocked when remaining > 0.

### P5-T5 — Offline participant admin CRUD (`PesertaOfflineController`)

- **Goal:** Admin manages offline participant master + credential export (C-4).
- **Create:** `php artisan make:controller Superadmin/PesertaOfflineController --no-interaction`;
  `php artisan make:request StorePesertaOfflineRequest --no-interaction`.
- **Routes:** `resource ujian.peserta-offline`; `GET ujian/{ujian}/peserta-offline/export`.
- **Spec Ref:** §9.1, §10, §6.4, C-4.
- **Tests:** `tests/Feature/Superadmin/PesertaOfflineManagementTest.php` — create returns plaintext
  once; unique `nomor_peserta` per exam; export renders credential sheet.

### P5-T6 — Offline participant login (`PesertaOfflineController@login`)

- **Goal:** Credential login → `AttemptService::startOffline` + set session keys (C-4/§8.3).
- **Create:** `php artisan make:request LoginPesertaOfflineRequest --no-interaction`.
- **Change:** `Peserta\` namespace controller method `login`; sets
  `offline_peserta_id`/`offline_ujian_id`/`offline_attempt_id` session keys.
- **Routes:** `POST ujian/offline/login` (no auth middleware).
- **Spec Ref:** §8.3, §9.2, §10.
- **Tests:** `tests/Feature/Peserta/OfflineLoginTest.php` — correct creds start attempt + session set;
  wrong `kode_akses` rejected.

### P5-T7 — Refactor `Peserta\UjianController` to services + dual-guard + snapshot deadline

- **Goal:** Replace inline logic with `AttemptService`; enforce dual-guard ownership (C-4) and
  snapshot `batas_waktu` checks (M-AU-8). This resolves CF-1 read assumptions (latest attempt).
- **Change:** `app/Http/Controllers/Peserta/UjianController.php` —
  - `start` → `AttemptService::start` (online) / token path unchanged for online exams; offline via
    P5-T6.
  - `saveAnswer` → add dual-guard (session vs `Auth::id()`); validate `now() < batas_waktu` using
    snapshot (M-AU-8); resolve attempt as latest `sedang_ujian` row (AD-9 — no longer unique).
  - `submit` → `AttemptService::submit`; clear offline session keys on offline submit (§8.3).
  - `hasil` → dual-guard; show only when `tampilkan_hasil` AND `selesai`.
  - Deadline helpers now read `peserta->batas_waktu` snapshot instead of recomputing from
    `durasi_ujian` (align with AD-10).
- **Spec Ref:** §6.3, §8.3, §9.2 (C-4, M-AU-8, AD-9/AD-10).
- **Tests:** extend `tests/Feature/Peserta/UjianEngineTest.php`; add
  `tests/Feature/Peserta/AccessIsolationTest.php` — participant A cannot access B's attempt (403);
  answer after `batas_waktu` rejected.

### P5-T8 — `StoreJawabanRequest` (extract answer validation, M-AU-8)

- **Goal:** Move answer validation into a Form Request reading snapshot `batas_waktu`.
- **Create:** `php artisan make:request StoreJawabanRequest --no-interaction`.
- **Spec Ref:** §10 `StoreJawabanRequest` (M-AU-8).
- **Tests:** covered by P5-T7 isolation/deadline tests.

### P5 — Macro Gate

- All `tests/Feature/Superadmin/*` and `tests/Feature/Peserta/*` green, including refactored
  `UjianEngineTest`. Any test previously asserting the dropped unique constraint (CF-1) updated with
  a comment referencing AD-9.

---

## P6 — Scheduled Jobs

> Console scheduling in Laravel 12 lives in `routes/console.php` / `bootstrap/app.php`. Jobs are
> `ShouldQueue` classes created via `php artisan make:job`. Register schedule frequencies per §11.

### P6-T1 — `AutoSubmitExpiredAttempts` job

- **Goal:** Finalize attempts past their snapshot deadline (AD-6/AD-10).
- **Create:** `php artisan make:job AutoSubmitExpiredAttempts --no-interaction`
- **Behavior:** Calls `AttemptService::autoSubmitExpired()`. Query filter `status='sedang_ujian'` and
  `batas_waktu <= now()` (single-source deadline, C-AU-6).
- **Schedule:** every minute (§11).
- **Spec Ref:** §11, §6.3, AD-6/AD-10.
- **Tests:** `tests/Feature/Jobs/AutoSubmitExpiredAttemptsTest.php` — expired attempt finalized once;
  non-expired untouched; already-`selesai` skipped; renewed subscription does NOT extend (C-AU-6).

### P6-T2 — `ExpireSubscriptions` job

- **Goal:** Flip `panritta_peserta_langganan.status` to `expired` when `berakhir_pada` passed.
- **Create:** `php artisan make:job ExpireSubscriptions --no-interaction`
- **Schedule:** hourly (§11).
- **Spec Ref:** §11.
- **Tests:** `tests/Feature/Jobs/ExpireSubscriptionsTest.php` — passed → expired; active window
  untouched.

### P6-T3 — Register schedule

- **Goal:** Wire both jobs into the scheduler.
- **Change:** `routes/console.php` (or `bootstrap/app.php` `->withSchedule(...)`) —
  `everyMinute()` and `hourly()`.
- **Spec Ref:** §11.
- **Tests:** schedule assertion optional; job tests above are authoritative.

### P6 — Macro Gate

- All `tests/Feature/Jobs/*` green.

---

## P7 — Macro Regression & Traceability Closure

### P7-T1 — Full suite green

- **Goal:** Zero failures across the entire suite (macro mandate).
- **Command:** `php artisan test --compact` (or `./vendor/bin/pest`).
- **Exit condition:** all green; no skipped exam-domain tests.

### P7-T2 — Spec traceability closure

- **Goal:** Verify every Tech Spec §12 test-focus row has a corresponding implemented test.
- **Action:** Cross-check the matrix in §7 below; mark any gap and add the missing test.

### P7-T3 — Housekeeping (Spec §15 item 1)

- **Goal:** Update `ai/AGENTS.md` Tech Stack Rules line `Laravel 11` → `Laravel 12 (PHP 8.2+)`.
- **Note:** This is a documentation edit flagged by Spec C-1; do only if user approves (out of code
  scope but tracked here).

### P7 — Macro Gate (Phase Exit for Code)

- Full suite green; traceability matrix fully covered; `vendor/bin/pint --format agent` clean.

---

## 3. Task Dependency Order

```
P1 (schema) ─▶ P2 (models) ─▶ P3 (services) ─┬─▶ P4 (offline auth) ─▶ P5 (HTTP) ─▶ P6 (jobs) ─▶ P7
                                              └────────────────────────────────────▲
P3-T3 AttemptService is a prerequisite for P5-T7 and P6-T1.
P1-T2 (drop unique) MUST precede P5-T7 (latest-attempt reads) and P3-T3 (re-take).
P1-T4 (pivot) MUST precede P3-T7 and P5-T3.
```

---

## 4. Spec → Plan Traceability

| Tech Spec Item | Plan Task(s) |
| -------------- | ------------ |
| §4.5 `sub_jenis_ujian_id` (AD-7/C-AU-2) | P1-T1, P2-T4, P5-T1 |
| §4.4 re-take, no unique (AD-9/C-AU-2) | P1-T2, P3-T3, P5-T7 |
| §4.2 lifecycle fields (AD-10) | P1-T3, P2-T5, P3-T3 |
| §4.1 pivot `panritta_paket_ujian` (AD-5) | P1-T4, P2-T1, P3-T7, P5-T3 |
| §4.3 per-category result (AD-4) | P1-T5, P2-T2, P3-T2 |
| §4.4 offline master (C-4) | P1-T6, P2-T3, P3-T4, P5-T5 |
| §6.1 `ExamAssemblyService` | P3-T1, P5-T4 |
| §6.2 scoring persist (C-2/C-3/AD-4) | P3-T2 |
| §6.3 `AttemptService` snapshot deadline (C-AU-1/5/6, AD-10) | P3-T3, P6-T1 |
| §6.3 quota unlimited (M-AU-4/AD-8) | P3-T6, P5-T2 |
| §6.4 `OfflineParticipantService` | P3-T4, P5-T5 |
| §6.5 `TokenService` | P3-T5 |
| §8.3 offline session + middleware (C-AU-2) | P4-T1, P4-T2, P5-T6, P5-T7 |
| §9 route surface | P5-T3..T8 |
| §10 form requests | P5-T1, P5-T2, P5-T3, P5-T5, P5-T6, P5-T8 |
| §11 scheduled jobs | P6-T1, P6-T2, P6-T3 |
| §12 testing strategy | test items across every P*-T* + P7-T1/T2 |

---

## 5. Risk Register

| # | Risk | Impact | Mitigation (in plan) |
| - | ---- | ------ | -------------------- |
| R-1 | Dropping `ujian_peserta_unique` breaks code that assumes one attempt per pair | Med | P5-T7 refactors reads to "latest active attempt"; CF-1 flagged; tests updated not deleted |
| R-2 | `akses_member` json vs new pivot dual-source drift | Med | P3-T7 non-destructive; reads use pivot; column kept nullable one release |
| R-3 | Existing `UjianScoringService::finalize` signature reused by controller/tests | Low | P3-T2 preserves signature; only adds internal persistence |
| R-4 | Snapshot `batas_waktu` vs existing controller recompute-from-duration | Med | P5-T7 switches deadline reads to snapshot (AD-10); regression test added |
| R-5 | Offline session isolation regressions on shared routes | High | P4 middleware + P5-T7 dual-guard + `AccessIsolationTest` |
| R-6 | Quota race on concurrent starts | High | P3-T3 `lockForUpdate` in transaction; concurrency test |

---

## 6. Out of Scope (Deferred)

Per PRD §6 and Spec §14 — not planned here: payment gateway internals, certificate generation,
analytics, proctoring UX, offline bulk import UI polish, multi-sub-type online exams (L-AU-9),
late-tolerance behavior (Spec §14 #1), post-attempt exam edit rules (Spec §14 #2).

---

## 7. Next Steps

1. Run `/sdlc-clarify-reqs` against this Plan to interrogate task granularity and hidden
   assumptions (especially CF-1/CF-2 and R-1/R-4).
2. Run `/sdlc-audit-consistency` (PRD + Spec + Plan) for full three-way traceability.
3. Proceed to `/sdlc-write-code` starting at **P1-T1**, honoring the two-layer testing mandate
   (micro test per task, macro gate per phase).

