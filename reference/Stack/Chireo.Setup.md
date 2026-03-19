# CHIREO – Veterinární chiropraktický systém

## Stack
- **Laravel 12** (PHP 8.4), **Tailwind CSS v4** (`@tailwindcss/vite`), **Alpine.js 3.x**, **Vite**
- **SQLite** (lokálně: `database/database.sqlite`), MySQL (produkce)
- **DomPDF** (`barryvdh/laravel-dompdf ^3.1`) – PDF zprávy
- **Laravel Herd** → `http://chireo.test` (document root: `src/public`)
- Aplikace je v adresáři `src/`

## Barvy (CHIREO theme)
- Primární: navy `#1e3a5c` (`--color-navy-*`)
- Akcentová: teal `#29b8b0` (`--color-teal-*`)
- CSS třídy: `btn-primary`, `btn-secondary`, `card`, `form-input`, `form-label`, `form-error`,
  `data-table`, `nav-link`, `badge badge-{navy|teal|green|amber|red|gray}`,
  `page-title`, `page-subtitle`, `alert alert-{success|error}`

## Designové a UI konvence – ZÁVAZNÉ PRAVIDLA

### Tlačítka (KRITICKÉ – opakovaná chyba)
- Vždy používat sémantické CSS třídy: `btn-primary` (aktivní/CTA), `btn-secondary` (neaktivní/alternativní), `btn-ghost` (subtilní)
- **NIKDY** nepoužívat raw Tailwind barvy pro tlačítka: ~~`bg-navy-600 text-white`~~, ~~`bg-teal-500`~~ atd.
- Tailwind **nepurguje dynamické `:class`** s interpolovanými hodnotami → barvy v Alpine `:class` budou neviditelné pokud nejsou v safe-list

### Alpine.js toggle přepínač (view toggle, tab toggle, status toggle)
```html
{{-- SPRÁVNĚ – btn-primary/btn-secondary fungují vždy --}}
<button :class="active ? 'btn-primary' : 'btn-secondary'" class="text-xs">Label</button>

{{-- ŠPATNĚ – bg-navy-600 Tailwind nepurguje při dynamickém :class --}}
<button :class="active ? 'bg-navy-600 text-white' : 'text-gray-600'" class="...">Label</button>
```

### Header akce (page header)
- Vždy `<div class="header-actions">` s `btn-secondary` (sekundární) + `btn-primary` (hlavní CTA)
- Vyhledávání v layoutu app.blade.php – nikdy duplicitní inline

### Formuláře
- Inputy: `form-input`, labely: `form-label`, chyby: `form-error`
- Sekce: `card` wrapper, záhlaví sekce `text-sm font-medium text-muted mb-3`

## Architektura
- **Multi-tenancy**: `tenant_id` na modelech, `TenantMiddleware` injektuje `app('current_tenant')`
- **Session-based auth**: `session('user_id')`, `session('user_role')` – žádný Laravel Auth
- **Role**: `super_admin` / `admin` / `assistant`
- Super admin přepíná tenanta přes `session('current_tenant_id')`

## Middleware (zaregistrované v bootstrap/app.php)
- `auth.chireo` → `AuthMiddleware` – přihlášení
- `role` → `RoleMiddleware` – kontrola role
- `tenant` → `TenantMiddleware` – tenant resolution

## Routy (web.php)
- `/prihlaseni` → login
- `/zprava/{token}` → veřejná zpráva + PIN
- `/admin/*` → super admin (middleware: `auth.chireo, role:super_admin`)
- `/dashboard`, `/majitele`, `/pacienti`, `/vysetreni` → vet rozhraní (middleware: `auth.chireo, tenant`)
- Nested shallow resources: `pacienti.vakcinace`, `pacienti.odcerveni`, `pacienti.zaznamy`

## Route model binding (pozor na české URL klíče)
- `DewormingController` → parametr `$odcerveni` (shoduje se s `pacienti.odcerveni`)
- `MedicalRecordController` → parametr `$zaznam`

## Klíčové soubory
| Soubor | Účel |
|--------|------|
| `src/app/Http/Controllers/` | Všechny controllery |
| `src/app/Http/Middleware/` | Auth, Role, Tenant |
| `src/app/Models/` | Eloquent modely |
| `src/database/migrations/` | 11 migrací |
| `src/database/seeders/` | DatabaseSeeder, TenantSeeder, UserSeeder, VaccinationTypeSeeder |
| `src/resources/views/layouts/` | app, admin, auth, public |
| `src/resources/views/reports/pdf.blade.php` | DomPDF šablona (inline CSS!) |
| `src/resources/css/app.css` | CHIREO Tailwind theme |
| `src/routes/web.php` | Všechny routy |

## Modely a relace
- `Tenant` → hasMany: User, Owner, Patient
- `User` → belongsTo: Tenant; helpers: isSuperAdmin(), isAdmin(), isAssistant()
- `Owner` → belongsTo: Tenant; hasMany: Patient; getFullNameAttribute()
- `Patient` → belongsTo: Owner, Tenant; hasMany: Visit, Vaccination, DewormingRecord, MedicalRecord; getAgeAttribute(), getSpeciesLabelAttribute(), getSexLabelAttribute()
- `Visit` → belongsTo: Patient, User; hasMany: Vaccination, DewormingRecord, MedicalRecord; generateShareToken(), generatePin(), getTypeLabelAttribute(), getStatusLabelAttribute()

## Testovací přístupy (po `migrate:fresh --seed`)
- super_admin: `super@chireo.cz` / `změňteHeslo123!`
- admin: `admin@chireo.test` / `změňteHeslo123!`
- asistentka: `asistentka@chireo.test` / `změňteHeslo123!`

## PDF šablony
- DomPDF nepodporuje Tailwind – pouze inline CSS v `reports/pdf.blade.php`
- `App\Services\ReportPdfService` (je-li vytvořen) ukládá do `storage/tenants/{id}/`

## Konvence
- Používej `currentUser()` a `currentTenant()` z base `Controller`
- Tenant vždy filtruj: `->where('tenant_id', $this->currentTenant()->id)`
- Flash zprávy: `->with('success', '...')` nebo `->with('error', '...')`
- Foto pacientů: `storage/public/tenants/{tenant_id}/patients/{patient_id}/`
- Veřejná zpráva: `session("report_pin_{$token}")` pro ověřený PIN

## Fáze 1 MVP – implementováno ✅
- Autentizace + role + multi-tenancy
- Majitelé (CRUD), Pacienti (CRUD + foto), Návštěvy (multi-step Alpine.js form)
- Vakcinace, Odčervení, Veterinární záznamy
- Veřejná zpráva: PIN + PDF (DomPDF) + e-mail (ExaminationReportMail)
- Super admin: Tenanti (CRUD), Uživatelé (CRUD), Přepínač tenanta

## Příkazy
```bash
cd src
php artisan migrate:fresh --seed   # Reset DB + seeders
npm run dev                        # Dev server
npm run build                      # Produkční build
php artisan storage:link           # Symlink pro public storage
```
