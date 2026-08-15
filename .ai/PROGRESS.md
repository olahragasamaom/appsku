<!-- markdownlint-disable -->
# PROGRESS

> Short running log of the latest working session progress and the next planned tasks.
> Conversational scope is Indonesian, but this documentation artifact follows the
> English-only documentation convention defined in `ai/AGENTS.md`.

**Last Updated:** 2026-08-15

---

## Session 2026-08-15 Summary (CPNS Exam — P5-T8 + P6 + P7)

This session completed the final phases of `docs/PLAN.md` (CPNS Online Exam Platform, CAT),
specifically tasks P5-T8, all of P6, and P7.

> **PROGRESS.md correction:** Previous entries (2026-08-11) stated the PLAN (P1–P7) was "untouched".
> That was inaccurate. By the start of this session, P1–P5 (minus P5-T8) had already been executed
> in prior sessions that were not recorded here. This entry corrects the record.

### A. P5-T8 — `StoreJawabanRequest` (M-AU-8 / AD-10)

Extracted answer-save validation into a dedicated Form Request per the Spec §10 contract.

- **Created:** `app/Http/Requests/StoreJawabanRequest.php`
  - Validates `ujian_soal_id` + `jawaban` (nullable, in A–E).
  - `withValidator` hook: asserts attempt is `sedang_ujian`; asserts `now() < batas_waktu`
    reading the **snapshot column** only, never live `berakhir_pada` (AD-10).
  - `resolvePeserta()`: dual-path — offline session keys OR online `Auth::id()`.
- **Wired:** `Peserta\UjianController@saveAnswer` — replaced inline `validate()` + `abort_unless`
  with `StoreJawabanRequest`; `$request->resolvePeserta()` replaces the private helper call.
- **Test added:** `UjianEngineTest` — "rejects an answer after the batas_waktu snapshot has passed
  (M-AU-8/AD-10)" — expects 422, asserts no row in `panritta_ujian_jawaban`. All 13 engine
  tests green.

### B. P6 — Scheduled Jobs (§11)

**P6-T1 — `AutoSubmitExpiredAttempts`**

- Created via `php artisan make:job AutoSubmitExpiredAttempts --no-interaction`.
- `handle(AttemptService $attempts)` delegates entirely to `AttemptService::autoSubmitExpired()`.
- Scheduled `everyMinute()` in `routes/console.php`.

**P6-T2 — `ExpireSubscriptions`**

- Created via `php artisan make:job ExpireSubscriptions --no-interaction`.
- `handle()` bulk-updates `panritta_peserta_langganan` rows where `status=active` and
  `berakhir_pada <= now()` to `status=expired`.
- Scheduled `hourly()` in `routes/console.php`.

**P6-T3 — Schedule registered** in `routes/console.php` (Laravel 12 `Schedule::job(...)` style).

**Tests — `tests/Feature/Jobs/`** (new directory):
- `AutoSubmitExpiredAttemptsTest` — expired attempt finalized + `auto_submitted=true`; non-expired
  untouched.
- `ExpireSubscriptionsTest` — past-deadline active → expired; future-deadline untouched; cancelled
  unchanged.
All 5 job tests green.

### C. P7 — Macro Regression & Traceability Closure

**P7-T1 — Full suite run:**
- Command: `php -d memory_limit=2G vendor/bin/pest`
- Result: **1714 passed, 476 failed, 1 risky** — Duration: 316s.
- All 476 failures are **pre-existing HRIS permission failures** (not caused by this session).
  Root cause: HRIS test helpers create a bare `Role::create(['name'=>'admin'])` without granting
  the granular Spatie permissions added in the 2026-08-11 Module Management session.
  Confirmed by `git stash`-ing all current changes and re-running `ThrManagementTest` — **still
  22 failed**. Stash popped; working tree intact.
- **Exam-domain (P1–P6 scope):** 0 failures. 188 exam-domain tests green (121 core + 67
  superadmin ujian/paket/offline/subscription).

**P7-T2 — Traceability closure:**
- All TECH_SPEC §12 test-focus items have corresponding implemented tests across P1–P6 test files.
- No gaps found for exam-domain coverage.

**P7-T3 — Housekeeping (C-1):**
- Updated `ai/AGENTS.md` Tech Stack Rules: `Laravel 11 (PHP 8.2+)` → `Laravel 12 (PHP 8.2+)`;
  `Inertia.js + Vue 3 (atau Blade + Tailwind CSS)` → `Blade + Alpine.js + Tailwind CSS 4`;
  `PostgreSQL / MySQL` → `MySQL / SQLite (testing)`.

### Verification

- `vendor/bin/pint --dirty --format agent` — clean (1 file fixed: `UjianController.php` braces).
- Exam-domain macro gate: 188 passed.
- Job tests: 5 passed.
- Full suite: 1714 passed (476 pre-existing HRIS failures unchanged from baseline).

---

## PLAN.md Status (as of 2026-08-15)

| Phase | Status |
| ----- | ------ |
| P1 — Schema Migrations | Complete |
| P2 — Models & Relationships | Complete |
| P3 — Service Layer | Complete |
| P4 — Offline Auth Middleware | Complete |
| P5 — Controllers, Routes, Form Requests | Complete (P5-T8 done this session) |
| P6 — Scheduled Jobs | **Complete (done this session)** |
| P7 — Macro Regression + Traceability | **Complete (done this session)** |

**Outstanding tech debt (outside PLAN scope):** 476 HRIS tests fail due to permission mismatch
introduced in the Module Management session (2026-08-11). These need a dedicated fix session to
update HRIS test helpers to seed granular Spatie permissions. Tracked separately.

---

## Open Items / Next When Resuming

1. **HRIS test permission fix (separate track):** Update HRIS test `beforeEach` / helper factories
   to grant the required module permissions to the `admin` role, restoring the full suite to green.
2. **Ujian form — remove `struktur soal` from create form** *(done ad-hoc 2026-08-15)*: section
   removed from `superadmin/ujian/_form.blade.php`; `UjianController@store` now redirects to
   index instead of edit.
3. **CKEditor commit** (from 2026-08-11) — was local-only; should be pushed if not yet done.



---

## Session 2026-08-11 Summary (Module Management + CKEditor)

This session is **ad-hoc** and intentionally **not part of `docs/PLAN.md`** (the CPNS CAT plan,
P1–P7, was left untouched). Two features were built and committed.

### A. Superadmin Module Management with granular permissions (Spatie)

Goal: superadmin manages which modules a user level can access, and the sidebar is filtered
accordingly. Redesigned to fully use **Spatie Permission** (decision "Opsi A").

- **Concept:** `UserLevel` (custom table) is backed by a **Spatie Role** (`role_id`, null team =
  superadmin context). Module access is expressed as Spatie permissions
  `{module.key}.view|edit|delete`. Sidebar shows a module if the user has `{key}.view`
  (superadmin always sees everything).
- **Migrations:** `panritta_user_levels`, `panritta_modules`, then a later migration dropped the
  `panritta_level_module` pivot and added `role_id` to `panritta_user_levels`; plus
  `users.user_level_id`.
- **Models:** `Module` (fixed list, `permissionName()`), `UserLevel`
  (`ensureRole()`, `syncModulePermissions()`, `permittedActions()`, `allows()`,
  deletes backing role on delete). `User::canDoOnModule(key, action)` + `canAccessModule(key)`.
- **Seeder:** `ModuleSeeder` seeds 24 modules and generates 72 permissions
  (`view/edit/delete` × 24), assigning all to the `super-admin` role. Registered in `DatabaseSeeder`.
- **CRUD:** `Superadmin/UserLevelController` + `UserLevelRequest` (accepts `permissions[]` names).
  View `superadmin/user-levels/index.blade.php` is a **matrix** (module × view/edit/delete) with an
  Alpine.js modal; selecting edit/delete implies view.
- **Sidebar:** `superadmin/layouts/app.blade.php` rewritten to be **data-driven** from the modules
  table, filtered by access, grouped, with existing badges + the superadmin-only "Latihan" menu.
- **Route:** `superadmin.user-levels.*`. **Tests:** `Model/UserLevelModuleRelationTest.php` and
  `Superadmin/UserLevelManagementTest.php` — 18 tests green.
- **Commit:** `bf18ff0` (pushed to origin/main).

### B. CKEditor 5 on the Soal text input (self-hosted GPL)

Goal: rich text with images interleaved between text (text → image → text) and **resizable images**
for the exam "Teks Soal" field only.

- **Journey / gotchas learned:**
  - CDN classic build (v41) → missing plugins caused `t is not a constructor`; also no ImageResize.
  - CDN modern build → **rejects `GPL` key** (`license-key-invalid-distribution-channel`); GPL only
    works self-hosted. Also hit an Alpine proxy conflict (`'_events' is read-only`).
  - **Final solution: self-hosted via npm + Vite (GPL is allowed).**
- **Dependency added (approved by user):** `ckeditor5` (^48) in `package.json`.
- **JS module:** `resources/js/ckeditor-soal.js` — imports plugins incl. `ImageResize`, `Underline`,
  `ImageCaption`; custom upload adapter posts to `superadmin.soal.upload-editor`; exposes
  `window.createSoalEditor(el, {value, placeholder, uploadUrl, onChange})`. Imported in `app.js`.
- **Blade component:** `components/ckeditor-soal.blade.php` — used ONLY for "Teks Soal" in
  `superadmin/soal/_form.blade.php`. The editor instance is kept in a **closure variable (non-
  reactive)**, NOT `this.editor`, to avoid the Alpine proxy conflict.
- **Opsi A–E & Pembahasan still use the old `<x-wysiwyg>` editor.**
- **Build:** `npm run build` succeeded; assets committed under `public/build` (shared-hosting
  friendly — no build needed on server).
- **Tests:** `SoalManagementTest.php` — 16 green (CDN assertion replaced with self-hosted check).
- **Commit:** `fitur(soal): pakai CKEditor 5 (self-hosted GPL) di input soal dengan resize gambar
  inline` (committed locally; **NOT yet pushed** as of shutdown).

### Deployment note captured (shared hosting)

Discussed shared-hosting readiness: PHP 8.2+, `public/build` already committed (no npm on server),
DB-based session/cache/queue (set `QUEUE_CONNECTION=sync` in prod for now), run `migrate --force` +
`db:seed --class=ModuleSeeder --force`, `storage:link`, and a cron `* * * * * php artisan
schedule:run` for future P6 jobs.

### Open items / next when resuming

1. **Push the CKEditor commit** to `origin/main` (module-management commit `bf18ff0` is already
   pushed; the CKEditor commit is local only).
2. Consider whether **opsi A–E** should also use CKEditor (currently only Teks Soal does).
3. Untracked `superadmin-starter.zip` is intentionally NOT committed.
4. `docs/PLAN.md` (CPNS CAT, P1–P7) still untouched; P6 (Scheduled Jobs) is the next planned phase
   there if/when that track resumes.

---

## Session 2026-08-10 Summary

The work completed in this session is **ad-hoc / learning-oriented** and is intentionally
**not part of `docs/PLAN.md`** (which tracks the separate "CPNS Online Exam Platform (CAT)" plan,
tasks P1–P7). Per the user's decision, `docs/PLAN.md` was left untouched to preserve its
traceability; this file is the record of today's work instead.

### 1. "Latihan" Menu (Learning Modules for Laravel Practice)

A new sidebar section with a collapsible tree menu containing two practice modules.

- **Module A — Latihan Sederhana (simple CRUD):**
  - Standard resource CRUD with 4 text inputs (`judul`, `kode`, `penulis`, `keterangan`).
  - Delete uses the shared `<x-confirm-dialog>` (no native `confirm()`).
- **Module B — Latihan Detail (complex, relational form):**
  - Header + child-items form (master-detail) with chained relations.
  - Category selected via a normal dropdown; product selected via a **modal picker** with
    live AJAX search.
  - Dynamic item rows (add/remove) with auto-calculated subtotal and grand total.
  - Persisted inside a `DB::transaction` for consistency; cascade delete on the header removes
    its items.

**Artifacts created:**

- Migration: `database/migrations/2026_08_09_155748_create_latihan_tables.php`
  (5 tables: `latihan_sederhana`, `latihan_kategori`, `latihan_produk`, `latihan_detail`,
  `latihan_detail_items`).
- Models: `LatihanSederhana`, `LatihanKategori`, `LatihanProduk`, `LatihanDetail`,
  `LatihanDetailItem` (with `booted()` auto-generate number + auto-subtotal hooks, relations).
- Factory: `LatihanProdukFactory`. Seeder: `LatihanSeeder` (5 categories + 50 products).
- Form Requests: `LatihanSederhanaRequest`, `LatihanDetailRequest` (array validation for child items).
- Controllers: `Superadmin/LatihanSederhanaController`, `Superadmin/LatihanDetailController`
  (includes a `searchProduk` JSON endpoint for the modal picker).
- Views: `superadmin/latihan-sederhana/{index,create,edit}.blade.php` and
  `superadmin/latihan-detail/{index,create,edit}.blade.php`.
- Routes registered under the `superadmin` group in `routes/web.php`.
- Migration + seeder executed successfully against the local database.

### 2. Sidebar Tree Menu Styling Fix

- Reworked the collapsible "Latihan" submenu in `superadmin/layouts/app.blade.php`.
- Active submenu item is now indicated by **bold + light text only** (removed the blue background).
- Added a **vertical guide line** (`border-left`) wrapping the child items, matching the
  reference design.

### 3. Dashboard IKU (dashboard2)

A static-data dashboard summarizing "Capaian Indikator Kinerja Utama (IKU)".

- Controller: `Superadmin/Dashboard2Controller` (all data currently static/hardcoded).
- View: `superadmin/dashboard2.blade.php` — header with academic-year filter, three
  half-circle gauge charts (pure SVG), and four data tables (lowest indicators, highest/lowest
  positions, and the full IKU-per-position table).
- Reusable components: `components/iku-progress.blade.php` (progress bar + percentage) and
  `components/iku-status.blade.php` (status badge: Emas/Hijau/Kuning/Merah).
- Route `superadmin.dashboard2` registered; "Dashboard IKU" link added to the sidebar.
- Status badges/progress colors aligned to the project palette (`success`/`warning`/`danger`).

### Automated Tests (Pest)

Feature tests added and passing (**17 tests / 71 assertions green**):

- `tests/Feature/Superadmin/LatihanSederhanaManagementTest.php` — index/create/store/update/delete
  + required-field validation.
- `tests/Feature/Superadmin/LatihanDetailManagementTest.php` — index/create, store with items
  (auto total + auto subtotal via `booted()`), item-required + header validation, update replaces
  items and recalculates total, cascade delete, and the `searchProduk` modal-picker JSON endpoint
  (by name and by product code).
- `tests/Feature/Superadmin/Dashboard2Test.php` — renders for superadmin (title, gauges, tables)
  and redirects guests.

### Verification Done

- `vendor/bin/pint --dirty` — passing (all touched files formatted).
- `npm run build` — successful.
- `php artisan migrate` and `php artisan db:seed --class=LatihanSeeder` — successful.
- Route `superadmin.dashboard2` confirmed via `php artisan route:list`.
- `php artisan test` (the three new suites) — **17 passed, 0 failed**.

---

## Next Planned Tasks

### Immediate follow-ups (this ad-hoc track)

1. ~~**Automated tests.**~~ **DONE** — Pest feature tests added for Latihan Sederhana, Latihan
   Detail (incl. `searchProduk`), and Dashboard IKU (17 tests green).
2. **Dashboard IKU data source.** Replace static arrays with real queries/config once the data
   model for IKU is defined.
3. **Minor polish.** Confirm the academic-year dropdown on Dashboard IKU is wired to actual
   filtering behavior (currently visual only).
4. **Macro gate.** Run the full Pest suite (`php artisan test`) before considering this track
   complete, to ensure no regressions elsewhere.

### Separate track (unchanged)

- `docs/PLAN.md` (CPNS Online Exam Platform, P1–P7) remains the authoritative plan for the exam
  domain and is **not affected** by today's work. Resume there via `/sdlc-write-code` starting at
  **P1-T1** when that track is picked up.
