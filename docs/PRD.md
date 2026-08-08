<!-- markdownlint-disable -->
# Product Requirements Document (PRD) — Transformation: HRIS to CPNS Online Exam (CAT)

**Document Name:** Transformation PRD — HRIS to CPNS Online Exam Platform (CAT)
**Backend/Frontend Framework:** Laravel 12 + Blade + Alpine.js + Tailwind CSS 4
**Status:** Draft (Phase PRD)
**Author:** Product Manager (SDLC `/sdlc-draft-prd`)
**Type:** Transformation PRD (Pivot from existing HRIS codebase)

---

## 1. Overview

### 1.1. Background

This product is a **pivot** of an existing HRIS/Payroll application (GajiPro). The
codebase, its administrative theme, sidebar, layout, and authentication system are being reused as
the foundation for a new product: an **Online Exam Application for CPNS/Kedinasan selection (CAT —
Computer Assisted Test)**.

The decision to reuse the HRIS application is deliberate: its dashboard structure and UI are already
polished and production-ready, which lets the team focus effort on the new exam domain rather than
rebuilding infrastructure.

> **Deployment Model — Single-Tenant (Decided):** The exam platform is designed as a
> **single-tenant** application, serving **exactly one institution** per deployment. The
> multi-tenant company-isolation behavior inherited from the HRIS codebase is **not used** in the
> exam domain. All exam types, questions, exams, packages, and participants belong to that single
> institution, and no `company_id`-based data partitioning governs exam-domain access.

### 1.2. Product Goal

Deliver a platform where:

- **Admins** manage exam types, questions, exams, and member packages.
- **Participants** take exams — either **offline** (proctored, in a classroom/lab) or **online**
  (self-service, gated by a purchased membership package).

### 1.3. Scope of This PRD

This PRD covers **only** the current transformation slice: retained infrastructure, deprecated HRIS
modules, and the new core exam domain (Exam Type, Question, Exam, and Member Package management).

Downstream concerns (live scoring UX polish, payment gateway flows, leaderboards, certificates,
analytics) are **out of scope for this document** and will be captured in a follow-up PRD once real
data has been entered and validated.

---

## 2. Retained Infrastructure

> These capabilities already exist in the HRIS codebase and are **reused as-is** (with minimal
> rebranding). They MUST NOT be rebuilt from scratch.

### 2.1. Authentication & User Management

- **Reuse** the existing Auth system (Login / Register) and User management.
- The application already exposes two auth contexts that map cleanly to the new domain:
  - **Admin/Superadmin auth** — for staff who manage exam content and sessions.
  - **Participant (Peserta) auth** — including standard login/register and Google OAuth, already
    present for self-service online members.
- Roles & permissions continue to use the existing **Spatie Permission** setup.

**Acceptance Criteria:**

- An admin can log in and reach the admin dashboard using the existing login flow.
- A participant can register and log in using the existing participant auth flow.
- No new authentication mechanism is introduced in this phase.

### 2.2. Theme, Sidebar & Admin Dashboard Layout

- **Reuse** the existing admin dashboard theme, sidebar, and layout for **both** the Admin view and
  the Participant view.
- Navigation labels are re-purposed for the exam domain (e.g., replacing payroll menu entries with
  exam-domain entries), but the underlying layout components, styling conventions, and UI component
  library (buttons, badges, cards, tables, alerts, confirm dialog) are retained.

**Acceptance Criteria:**

- Admin exam screens render inside the existing admin layout with a consistent sidebar.
- Participant screens reuse the same visual system and component standards.
- Existing standardized form components (`.input`, labels, prefix inputs) and the
  `<x-confirm-dialog />` pattern are used for all new screens.

---

## 3. Deprecated Modules

> The following HRIS modules are **dropped** from the product. They are considered out of scope and
> their menu entries MUST be removed or hidden from the exam-domain navigation.

| Deprecated Module | Disposition |
| ----------------- | ----------- |
| Attendance / Presence (Absensi & Presensi) | Removed / ignored |
| Payroll (Penggajian) | Removed / ignored |
| Leave (Cuti) | Removed / ignored |
| Overtime, Reimbursement, Tax, BPJS, and related payroll sub-modules | Removed / ignored |

**Notes & Acceptance Criteria:**

- These modules and their sidebar menu entries are **not visible** to admins in the exam product.
- Underlying code/tables MAY remain in the repository temporarily to avoid destabilizing shared
  infrastructure, but they are treated as **dead/deprecated** and receive no further product
  investment.
- No participant or admin exam workflow depends on any deprecated module.

---

## 4. New Core Domain — CPNS Exam System

> This is the heart of the transformation. Several building blocks **already exist** in the codebase
> (exam types, questions, some exam management). This PRD documents the intended behavior and
> acceptance criteria, and flags the delta between what exists and what is new.

### 4.0. Domain Building Blocks Already Present

Based on codebase exploration, the following already exist and are **reused**:

- **Exam Type management** (Jenis Ujian) — already in the system.
- **Sub Exam Type / Indicator** (Sub Jenis Ujian, Sub Indikator) — already in the system.
- **Question management** (Bank Soal) — already in the system, including question images, options
  A–E, answer keys, per-option scoring weights, and explanations.
- **Exam management** (Ujian) — already scaffolded with support for `offline_kelas` and
  `online_paket` types, question randomization, result visibility, scheduling, tokens, and passing
  grade per exam type.
- **Member Package management** (Paket) — already scaffolded.
- **Participant self-service** (Peserta) — dashboard, subscription, and exam-taking flows already
  scaffolded.

The **new** work in this slice is primarily about **refining and confirming behavior** of Exam
management (splitting the offline vs online configuration clearly) and the **online member exam +
package** flow.

---

### 4.1. Exam Type Management (Jenis Ujian) — Existing

Admins manage the catalog of exam types (e.g., SKD, SKB, TPA), each with sub-types/indicators used
to categorize questions.

**Acceptance Criteria:**

- Admin can create, view, edit, and delete an exam type.
- Each exam type can have sub-types (Sub Jenis Ujian), and each sub-type can have sub-indicators
  (Sub Indikator).
- Each sub-type carries its own **scoring system** (e.g., right/wrong, or per-option points) and
  answer configuration.

---

### 4.2. Question Management (Manajemen Soal) — Existing

Admins manage the question bank. Questions belong to a sub-indicator (and therefore inherit an exam
type and sub-type).

**Acceptance Criteria:**

- Admin can create, view, edit, and delete questions.
- A question supports text and image for the question body, options A–E (with optional images),
  an answer key, per-option scoring weights, and an explanation.
- Questions are filterable by exam type / sub-type / sub-indicator when building an exam.

---

### 4.3. Exam Management (Manajemen Ujian) — Existing + Refined

Exams are split into **two types**. The **offline** type is the most frequently used (classroom
exams); the **online member** type is the newer capability gated by membership packages.

> **Composite Exam Structure (Decided):** A single exam is a **composite** of multiple exam-type
> categories. For the CPNS SKD case, one exam bundles the three standard categories together:
> **TWK** (Tes Wawasan Kebangsaan), **TIU** (Tes Intelegensi Umum), and **TKP** (Tes Karakteristik
> Pribadi). Each category carries its **own passing grade**. A participant **passes the exam only
> if _all three_ categories independently meet their respective passing grades**. Failing any single
> category means the participant fails the whole exam, regardless of the total score.

#### 4.3.1. Offline Exam (Ujian Offline / `offline_kelas`)

**Configuration fields:**

- **Exam name**
- **Number of questions** — the total capacity of questions the exam will hold (e.g., `80`).
- **Exam type** (select the parent Jenis Ujian; a composite exam may include the TWK, TIU, and TKP
  categories — see §4.3).
- **Question assembly flow (Decided — see §4.3.3):**
  - **Step 1 — Declare capacity first.** After the admin sets the **Number of questions**, the
    system displays that capacity as the target the exam must be filled up to.
  - **Step 2 — Show selectable sub exam types.** The system lists the available **sub exam types**
    (sub jenis soal) that can contribute questions to the exam.
  - **Step 3 — Add questions per sub exam type.** To add questions grouped by their category, the
    admin first **clicks a sub exam type button**, then **selects/adds the questions** for that
    category from the question bank.
  - **Step 4 — Live remaining counter.** The system shows a running notification of how many
    questions still need to be added, e.g., *"Remaining questions to add: 12"* — meaning 12 of the
    80 declared questions have not yet been filled.
- **Question randomization** — randomize / not randomize (**default: not randomized**).
- **Result visibility** — show participant result / hide (**default: show**).
- **Exam configuration:**
  - **Date & time** of the exam.
  - **Duration** (in minutes).
  - **Late tolerance limit** (date & time).
  - **Exam token**.
- **Passing grade** defined **per exam-type category** included in the exam (e.g., separate passing
  grades for TWK, TIU, and TKP). Pass/fail is evaluated per category — see §4.3.
- **List of questions to be tested** — assembled via the flow above; storage design in §4.3.3.

**Acceptance Criteria:**

- After the admin sets the **Number of questions**, the UI displays the selectable sub exam types
  and a **live remaining counter** (e.g., *"Remaining questions to add: 12"*).
- The admin adds questions by first clicking a **sub exam type button**, then selecting questions
  for that category; the remaining counter decreases as questions are added.
- The exam cannot be finalized while the remaining counter is greater than zero (the declared
  Number of questions must be fully filled).
- Offline-specific fields (date/time, duration, late limit, token) are only shown/required for
  offline exams.
- Randomization defaults to **off** and result visibility defaults to **on**.
- A **separate passing grade** can be set for each exam-type category included in the exam
  (e.g., TWK, TIU, TKP).
- At exam end, the participant **passes only if every category meets its own passing grade**;
  failing any single category fails the whole exam.
- If no token is supplied, the system generates one automatically.

#### 4.3.2. Online Member Exam (Ujian Online / `online_paket`)

**Configuration fields:**

- **Exam name**
- **Number of questions**
- **Exam type**
- **Sub exam type**
- **Question randomization** — randomize / not randomize (**default: not randomized**).
- **Result visibility** — show participant result / hide (**default: show**).
- **Passing grade**

**Key difference:** Online exams have **no scheduling/timing configuration** (no date, duration,
late limit, or token). Instead, an online exam is later attached to one or more **membership
packages**, and access is governed by the participant's active package.

**Acceptance Criteria:**

- Timing/token fields are **not** shown or required for online exams.
- An online exam can be included in one or more member packages (see §4.4).
- A participant can only start an online exam if they hold an active package that grants access to
  that exam.

#### 4.3.3. Design Note — Storing the List of Exam Questions

> The user asked whether the list of questions should be stored as a single `list_soal` field
> (e.g., `1-4-5-7-99-1001`) inside the exam record.

**Product recommendation: Do NOT store questions as a single delimited `list_soal` string.**

Rationale (expressed at the product/behavior level, not as a schema mandate):

- The codebase **already** links questions to exams through a dedicated relationship
  (an exam↔question association that also records the question's exam type and display order). This
  is the existing, working pattern and should be the source of truth.
- A relational link (one row per question in the exam) supports behaviors this product needs:
  - Per-question **display order** (required for "not randomized" mode).
  - Grouping questions by **exam type / sub-type** (required for tabbed question management and
    per-type passing grades).
  - Counting questions per exam and validating against the configured "number of questions".
  - Safe add/remove of individual questions without string parsing.
- A single delimited string makes these operations fragile (manual parsing, no integrity, no easy
  per-question metadata, poor querying/reporting).

**Decision for this PRD:** The exam's question set is managed as a **collection of linked questions**
(the existing association), **not** a single `list_soal` string field. The precise storage mechanism
is a technical concern to be finalized in the Specification phase.

---

### 4.4. Member Package Management (Manajemen Paket Ujian Member) — Existing + Refined

Admins define the catalog of membership packages offered by the system (e.g., Silver, Platinum,
Diamond), each with its own set of benefits. When configuring a package, the admin selects which
**online exams** are included in that package.

**Package attributes (already present in the system):**

- Package name, description, price, duration (days), exam quota, and feature flags such as video
  explanation, analytics, and certificate.
- Active status and display order.

**Acceptance Criteria:**

- Admin can create, view, edit, and delete a package.
- Admin can define each package's benefits/features.
- When editing a package, the admin can **select the list of (online) exams** included in that
  package.
- A participant who purchases/activates a package gains access to the online exams attached to it,
  subject to the package's quota and duration.
- Only exams of type **Online Member** are eligible to be attached to a package.

**Quota & Expiry Rules (Decided):**

- **Quota is counted per attempt.** Each time a participant **starts** an online exam, it consumes
  **one** unit of the package's exam quota — regardless of which exam is taken. Re-taking the same
  exam consumes another quota unit.
- **Duration governs package validity.** The package `duration (days)` defines the window during
  which the participant may consume quota.
- **Auto-submit on expiry.** If the package **expires while an attempt is in progress**, the ongoing
  exam is **automatically submitted** with the answers recorded so far. The attempt is scored as-is;
  it is not discarded.

---

## 5. Primary User Roles

| Role | Description |
| ---- | ----------- |
| **Admin / Superadmin** | Manages exam types, questions, exams, packages, and (later) exam sessions. Reuses existing admin auth and dashboard. |
| **Participant — Offline** | Registered by an admin for classroom exams; logs in and enters a token to take the assigned offline exam. |
| **Participant — Online Member** | Self-registers, purchases a membership package, and takes the online exams included in that package. |

---

## 6. Out of Scope (This PRD Iteration)

The following are intentionally **deferred** until after real data has been entered and validated:

- Payment gateway integration details and activation webhooks.
- Live scoring UX, proctoring dashboard refinements, and national leaderboard.
- Result review UI, certificate generation, and analytics.
- Detailed participant allocation/import flows for offline sessions.
- Any changes to backend column data types or JSON payloads (these belong to the Specification
  phase, not the PRD).

---

## 7. Assumptions & Open Items

| # | Item | Status |
| - | ---- | ------ |
| 1 | HRIS deprecated tables may physically remain but stay hidden/unused. | `[Assumed]` |
| 2 | Exam↔question links use the existing association pattern, not a `list_soal` string. | Decided (§4.3.3) |
| 3 | Sidebar/menu re-labeling for the exam domain is a cosmetic reuse of the existing layout. | `[Assumed]` |
| 4 | Package quota is counted **per attempt**; expiry during an attempt triggers **auto-submit**. | Decided (§4.4) |
| 5 | The application is **single-tenant** (serves exactly one institution); no `company_id` isolation in the exam domain. | Decided (§1.1) |
| 6 | An exam is a **composite** of categories (TWK, TIU, TKP); pass requires **all** categories to meet their passing grade. | Decided (§4.3) |
| 7 | Question assembly uses a **capacity-first** flow with a **live remaining counter**. | Decided (§4.3.1) |

---

## 8. Next Steps

1. Run `/sdlc-clarify-reqs` against this PRD to interrogate ambiguities (especially items in §7).
2. Once clarified, run `/sdlc-audit-consistency`, then proceed to `/sdlc-define-specs` for the
   technical design.
