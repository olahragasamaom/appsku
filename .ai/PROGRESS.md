<!-- markdownlint-disable -->
# PROGRESS

> Short running log of the latest working session progress and the next planned tasks.
> Conversational scope is Indonesian, but this documentation artifact follows the
> English-only documentation convention defined in `ai/AGENTS.md`.

**Last Updated:** 2026-08-10

---

## Latest Session Summary

The work completed in the latest session is **ad-hoc / learning-oriented** and is intentionally
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
