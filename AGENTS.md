# Project Context & Memory - GajiPro HRIS/Exam System

## Project Overview
- **Name**: GajiPro (HRIS/Payroll + Exam Testing SaaS for Indonesia market)
- **Framework**: Laravel 12 + Blade + Alpine.js + Tailwind CSS 4
- **Testing**: Pest PHP v3 (NOT PHPUnit)
- **Architecture**: Multi-tenant (company isolation via `company_id`)
- **Current Focus**: Soal (Question) Management & HTML/Formula Rendering

## Key Technologies & Versions
- PHP 8.3.22
- Laravel 12
- CKEditor 5 (GPL, self-hosted via Vite)
- KaTeX (LaTeX math rendering)
- Spatie Permission (roles & permissions)

## Core Module: Soal (Questions) Management

### Models & Structure
- **Soal** (panritta_soal): Base question model
  - Fields: soal, opsi_a-e, gambar_soal, gambar_opsi_a-e, pembahasan, kunci_jawaban, nilai_bobot_*, tipe_tampil
  - Relations: belongsTo SubIndikator, belongsTo User (pembuat_soal_id)
  - Methods: `hasImages()` → detect `<img>` tags for badges

- **UjianSoal**: Pivot for Ujian ↔ Soal relationship
- **SubIndikator** → **SubJenisUjian** → **JenisUjian** (hierarchy)

### Database Considerations
- HTML content stored ESCAPED in DB (`&lt;p&gt;...&lt;/p&gt;`)
- Auto-escape happens via Blade `e()` function in forms
- Stored escaped to prevent injection attacks

### Views & Controllers
- **Controllers**: `Superadmin\SoalController`, `Superadmin\UjianSoalController`
- **Key Views**:
  - `resources/views/superadmin/soal/index.blade.php` → Bank Soal daftar
  - `resources/views/superadmin/soal/_form.blade.php` → Edit form
  - `resources/views/superadmin/soal/preview.blade.php` → Simulasi tampilan
  - `resources/views/superadmin/ujian/soal/index.blade.php` → Kelola soal per ujian
  - `resources/views/components/ckeditor-soal.blade.php` → CKEditor wrapper
  - `resources/views/components/soal-options-display.blade.php` → Opsi render (horizontal/vertical)

### CKEditor Configuration
- **File**: `resources/js/ckeditor-soal.js`
- **Toolbar**: heading, bold, italic, underline, lists, blockquote, link, imageUpload, undo/redo
- **Upload**: Image upload → `superadmin.soal.upload-editor` route
- **Data Flow**:
  1. Load: Blade `data-initial-value` attribute holds cleaned value (outer `<p>` stripped)
  2. JS: `decodeHtml()` converts escaped entities → clean HTML
  3. CKEditor: `initialData` config loads clean content in WYSIWYG
  4. Edit: User edit content (CKEditor auto-wraps with `<p>`)
  5. Save: Hidden input gets serialized HTML → Blade escape → DB stored escaped

## HTML & Formula Rendering System

### FormulaRenderer Service
- **Location**: `app/Services/FormulaRenderer.php`
- **Methods**:
  - `render(string $content): string`
    - Unescape HTML entities: `&lt;p&gt;` → `<p>`
    - Keep safe tags: `<p>`, `<b>`, `<i>` intact
    - Replace `[[latex_formula]]` → `<span class="math-formula" data-katex="...">`
    - KaTeX renders on client-side via JS
  - `getKaTeXScripts(): string` → Output KaTeX CSS + JS + render function

### KaTeX Formula Syntax
- Input format: `[[\\frac{3}{5}]]` or `[[\\sqrt{2}]]`
- Stored in DB as-is (inside `<p>` tags)
- Rendered client-side using `window.katex.renderToString()`
- Invalid formulas show red error: `❌ Invalid LaTeX`

### Render Locations
1. **Preview** (`superadmin/soal/preview.blade.php`):
   - Soal text: `{!! FormulaRenderer->render($soal->soal) !!}`
   - Opsi text: `{!! FormulaRenderer->render($opsiText) !!}`
   - Includes KaTeX scripts

2. **Exam Display** (`components/soal-options-display.blade.php`):
   - Horizontal/vertical layout opsi
   - Uses FormulaRenderer for text
   - Includes KaTeX scripts in parent layout

3. **Tables** (index views):
   - Plain text only (strip_tags), NO formula render
   - Display badge "Soal bergambar" if `hasImages()` true

## Badge System
- **"Soal bergambar"** appears when content contains `<img` string
- Displayed in:
  - Bank Soal index table
  - Kelola Soal per Ujian (both tabs)

## UI/UX - Action Buttons Order
**Kelola Soal per Ujian** (ujian soal index):
- Left to Right: **Edit** (ghost) → **Delete** (danger ghost) → **Preview** (blue primary, rightmost)
- Preview button styled `btn-primary` for prominence
- Flex: `justify-end` to push buttons right

## Testing
- **Framework**: Pest PHP v3
- **Test Files**:
  - `tests/Unit/FormulaRendererTest.php` → 16 unit tests covering:
    - Plain text, all HTML tags, formula, escaped HTML, mixed scenarios
  - `tests/Feature/Superadmin/SoalManagementTest.php` → Integration tests
- **Run Tests**: `php artisan test --compact --filter="Soal"`

## Linting & Code Standards
- **Formatter**: Laravel Pint v1
- **Run**: `vendor/bin/pint --dirty --format agent`
- **PHP Standards**: 
  - Always explicit return types & type hints
  - Constructor property promotion
  - No empty constructors
  - PHPDoc for complex logic

## Git Workflow (Standing Instructions)
- **ALWAYS**: Confirm before commit & push (never auto-commit)
- **Before commit**: Show `git status` + `git diff` + propose message
- **After commit**: Show result before requesting push approval
- **Commit message**: English, concise, follow repo style
- **No**: force-push, --amend after failed commit, hook-skip

## Common Commands
```bash
# Run tests
php artisan test --compact --filter="Soal"

# Format code
vendor/bin/pint --dirty --format agent

# Commit (after confirmation)
git add -A
git commit -m "feat: message here"
git push

# View recent commits
git log --oneline -10

# Tinker/debug
php artisan tinker
# Query: App\Models\Soal::find(169)->only(['soal', 'opsi_a']);
```

## Known Quirks & Gotchas
1. **Double-escaping**: Content stored escaped in DB, unescape on render via FormulaRenderer
2. **CKEditor initialData**: Must use `data-initial-value` attribute, not raw innerHTML
3. **KaTeX sync load**: Script must load synchronously, not defer, for immediate rendering
4. **HTML in DB**: Deliberately allowed `<p>`, `<b>`, `<i>` after unescape
5. **Outer <p> strip**: Only stripped for editor display (form load), stored with `<p>` in DB
6. **N+1 prevention**: Always eager-load relations (subIndikator, subJenisUjian, jenisUjian)

## Recent Session Summary (2026-10-27)
**Commits**: `e67b0b3 feat: offline exam results flow, storage upload fix, and ujian management improvements`

### Changes Made (Session 2026-10-27):
1. **Storage Upload Fix** (Hostinger subdomain `program.lkppanrita.com`)
   - Root `.htaccess` rewrite: all requests → `public/`
   - New `uploads` filesystem disk in `config/filesystems.php`
     - Root: `public_path('uploads')`
     - URL: `APP_URL.'/uploads'`
   - SoalController: all image upload/url methods use `disk('uploads')` instead of `public`
   - Preview/form views: `Storage::disk('uploads')->url()` calls
   - Created public/uploads folder structure
   - Added `/public/uploads` to .gitignore
   - Deployment guide PDF: `STORAGE_FIX_GUIDE.pdf`

2. **Offline Exam Results Flow**
   - `UjianController` (`Peserta\`): `hasil()`, `pembahasan()`, `leaderboard()` check offline session first
   - `resolvePeserta()`: returns ujianPeserta from offline or online user
   - `OfflineParticipantAuth` middleware: added branch for result routes (only needs offline_peserta_id + token)
   - Redirects: `peserta.login` → `home` route (landing page) across all auth flows
   - `PesertaOffline::with('ujianPeserta')` eager-loaded
   - Session cleared only on explicit logout, not after exam/results

3. **Results Page UI** (`resources/views/peserta/ujian/hasil.blade.php`)
   - Per-sub-jenis (TWK/TIU/TKP) breakdown cards with nilai, passing_grade, lulus status
   - Progress bar colored by score % (≥75 green, 50-74 yellow, <50 red)
   - Overall LULUS/TIDAK LULUS computed from breakdown

4. **UjianScoringService**
   - Added `breakdownPerSubJenis(UjianPeserta)` method
   - Builds map of all sub-jenis, even those with 0 score (unattempted)
   - Returns: `nilai_maksimal`, `pencapaian_persen`, `passing_grade`, `lulus` per sub-type

5. **Leaderboard Injection** (`UjianController`)
   - Private `injectLulusStatus()` method recomputes lulus for 0-score participants
   - Uses `breakdownPerSubJenis()` to show TIDAK LULUS for non-participants

6. **Offline Participant Activation**
   - Migration: `add_is_active_to_peserta_offline_table` default = false
   - `OfflineParticipantService::create()`: sets `is_active => false`
   - Exam list shows badge "✓ Sudah Mengerjakan" and "Lihat Hasil" button if done, hides start

7. **Ujian Status Update** (`Superadmin\UjianController`)
   - New `updateStatus()` method; new route `superadmin.ujian.update-status`
   - Index action buttons: Edit (yellow) → Hapus (red) → Simulasi (blue primary), right-aligned
   - Status badge: aktif (green), draft (yellow); clickable opens modal Aktif/Draft/Selesai

8. **Hasil Ujian Filter** (`Superadmin\HasilUjianController`)
   - Index now shows only ujian `status=aktif` AND currently ongoing
   - Ongoing = `tanggal_ujian <= now` AND `now <= max(batas_keterlambatan, tanggal_ujian + durasi)`
   - Window logic: batas if valid > start, else start + durasi

9. **UI/UX Minor**
   - Exam warning modal: compact, 2 rules, scrollable content, agreement checkbox gates submit
   - Absensi activation view: removed duplicate alert block
   - Navigator answered questions: green `bg-success-500` color

### File Changes Summary (28 files):
- New: `.htaccess`, `STORAGE_FIX_GUIDE.pdf`
- Modified: Controllers (SoalController, UjianController, HasilUjianController, Peserta/*, Middleware/*), Models (Soal), Views (preview, ujian/*, offline/*, absensi/), Services (UjianScoringService, OfflineParticipantService), Config (filesystems.php), Migration
- Working tree clean after commit

## Known Constraints & Patterns
- **Offline session keys**: UjianPeserta needs 4 fields (offline_peserta_id, offline_session_token, offline_ujian_id, offline_attempt_id)
- **Hasil page layout**: Uses breakdown data; gracefully handles both online (user) and offline (pesertaOffline) participants
- **Storage disk separation**: Keep `public` disk for symlink fallback; use `uploads` disk for reliable subdomain access
- **Date filtering**: batas_keterlambatan might be epoch (1970) for legacy; always fallback to start + durasi

## Next Potential Work
- [ ] HTML sanitizer (remove unsafe tags while preserving safe ones)
- [ ] Formula preview in editor modal
- [ ] Bulk import with HTML/formula support
- [ ] Export soal to PDF with proper formatting
- [ ] Accessibility improvements (ARIA labels for formulas)
- [ ] Offline exam certificate/document generation
- [ ] Mobile app sync for offline exams

---
*Last updated: 2026-10-27 | Session: Offline exam portal + storage fix + ujian management*
