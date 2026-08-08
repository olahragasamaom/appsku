<!-- markdownlint-disable -->
# Technical Specification (TECH_SPEC) — CPNS Online Exam (CAT)

**Document Name:** Technical Specification — HRIS to CPNS Online Exam Platform (CAT)
**Backend:** Laravel 12.x (PHP 8.2+) — verified against `composer.json` (`laravel/framework: ^12.0`, `php: ^8.2`)
**Frontend:** Blade + Alpine.js + Tailwind CSS 4
**Status:** Draft (Phase Spec) — Remediation Iteration for Critical Findings C-1..C-4, C-AU-1, C-AU-2, M-AU-4, C-AU-5, C-AU-6, M-AU-7, M-AU-8, L-AU-9
**Author:** Architect (SDLC `/sdlc-define-specs`)
**Upstream:** `docs/PRD.md` (Readiness Score 84/100, Iteration 2)

> **C-1 Resolution:** Framework version is authoritatively pinned to **Laravel 12 + PHP 8.2+** by
> reading `composer.json` (Codebase Reality Check). The note in `ai/AGENTS.md` that mentions
> "Laravel 11 / PHP 8.2+" is a stale rule and MUST be updated in a follow-up housekeeping task; the
> Spec follows the actual codebase to prevent misleading downstream Plan/Code phases.

---

## 1. Purpose & Scope

This document translates the approved PRD into a definitive technical blueprint: data models,
schema deltas, service contracts, and controller/route surfaces. It aligns the PRD behavior with the
**existing** `panritta_*` exam schema already present in the codebase.

**In scope:** Exam Type, Question, Exam (offline/online composite), Member Package, subscription
quota/expiry, scoring & pass evaluation, question-assembly flow.

**Out of scope (per PRD §6):** Payment gateway internals, certificate generation, analytics,
proctoring UX, offline participant bulk import.

---

## 2. Architectural Decisions (Traceable to PRD)

| ID | PRD Ref | Decision |
| -- | ------- | -------- |
| **AD-1** | §1.1 | **Single-tenant.** No `company_id` scoping is added to any `panritta_*` table. Access control relies on Spatie roles only. |
| **AD-2** | §4.3.3 | Exam↔question uses the existing `panritta_ujian_soal` relational table. **No `list_soal` string.** |
| **AD-3** | §4.3 | An exam is a **composite** of categories via `panritta_ujian_jenis_ujian`, each row holding a per-category `passing_grade`. |
| **AD-4** | §4.3 | **Pass = all categories pass.** `lulus = true` only if every category's category-score ≥ its `passing_grade`. |
| **AD-5** | §4.4 | Package↔online-exam link needs a **new pivot** `panritta_paket_ujian` (current schema lacks it). |
| **AD-6** | §4.4 | Quota decrements **per attempt at start**; expiry triggers **auto-submit**. |
| **AD-7** | §4.3.2 | Online exam carries a `sub_jenis_ujian_id` FK to scope its question bank; offline exam uses composite `ujian_jenis_ujian` categories only. `null` on offline. (C-AU-2) |
| **AD-8** | §4.4 | `kuota_ujian = null` on a package means **unlimited attempts**; `sisa_kuota_ujian = null` on the subscription row signals this and bypasses the quota check in `start()`. (M-AU-4) |
| **AD-9** | §4.4 | `panritta_ujian_peserta` has **no unique constraint** on `(ujian_id, user_id)` — multiple rows (re-takes) are allowed. Each re-take is a new row; each decrements quota. (C-AU-2) |
| **AD-10** | §4.4 | **`batas_waktu` is the single source of deadline truth**, snapshot-frozen at `start()` for both exam types (offline = duration; online = subscription `berakhir_pada` at start). Auto-submit and answer validation read `batas_waktu` only, never live `berakhir_pada`. (C-AU-1/C-AU-5/C-AU-6) |

> **ADR Note:** Per `.agents/standards` Triple-Gate, AD-1 and AD-5 are candidates for `docs/adr/`
> (hard to reverse, non-obvious, real trade-off). ADR authoring is deferred to the standards folder
> when it exists; recorded here as interim.

---

## 3. Existing Schema Baseline (As-Is)

Confirmed by migration inspection. Prefix: `panritta_`.

| Table | Key Columns |
| ----- | ----------- |
| `panritta_jenis_ujian` | `id`, `nama_jenis_ujian` — **no scoring columns** (scoring is per sub-category, see below) |
| `panritta_sub_jenis_ujian` | `id`, `jenis_ujian_id`, `nama_sub_jenis_ujian`, `sistem_penilaian` enum(`benar_salah`\|`tiap_jawaban_ada_poin`) default `benar_salah`, `jumlah_jawaban_pilihan_ganda` smallint default 5, `nilai_benar` decimal(5,2) default 5.00, `keterangan` text nullable, `urutan` int default 0 |
| `panritta_sub_indikator` | `id`, `sub_jenis_ujian_id` |
| `panritta_soal` | `id`, `sub_indikator_id`, `soal` (longText), `gambar_soal` nullable, `opsi_a..d` text, `opsi_e` nullable, `gambar_opsi_a..e` nullable, `kunci_jawaban` enum(A..E) nullable, `nilai_bobot_benar` decimal(5,2) nullable *(overrides `nilai_benar` if set)*, `nilai_bobot_a..e` decimal(5,2) nullable *(used when `sistem_penilaian=tiap_jawaban_ada_poin`)*, `pembahasan`, `gambar_pembahasan`, `pembuat_soal_id` |
| `panritta_ujian` | `id`, `nama_ujian`, `tipe_ujian` enum(`offline_kelas`\|`online_paket`) default `offline_kelas`, `jumlah_soal` smallint unsigned default 0, `sub_jenis_ujian_id` nullable FK → `panritta_sub_jenis_ujian.id` *(online exam only — PRD §4.3.2; null for offline_kelas)*, `acak_soal` boolean default **false**, `tampilkan_hasil` boolean default true, `tanggal_ujian` dateTime nullable, `durasi_ujian` uint nullable *(minutes)*, `batas_keterlambatan` dateTime nullable, `token_ujian` varchar(50) nullable, `akses_member` json nullable, `status` enum(`draft`\|`aktif`\|`selesai`) default `draft`, `dibuat_oleh` FK users |
| `panritta_ujian_jenis_ujian` | `ujian_id`, `jenis_ujian_id`, `passing_grade` decimal(6,2) |
| `panritta_ujian_soal` | `ujian_id`, `soal_id`, `jenis_ujian_id`, `urutan`; unique(`ujian_id`,`soal_id`) |
| `panritta_ujian_peserta` | `id`, `ujian_id`, `user_id`, `status` enum(`terdaftar`\|`diblokir`\|`sedang_ujian`\|`selesai`) default `terdaftar`, `waktu_mulai`, `waktu_selesai`, `total_nilai`, `lulus` |
| `panritta_ujian_jawaban` | `id`, `ujian_peserta_id`, `ujian_soal_id`, `soal_id`, `jenis_ujian_id`, `jawaban` enum(A..E) nullable, `nilai` decimal(6,2) default 0, `benar` boolean nullable |
| `panritta_paket` | `id`, `nama_paket`, `slug`, `harga`, `durasi_hari`, `kuota_ujian`, feature flags, `is_active`, `urutan` |
| `panritta_peserta_langganan` | `id`, `user_id`, `paket_id`, `status` enum(`pending`\|`active`\|`expired`\|`cancelled`), `mulai_pada`, `berakhir_pada`, `sisa_kuota_ujian` |

> **C-3 Correction:** Scoring mode is determined at the **`panritta_sub_jenis_ujian`** level via
> `sistem_penilaian` + `nilai_benar`. The Spec previously implied scoring mode was on the question
> itself; this was incorrect. See §6.2 for the corrected `ExamScoringService` contract.

---

## 4. Schema Deltas (To-Be)

Additive, backward-compatible migrations. No destructive changes to HRIS tables (AD-1).

### 4.1. New Table — `panritta_paket_ujian` (AD-5)

Links a package to the **online** exams it grants access to.

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint PK | |
| `paket_id` | FK → `panritta_paket.id` | cascade on delete |
| `ujian_id` | FK → `panritta_ujian.id` | cascade on delete |
| `timestamps` | | |

- `unique(paket_id, ujian_id)`.
- Application guard: only `ujian` with `tipe_ujian = 'online_paket'` may be attached (PRD §4.4).

### 4.2. Attempt Lifecycle Fields — `panritta_ujian_peserta`

To support auto-submit (AD-6) and quota linkage:

| New Column | Type | Notes |
| ---------- | ---- | ----- |
| `langganan_id` | nullable FK → `panritta_peserta_langganan.id` | records which subscription funded this attempt (online only) |
| `batas_waktu` | nullable dateTime | computed hard deadline: **offline** = `waktu_mulai` + `durasi_ujian` (minutes cast to interval); **online** = the subscription's `berakhir_pada` at the moment `start()` is called — snapshot-copied so that subsequent subscription updates do not shift the deadline mid-attempt (C-AU-1) |
| `auto_submitted` | boolean default false | true when finalized by the expiry job/guard |

> Existing `status` enum already includes `sedang_ujian` and `selesai`; reused as-is.

### 4.3. Per-Category Result — `panritta_ujian_peserta_kategori` (new)

Stores the category-level score used for AD-4 pass evaluation and result display.

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint PK | |
| `ujian_peserta_id` | FK → `panritta_ujian_peserta.id` | cascade |
| `jenis_ujian_id` | FK → `panritta_jenis_ujian.id` | cascade |
| `nilai_kategori` | decimal(8,2) default 0 | summed from `panritta_ujian_jawaban.nilai` for this category |
| `passing_grade` | decimal(6,2) nullable | snapshot copied from `panritta_ujian_jenis_ujian` at start |
| `lulus_kategori` | boolean nullable | `nilai_kategori >= passing_grade` |
| `timestamps` | | |

- `unique(ujian_peserta_id, jenis_ujian_id)`.

### 4.4. Offline Participant Master — `panritta_peserta_offline` (new, C-4)

Resolves the offline participant registration flow (PRD §5). Offline participants do NOT use the
standard user registration/Google OAuth flow. An admin creates participant records for a specific
exam; the generated `kode_akses` is printed and distributed in the exam room as the participant's
one-time password.

| Column | Type | Notes |
| ------ | ---- | ----- |
| `id` | bigint PK | |
| `ujian_id` | FK → `panritta_ujian.id` | cascade on delete; must be `tipe_ujian = offline_kelas` |
| `nomor_peserta` | varchar(50) | exam-room seat or participant number; used as login identifier |
| `nama_peserta` | varchar(255) | participant's full name |
| `kode_akses` | varchar(255) | bcrypt hash of the one-time access code assigned by admin |
| `ujian_peserta_id` | nullable FK → `panritta_ujian_peserta.id` | set when participant logs in and starts |
| `timestamps` | | |

- `unique(ujian_id, nomor_peserta)`.
- Application guard: `ujian_id` must belong to an `offline_kelas` exam.
- `nomor_peserta` + `kode_akses` (plaintext given to participant) is the login credential pair for
  the offline exam session. This credential is **independent of the `users` table**.
- On offline exam login, the system verifies `kode_akses` via `Hash::check()` (Laravel 12 bcrypt).
- After successful login, the participant session is scoped to the specific `ujian_id`.

### 4.5. Online Exam Sub-Type Field — `panritta_ujian` (C-AU-2)

PRD §4.3.2 lists **Sub exam type** as a required configuration field for online exams. The existing
`panritta_ujian` table lacks this column. A nullable FK column is added:

| Column | Type | Notes |
| ------ | ---- | ----- |
| `sub_jenis_ujian_id` | nullable FK → `panritta_sub_jenis_ujian.id` | set on delete null; required when `tipe_ujian = online_paket`; must be null when `tipe_ujian = offline_kelas` |

- Application guard in `StoreUjianRequest`: `sub_jenis_ujian_id` is `required_if:tipe_ujian,online_paket` and `prohibited_if:tipe_ujian,offline_kelas`.
- The `sub_jenis_ujian_id` is separate from the composite `ujian_jenis_ujian` categories (AD-3); it
  scopes the question bank filter when assembling an online exam's question set.
- `[Assumed: online exam = single sub-type per exam]` — PRD §4.3.2 names "Sub exam type"
  (singular), so an online exam is scoped to **exactly one** `sub_jenis_ujian_id`. Offline exams
  (PRD §4.3.1) may draw from **multiple** sub-types via the assembly flow. If multi-sub-type online
  exams are needed later, this FK would be promoted to a pivot — deferred to backlog (§14).

---

## 5. Domain Models & Relationships

### 5.1. New Models

- **`PaketUjian`** (`panritta_paket_ujian`) — or expose via `Paket::ujians()` belongsToMany.
- **`UjianPesertaKategori`** (`panritta_ujian_peserta_kategori`).
- **`PesertaOffline`** (`panritta_peserta_offline`) — admin-created offline participant record (C-4).

### 5.2. Relationship Additions

```text
Paket          belongsToMany Ujian           (panritta_paket_ujian)       [online only]
Ujian          belongsToMany Paket           (panritta_paket_ujian)
Ujian          hasMany       PesertaOffline  (panritta_peserta_offline)   [offline only]
UjianPeserta   hasMany       UjianPesertaKategori
UjianPeserta   belongsTo     PesertaLangganan (langganan_id, nullable)
PesertaOffline belongsTo     Ujian
PesertaOffline hasOne        UjianPeserta    (via ujian_peserta_id)
SubJenisUjian  hasMany       SubIndikator
SubJenisUjian  belongsTo     JenisUjian       [scoring mode inherited from SubJenisUjian]
```

> **C-3 Note:** `SubJenisUjian::$sistem_penilaian` is the authoritative scoring mode discriminator.
> All scoring logic reads this field via the sub-category chain:
> `soal → sub_indikator → sub_jenis_ujian → sistem_penilaian / nilai_benar`.

Existing relations in `Ujian` (`jenisUjians` withPivot `passing_grade`, `soals` withPivot
`jenis_ujian_id`,`urutan`, `peserta`) are **reused unchanged**.

---

## 6. Core Service Contracts

Services are the single source of business logic (thin controllers).

### 6.1. `ExamAssemblyService` (PRD §4.3.1 — C1)

Capacity-first question assembly with live remaining counter.

| Method | Signature (conceptual) | Behavior |
| ------ | ---------------------- | -------- |
| `remainingSlots` | `(Ujian $ujian): int` | `jumlah_soal - count(ujian_soal)`; drives the "Remaining questions to add: N" indicator |
| `addQuestions` | `(Ujian $ujian, int $jenisUjianId, array $soalIds): void` | Appends questions for the clicked sub-type; sets `jenis_ujian_id` + next `urutan`; rejects if it would exceed `jumlah_soal` |
| `removeQuestion` | `(Ujian $ujian, int $soalId): void` | Detaches one question |
| `assertFinalizable` | `(Ujian $ujian): void` | Throws if `remainingSlots > 0` (blocks activation) |

**Edge case (R4 — pool shortage):** If the aggregate available bank for selected sub-types
`< jumlah_soal`, `assertFinalizable` surfaces a validation error naming the deficit. The exam stays
in `draft`; it cannot be set to `aktif`.

### 6.2. `ExamScoringService` (PRD §4.3 — C2/C3/AD-4)

#### Scoring Mode Resolution (C-3)

The scoring mode for a question is resolved by traversing the sub-category chain at runtime:

```
panritta_ujian_jawaban.soal_id
  → panritta_soal.sub_indikator_id
    → panritta_sub_indikator.sub_jenis_ujian_id
      → panritta_sub_jenis_ujian.sistem_penilaian   -- 'benar_salah' OR 'tiap_jawaban_ada_poin'
      → panritta_sub_jenis_ujian.nilai_benar         -- used when sistem_penilaian = 'benar_salah'
      → panritta_sub_jenis_ujian.jumlah_jawaban_pilihan_ganda
```

> **Performance note (Laravel 12):** This chain MUST be eager-loaded in a single query when
> assembling the scoring payload to prevent N+1 queries. Use nested `with()` on the attempt's
> questions before calling `scoreAnswer`.

#### CPNS Composite Scoring Rules (C-2)

The standard CPNS SKD exam bundles three categories with **different scoring modes**:

| Category | Sub-Type `sistem_penilaian` | Correct answer | Wrong/blank answer |
| -------- | -------------------------- | -------------- | ------------------ |
| **TWK** (Tes Wawasan Kebangsaan) | `benar_salah` | `+nilai_benar` (typically **+5**) | **0** |
| **TIU** (Tes Intelegensi Umum) | `benar_salah` | `+nilai_benar` (typically **+5**) | **0** |
| **TKP** (Tes Karakteristik Pribadi) | `tiap_jawaban_ada_poin` | per-option weight **1–5** (set on each question's `nilai_bobot_a..e`; no "wrong" — all options carry a positive point) | **lowest option weight** (not 0) |

- For `benar_salah`: `nilai = nilai_benar` if `jawaban == kunci_jawaban`, else `nilai = 0`.
  `benar = (jawaban == kunci_jawaban)`.
- For `tiap_jawaban_ada_poin`: `nilai = nilai_bobot_{jawaban|lowercase}` from the `soal` record.
  `benar = null` (concept of "correct" does not apply). If `jawaban` is null (unanswered):
  `nilai = 0`.

> **Fallback guard:** If `sistem_penilaian = benar_salah` and `nilai_benar` is null on
> `sub_jenis_ujian`, fall back to `panritta_soal.nilai_bobot_benar` if set, else default to 0.
> Log a warning — this condition indicates a data configuration error.

#### Service Methods

| Method | Behavior |
| ------ | -------- |
| `scoreAnswer(UjianJawaban $jawaban): void` | Resolves mode from `sub_jenis_ujian` (C-3 chain above). Writes `nilai` and `benar` to `panritta_ujian_jawaban`. Idempotent: re-scoring the same row produces the same result. |
| `aggregateCategories(UjianPeserta $peserta): void` | Sums `nilai` grouped by `jenis_ujian_id` into `panritta_ujian_peserta_kategori.nilai_kategori`. Upserts rows (not insert-only) to handle re-scoring. |
| `evaluatePass(UjianPeserta $peserta): void` | For each category: `lulus_kategori = nilai_kategori >= passing_grade`. **`UjianPeserta.lulus = true` iff ALL categories pass (AD-4).** Writes `total_nilai` = sum of category scores. |

### 6.3. `AttemptService` (PRD §4.4 — M3/M4/AD-6/C3/C4)

| Method | Behavior |
| ------ | -------- |
| `start(Ujian $ujian, int $userId): UjianPeserta` | Creates/loads `UjianPeserta` with `status=sedang_ujian`, `waktu_mulai=now`, computes `batas_waktu`. **`batas_waktu` formula (C-AU-1):** for offline = `now() + durasi_ujian minutes`; for online = snapshot of `langganan->berakhir_pada` at call time (not a live reference — prevents mid-attempt drift). **For online:** wraps quota decrement in a DB transaction with a row-level lock (`lockForUpdate()` on `panritta_peserta_langganan`) to prevent race conditions (C-3). Rejects if `sisa_kuota_ujian <= 0` or subscription not `active`. **Re-take behavior (C-AU-2):** if a previous `UjianPeserta` exists for this `(ujian_id, user_id)` with `status=selesai`, a **new** `UjianPeserta` row is inserted (re-take allowed per PRD §4.4); `panritta_ujian_peserta` has no unique constraint on `(ujian_id, user_id)`. **For offline:** validates `ujian_id` matches a `panritta_peserta_offline` record for this user session (C-4). |
| `startOffline(string $nomorPeserta, string $kodeAkses, Ujian $ujian): UjianPeserta` | Offline-only entry point (C-4). Verifies `Hash::check($kodeAkses, pesertaOffline->kode_akses)`. Creates `UjianPeserta` (status=`sedang_ujian`) and links `panritta_peserta_offline.ujian_peserta_id`. |
| `submit(UjianPeserta $peserta): void` | Finalizes attempt: runs scoring pipeline (§6.2), sets `status=selesai`, `waktu_selesai=now`. **Idempotency guard (C-3):** if `status` is already `selesai`, this method is a no-op (prevents double-submit). |
| `autoSubmitExpired(): void` | Invoked by scheduled job (§10). Evaluates **`batas_waktu` as the single source of deadline truth for both offline and online (C-AU-6)** — since `batas_waktu` is snapshot-frozen at `start()` (offline = duration; online = subscription `berakhir_pada` at start; AD-10). For attempts where `now >= batas_waktu` and `status = 'sedang_ujian'`: sets `auto_submitted=true` and calls `submit()`. Does **not** re-read live `berakhir_pada`, so a mid-attempt subscription renewal cannot extend an in-progress attempt. **Idempotency guard:** uses `status != 'selesai'` in the query filter to avoid re-processing. |

**Quota rule (R3):** Quota is consumed atomically at `start` using `lockForUpdate()`. An
auto-submitted attempt does **not** refund quota.

> **`kuota_ujian = null` semantics (M-AU-4):** When `panritta_paket.kuota_ujian` is `null`, the
> package grants **unlimited attempts** for the duration of the subscription. The `start()` method
> MUST skip the `sisa_kuota_ujian <= 0` rejection check when `langganan->paket->kuota_ujian` is
> null. `panritta_peserta_langganan.sisa_kuota_ujian` is also set to `null` on subscription
> creation in this case (not 0) to signal unlimited. The `autoSubmitExpired` job is still triggered
> by `berakhir_pada` regardless of quota setting.

**Question order snapshot (C-4 — randomization contract):**

- When `acak_soal = false` (default): questions are served in the `urutan` order from
  `panritta_ujian_soal`. Order is stable across refreshes.
- When `acak_soal = true`: on `start`, generate a shuffled order and write it to
  `panritta_ujian_jawaban` by pre-inserting blank answer rows in shuffled order (using `urutan`
  derived from the shuffle). This snapshot ensures the participant sees the same order on every
  page refresh within the same attempt. The `ExamScoringService` is order-agnostic (scores by
  `soal_id`, not position).

### 6.4. `OfflineParticipantService` (C-4 — new)

Manages admin CRUD for offline participant master data.

| Method | Behavior |
| ------ | -------- |
| `create(Ujian $ujian, array $data): PesertaOffline` | Validates `ujian` is `offline_kelas`. Generates a random `kode_akses` plaintext (e.g., 8-char alphanumeric), returns it **once** for admin to print/distribute. Stores `Hash::make($kodeAkses)` in `kode_akses`. |
| `bulkCreate(Ujian $ujian, array $participants): Collection` | Batch import. Returns collection of `[nomor_peserta, kode_akses_plaintext]` for the admin to print. |
| `blockParticipant(PesertaOffline $peserta): void` | Sets linked `UjianPeserta.status = diblokir`. |

### 6.5. `TokenService` (PRD §4.3.1)

- On offline exam create/activate, if `token_ujian` is null → generate a unique token.
- Uniqueness guard: token unique across `panritta_ujian` where `tipe_ujian=offline_kelas`.

---

## 7. Online vs Offline Configuration (Resolves R1/R2)

Both exam types share the **composite** structure (AD-3) and the **capacity-first assembly** flow
(C1). The only difference is timing/access.

| Aspect | Offline (`offline_kelas`) | Online (`online_paket`) |
| ------ | ------------------------- | ----------------------- |
| Composite categories (TWK/TIU/TKP) | Yes (via `ujian_jenis_ujian`) | **Yes — same model** |
| Per-category passing grade | Yes | **Yes — same** |
| Assembly flow + remaining counter | Yes | **Yes — same** |
| `tanggal_ujian`, `durasi_ujian`, `batas_keterlambatan`, `token_ujian` | Required/shown | Hidden/null |
| `sub_jenis_ujian_id` | Null (offline uses composite categories via `ujian_jenis_ujian`) | **Required** — scopes question bank for assembly (C-AU-2) |
| Access control | Token entry | Active package + quota |
| Attempt deadline (`batas_waktu`) | snapshot of `waktu_mulai` + `durasi_ujian` at start | **snapshot** of subscription `berakhir_pada` at start (frozen — not a live reference; C-AU-1/AD-10) |

> This explicitly aligns PRD §4.3.2 online exams with the composite decision in §4.3, closing the
> Iteration-2 residual R1/R2.

---

## 8. Authentication & Authorization

### 8.1. Spatie Roles

The application reuses the existing Spatie Permission setup (PRD §2.1). The following roles are
authoritative for the exam domain. Role names MUST match the seeded values in the database exactly.

| Role Name | Scope | Description |
| --------- | ----- | ----------- |
| `superadmin` | Admin panel | Full access to all admin routes including exam type, question, exam, package, and offline participant management |
| `admin` | Admin panel | Same access as `superadmin` for exam-domain routes; distinction from `superadmin` is reserved for future super-level config (e.g., system settings) |
| `peserta` | Participant panel | Registered online member; authenticated via `users` table with standard Laravel `auth` guard |

> **Offline participants** are NOT assigned a Spatie role. They do not have a `users` record. Their
> session is managed separately — see §8.3.

### 8.2. Route Middleware Groups

All routes are defined under one of three middleware stacks:

| Stack | Middleware | Applied To |
| ----- | ---------- | ---------- |
| **Admin** | `['web', 'auth', 'role:superadmin\|admin']` | All routes in §9.1 (Admin routes) |
| **Participant (Online)** | `['web', 'auth', 'role:peserta']` | All participant routes in §9.2 **except** offline login |
| **Offline Auth** | `['web', 'offline.auth']` | `POST ujian/{ujian}/jawaban`, `POST ujian/{ujian}/selesai`, `GET ujian/{ujian}/hasil` when accessed by an offline participant |

> `role:superadmin|admin` uses Spatie's `role` middleware alias registered via
> `PermissionServiceProvider`. No custom gate is needed for these route groups.

The `offline.auth` middleware is a **new custom middleware** — see §8.3.

### 8.3. Offline Participant Session Mechanism (C-AU-2)

Offline participants do not have a `users` table record and cannot be authenticated via Laravel's
standard `auth` guard. The following mechanism governs their session lifecycle.

#### Login Flow

1. Participant submits `POST ujian/offline/login` (unauthenticated — **no middleware guard**).
2. `AttemptService::startOffline()` verifies `Hash::check($kodeAkses, pesertaOffline->kode_akses)`.
3. On success, the system writes the following keys to the **Laravel session** (`session()` / `$request->session()`):

   ```php
   session([
       'offline_peserta_id' => $pesertaOffline->id,   // PK of panritta_peserta_offline
       'offline_ujian_id'   => $ujian->id,             // scopes session to exactly one exam
       'offline_attempt_id' => $ujianPeserta->id,      // PK of panritta_ujian_peserta
   ]);
   ```

4. The participant is redirected to the exam-taking page for `$ujian->id`.

> **No `Auth::login()` is called.** The offline session is entirely session-key–based and is
> intentionally isolated from the standard `auth` guard to prevent any accidental privilege
> escalation.

#### `offline.auth` Middleware (New)

A new middleware class `App\Http\Middleware\OfflineParticipantAuth` MUST be created and registered
as `offline.auth` in `bootstrap/app.php`.

**Responsibility:** For every request on an offline-participant route:

1. Assert `session('offline_peserta_id')` is set. If missing → **abort `403`**.
2. Assert `session('offline_ujian_id') === (int) $route->parameter('ujian')`. If mismatch → **abort `403`** (prevents one offline participant from accessing another exam).
3. Assert the linked `UjianPeserta` (via `session('offline_attempt_id')`) has `status = 'sedang_ujian'`. If `selesai` → redirect to result page if `tampilkan_hasil = true`, else **abort `403`**.

Registration in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'offline.auth' => \App\Http\Middleware\OfflineParticipantAuth::class,
    ]);
})
```

#### Dual-Guard on Shared Participant Routes

Routes in §9.2 that serve **both** online and offline participants (`POST jawaban`, `POST selesai`,
`GET hasil`) MUST detect the participant type and apply the correct ownership check:

```php
// In controller or base action:
if (session()->has('offline_peserta_id')) {
    // Offline path — ownership validated by offline.auth middleware already
    $peserta = UjianPeserta::findOrFail(session('offline_attempt_id'));
} else {
    // Online path — standard auth guard
    $peserta = UjianPeserta::where('user_id', Auth::id())
                            ->where('ujian_id', $ujian->id)
                            ->firstOrFail();
}
```

This pattern is the **single source of truth** for C-4 access isolation on shared routes.

#### Session Termination

On `POST ujian/{ujian}/selesai` (submit), after `AttemptService::submit()` completes:

```php
session()->forget(['offline_peserta_id', 'offline_ujian_id', 'offline_attempt_id']);
```

The session keys are cleared. Subsequent requests from this browser to any `offline.auth` route will
receive `403`.

---

## 9. Controller & Route Surface

Follows existing resource-route conventions (`Route::resource`). Admin under auth+role middleware;
participant under participant auth. For middleware stack definitions see §8.2.

### 9.1. Admin

| Route | Controller@method | Purpose |
| ----- | ----------------- | ------- |
| `resource ujian` | `UjianController` | CRUD exams (offline+online) |
| `POST ujian/{ujian}/soal` | `UjianSoalController@store` | Add questions per sub exam type |
| `DELETE ujian/{ujian}/soal/{soal}` | `UjianSoalController@destroy` | Remove a question |
| `GET ujian/{ujian}/remaining` | `UjianSoalController@remaining` | JSON remaining-slots counter (Alpine) |
| `POST ujian/{ujian}/activate` | `UjianController@activate` | Runs `assertFinalizable`, sets `status=aktif` |
| `resource paket` | `PaketController` | CRUD packages |
| `PUT paket/{paket}/ujian` | `PaketUjianController@sync` | Attach/detach online exams (online-only guard) |
| `resource ujian.peserta-offline` | `PesertaOfflineController` | CRUD offline participant master (C-4) |
| `GET ujian/{ujian}/peserta-offline/export` | `PesertaOfflineController@export` | Print/export participant credential sheet |

### 9.2. Participant

| Route | Controller@method | Purpose |
| ----- | ----------------- | ------- |
| `POST ujian/{ujian}/mulai` | `PesertaUjianController@start` | Token (offline) or package check (online) → `AttemptService::start` |
| `POST ujian/offline/login` | `PesertaOfflineController@login` | Offline participant credential login (nomor_peserta + kode_akses) → `AttemptService::startOffline` (C-4) |
| `POST ujian/{ujian}/jawaban` | `PesertaUjianController@answer` | Save one answer — **authorization guard: `ujian_peserta_id` must belong to the authenticated session** (C-4 isolation) |
| `POST ujian/{ujian}/selesai` | `PesertaUjianController@submit` | `AttemptService::submit` — same ownership guard |
| `GET ujian/{ujian}/hasil` | `PesertaUjianController@result` | Shown only if `tampilkan_hasil=true` AND attempt `status=selesai` — same ownership guard |

> **C-4 Access Isolation:** Every participant-facing route MUST use the dual-guard pattern defined
> in §8.3 to assert that the `UjianPeserta` record belongs to the current session. Return `403` on
> mismatch. This prevents one participant from reading or submitting answers on behalf of another.

---

## 10. Form Request Validation (Contracts)

| Request | Key Rules |
| ------- | --------- |
| `StoreUjianRequest` | `nama_ujian` required; `tipe_ujian` in enum; `jumlah_soal` ≥ 1; offline requires `tanggal_ujian`,`durasi_ujian`; online forbids timing fields; `sub_jenis_ujian_id` required_if `tipe_ujian=online_paket`, prohibited_if `tipe_ujian=offline_kelas` (C-AU-2) |
| `SyncUjianSoalRequest` | `jenis_ujian_id` exists; `soal_ids[]` exist; total after add ≤ `jumlah_soal` |
| `StorePaketRequest` | `nama_paket` required; `harga` ≥ 0; `durasi_hari` ≥ 1; `kuota_ujian` nullable integer ≥ 1 (if provided); **`null` = unlimited attempts** — `sisa_kuota_ujian` initialized to `null` on subscription creation (M-AU-4) |
| `SyncPaketUjianRequest` | each `ujian_id` must be `tipe_ujian=online_paket` |
| `StoreJawabanRequest` | `jawaban` in [A..E] or null; attempt must be `sedang_ujian` and `now() < batas_waktu` — validation reads the **snapshot** `panritta_ujian_peserta.batas_waktu` only, never live subscription `berakhir_pada` (M-AU-8/AD-10) |
| `StorePesertaOfflineRequest` (C-4) | `ujian_id` required, must be `offline_kelas`; `nomor_peserta` required, max 50, unique per `ujian_id`; `nama_peserta` required, max 255 |
| `LoginPesertaOfflineRequest` (C-4) | `ujian_id` required; `nomor_peserta` required; `kode_akses` required min 4 |

---

## 11. Scheduled Jobs

| Job | Schedule | Action |
| --- | -------- | ------ |
| `AutoSubmitExpiredAttempts` | every minute | `AttemptService::autoSubmitExpired()` for offline duration overrun and online subscription expiry (AD-6) |
| `ExpireSubscriptions` | hourly | Flip `panritta_peserta_langganan.status` to `expired` when `berakhir_pada` has passed |

---

## 12. Testing Strategy (Pest — Two-Layer Mandate)

Micro (per change) + macro (full suite green before phase exit).

| Area | Test Focus |
| ---- | ---------- |
| Assembly | `remainingSlots` math; add exceeding capacity rejected; `assertFinalizable` blocks on shortage (R4) |
| Scoring — mode resolution (C-3) | `benar_salah` mode: correct answer → `+nilai_benar`, wrong/blank → 0; `tiap_jawaban_ada_poin` mode: each option returns its configured weight; fallback to `soal.nilai_bobot_benar` when `nilai_benar` null |
| Scoring — CPNS composite (C-2) | TWK answer correct → +5, wrong → 0; TIU answer correct → +5, wrong → 0; TKP answer A → bobot_a (1–5), no "wrong" concept |
| Pass rule | all-pass → `lulus=true`; any single category fail → `lulus=false` (AD-4); partial pass combinations |
| Attempt/quota — idempotency (C-3) | concurrent start attempts consume quota exactly once (lock guard); `submit` on already-`selesai` attempt is no-op; `autoSubmitExpired` processes each attempt exactly once |
| Attempt/quota — online flow | start decrements quota once; start blocked at quota 0; auto-submit on deadline; no refund on auto-submit (R3) |
| Randomization snapshot (C-4) | `acak_soal=false` → questions served in `urutan` order across multiple requests; `acak_soal=true` → order is stable within same attempt but differs from another attempt |
| Offline participant (C-4) | admin can create participant with `nomor_peserta` + `kode_akses`; login with correct credentials starts attempt; login with wrong `kode_akses` rejected; same `nomor_peserta` per `ujian` rejected (unique) |
| Access isolation (C-4) | participant A cannot access/submit attempt belonging to participant B; returns 403 |
| Package link | online-only attach guard; offline exam rejected |
| Offline participant session (C-AU-2) | login sets session keys; `offline.auth` middleware aborts 403 on missing/mismatched keys; session cleared on submit |
| `batas_waktu` formula (C-AU-1) | offline: `waktu_mulai + durasi_ujian`; online: snapshot of `berakhir_pada` at start time; mid-attempt subscription renewal does not shift deadline |
| `batas_waktu` single-source (C-AU-6) | `autoSubmitExpired` fires on `batas_waktu` only; renewing subscription mid-attempt does NOT extend the in-progress attempt; `StoreJawabanRequest` rejects answers after `batas_waktu` |
| Re-take behavior (C-AU-2) | second `start()` call after `selesai` creates a new `UjianPeserta` row and decrements quota again; previous attempt row is untouched |
| Unlimited quota (M-AU-4) | package with `kuota_ujian=null` allows `start()` regardless of attempt count; `sisa_kuota_ujian=null` on langganan bypasses quota check |

---

## 13. Traceability Matrix (PRD → Spec)

| PRD Item | Spec Section |
| -------- | ------------ |
| §1.1 Single-tenant | AD-1, §4 (no company_id) |
| §4.3 Composite + all-pass | AD-3/AD-4, §6.2, §7 |
| §4.3 CPNS TWK/TIU/TKP scoring modes (C-2) | §6.2 (scoring rule table), §11 (CPNS composite tests) |
| §4.3.1 Capacity-first assembly (C1) | §6.1, §8.1 |
| §4.3.2 Online alignment (R1/R2) | §7 |
| §4.3.3 No list_soal (AD-2) | §3, §5.2 |
| §4.3.1 Randomization default off (C-4) | §3 (`acak_soal` default false), §6.3 (snapshot contract) |
| §4.4 Package↔online exams (AD-5) | §4.1, §8.1 |
| §4.4 Quota per-attempt + auto-submit (M3/M4) | §6.3, §10 |
| §4.4 Quota idempotency (C-3) | §6.3 (`lockForUpdate` + status guard) |
| §4.3.2 Online `sub_jenis_ujian_id` field (C-AU-2) | AD-7, §3 baseline, §4.5, §7, §10 (`StoreUjianRequest`) |
| §4.4 Re-take allowed, no unique constraint (C-AU-2) | AD-9, §6.3 `start()` re-take behavior |
| §4.4 `batas_waktu` formula online path (C-AU-1) | AD-10, §4.2 column notes, §6.3 `start()`, §7 deadline row |
| §4.4 `batas_waktu` single-source-of-truth (C-AU-5/C-AU-6) | AD-10, §6.3 `autoSubmitExpired()`, §7, §10 (`StoreJawabanRequest`) |
| §4.4 `kuota_ujian = null` = unlimited (M-AU-4) | AD-8, §6.3 quota rule, §10 (`StorePaketRequest`) |
| §5 Offline participant master (C-4) | §4.4, §5.1, §6.3, §6.4, §8.1, §8.2, §9 |
| Scoring mode per sub-category (C-3) | §3 (baseline), §5.2, §6.2 (mode resolution chain) |

---

## 14. Open Items Carried to Backlog

| # | Item | Disposition |
| - | ---- | ----------- |
| 1 | Late-tolerance behavior (reject vs truncated duration) | `[Backlog]` — PRD L1 |
| 2 | Exam lifecycle edit rules after attempts exist | `[Backlog]` — PRD L2 |
| 3 | Redundancy of `kunci_jawaban` vs per-option weights | `[Backlog]` — PRD L3; scoring service tolerates both |
| 4 | Multi-sub-type online exams (promote `sub_jenis_ujian_id` FK to pivot) | `[Backlog]` — L-AU-9; current design assumes single sub-type per online exam |

---

## 15. Next Steps

1. **[C-1 housekeeping]** Update `ai/AGENTS.md` Tech Stack Rules line to read `Laravel 12 (PHP 8.2+)` to eliminate the stale "Laravel 11" reference and prevent future spec drift.
2. Run `/sdlc-clarify-reqs` against this revised Spec to validate C-AU-1, C-AU-2, and M-AU-4 remediations are complete.
3. Proceed to `/sdlc-plan-tasks` for phased implementation planning.
