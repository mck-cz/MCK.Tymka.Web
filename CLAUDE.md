# CLAUDE.md – instrukce pro Claude Code

## Projekt
**Tymka** — platforma pro správu sportovních týmů a kroužků (MVP).

## Struktura repozitáře
- `src/` – veškeré zdrojové kódy (Laravel aplikace, nasazují se do document rootu)
- `reference/` – PRD, referenční materiály, loga (nesmazat)

## Tech stack
- **Framework:** Laravel 13 (PHP 8.4 lokálně přes Herd, produkce PHP 8.4)
- **CSS:** Tailwind CSS v4 (`@tailwindcss/vite`)
- **JS:** Alpine.js 3.x
- **Reaktivní komponenty:** Livewire 4
- **Mapy:** Leaflet.js + OpenStreetMap
- **Build:** Vite (`npm run build` pro produkci, `npm run dev` pro vývoj)
- **DB lokálně:** SQLite (`database/database.sqlite`); **produkce:** MySQL 8
- **Auth:** Laravel Sanctum (API), session-based (web)
- **Push:** Firebase Cloud Messaging + Laravel Notifications
- **Lokalizace:** CZ/EN — všechny stringy v `/lang/{cs,en}/` souborech

## Lokální vývoj
- **Laravel Herd** → `http://tymka.test` (document root: `src/public`)
- SQLite databáze: `src/database/database.sqlite`

## Barvy (Tymka "Field Green" theme)
- Primární: `#1B6B4A` (`--color-primary`)
- Primární tmavá: `#0F4A32` (`--color-primary-dark`)
- Primární světlá: `#E8F5EE` (`--color-primary-light`)
- Akcentová: `#E8793A` (`--color-accent`)
- Akcentová světlá: `#FFF3EC` (`--color-accent-light`)
- Text: `#1A1A18` (`--color-text`)
- Text sekundární: `#6B6B65` (`--color-text-secondary`)
- Pozadí: `#F7F7F5` (`--color-bg`)
- Povrch: `#FFFFFF` (`--color-surface`)
- Okraje: `#E8E8E3` (`--color-border`)

## Typografie
- Font: Inter (400/500/600) — fallback system-ui, sans-serif
- H1: 24px/600, H2: 20px/600, H3: 16px/500, Body: 14px/400

## Designové konvence
- Border radius: 8px (inputy, badge), 12px (karty), 16px (modály)
- Shadows: žádné — flat design, pouze focus ring na inputech
- Spacing: 4/8/12/16/24/32/48px
- Vždy používat sémantické CSS třídy (`btn-primary`, `btn-secondary`, `card`, `form-input`, `form-label`, `form-error`, `badge`)
- **NIKDY** nepoužívat raw Tailwind barvy pro tlačítka v Alpine.js `:class`

## Architektura
- **Multi-tenancy:** Single DB, `club_id` FK na club-scoped tabulkách, Laravel global scopes
- **Globální entity (bez club_id):** `users`, `user_guardians`, `conversations`, `messages`
- **UUID primary keys** na všech tabulkách
- **Role:** Klubové (owner/admin/member) + týmové (head_coach/assistant_coach/athlete)

## Lokalizace (i18n) – ZÁVAZNÉ PRAVIDLO
- Všechny UI stringy v `/lang/cs/*.php` a `/lang/en/*.php`
- Blade: `{{ __('messages.key') }}` nebo `@lang('messages.key')`
- JavaScript/Alpine: `window.translations` objekt
- **NIKDY** hardcoded český ani anglický text v šablonách
- Datum: cs = `d.m.Y`, en = `Y-m-d`

## Middleware
- `auth` → Laravel Sanctum / session auth
- `role` → Kontrola klubové role
- `club` → Club resolution a global scope
- `locale` → Nastavení jazyka z `users.locale`

## Konvence
- Kód píšeme v angličtině (proměnné, funkce, komentáře), UI texty v lang souborech
- Zdrojáky patří výhradně do `src/`
- Necommitovat `.env`, hesla, upload složky, `node_modules/`
- Před úpravou souboru ho vždy nejprve přečíst
- Nepřidávat zbytečné abstrakce — jednoduchost a přehlednost
- Commitovat pouze na explicitní žádost uživatele
- Po změnách CSS/JS vždy spustit `npm run build`

## Příkazy
```bash
cd src
php artisan migrate:fresh --seed   # Reset DB + seeders
npm run dev                        # Dev server
npm run build                      # Produkční build
php artisan storage:link           # Symlink pro public storage
```
