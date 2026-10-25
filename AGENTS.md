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

## Recent Session Summary (2026-10-26)
**Commit**: `7d66162 feat: implement HTML tags & formula rendering...`

### Changes Made:
1. **FormulaRenderer Service** (new)
   - Unescape HTML entities
   - Render LaTeX formulas with KaTeX
   - Keep safe HTML tags

2. **Soal Model**
   - Added `hasImages()` method

3. **CKEditor Improvements**
   - Clean initial value (strip outer `<p>`)
   - `initialData` config for WYSIWYG mode
   - Prevent double-escaping loop

4. **Display Updates**
   - Preview, opsi display, exam all use FormulaRenderer
   - KaTeX scripts included in layouts

5. **UI/UX Enhancement**
   - Preview button moved right, styled blue (primary)
   - Better visual hierarchy in kelola soal tables
   - "Soal bergambar" badge for image detection

6. **Tests**
   - 16 unit tests for FormulaRenderer
   - All 41 soal tests passing

### File Changes Summary:
- New: `app/Services/FormulaRenderer.php`, `tests/Unit/FormulaRendererTest.php`
- Modified: Models (Soal), Views (preview, ujian/soal, components), JS (ckeditor-soal)
- Updated: 20+ files

## Next Potential Work
- [ ] HTML sanitizer (remove unsafe tags while preserving safe ones)
- [ ] Formula preview in editor modal
- [ ] Bulk import with HTML/formula support
- [ ] Export soal to PDF with proper formatting
- [ ] Accessibility improvements (ARIA labels for formulas)

---
*Last updated: 2026-10-26 | Session: HTML/Formula Implementation*
