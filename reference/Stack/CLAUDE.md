# CLAUDE.md – instrukce pro Claude Code

## Projekt
Webová stránka **kasparikova.cz** – prezentační web MVDr. Martiny Kašpaříkové, veterinární chiropraktičky.

## Struktura repozitáře
- `src/` – veškeré zdrojové kódy webu (Laravel 12 aplikace, nasazují se do document rootu)
- `reference/` – referenční materiály, fotografie, loga, about.txt (nesmazat)

## Tech stack
- **Framework:** Laravel 12 (PHP 8.4 lokálně přes Herd, produkce PHP 8.4)
- **CSS:** Tailwind CSS v4 (`@tailwindcss/vite`)
- **JS:** Alpine.js (mobilní menu, cookie banner)
- **Editor článků:** TinyMCE 6 self-hosted (`public/tinymce/` – gitignorováno, obnovit přes `npm run tinymce:publish`)
- **Mapa:** Leaflet.js + OpenStreetMap
- **Build:** Vite (`npm run build` pro produkci, `npm run dev` pro vývoj)
- **DB lokálně:** SQLite; **produkce:** MySQL

## Klíčové soubory
- `src/routes/web.php` – všechny routy (veřejné i admin)
- `src/resources/css/app.css` – Tailwind + custom tema (primary: #29b8b0)
- `src/resources/views/web/home.blade.php` – hlavní stránka
- `src/resources/views/layouts/app.blade.php` – layout webu (nav, footer, GA, cookie)
- `src/resources/views/layouts/admin.blade.php` – layout adminu
- `src/database/seeders/DatabaseSeeder.php` – výchozí uživatelé
- `src/database/seeders/ArticleSeeder.php` – vzorové články

## Admin přístupy (lokální)
- `admin@kasparikova.cz` / `změňteHeslo123!` (role: admin)
- `m.kasparikova@email.cz` / `změňteHeslo123!` (role: editor)
- Admin URL: `/admin/prihlaseni`

## Produkční prostředí
- **Doména:** kasparikova.cz (HTTPS)
- **Server:** Apache s PHP-FPM/FastCGI
- **PHP:** 8.4, RHEL 8.10 (x86_64)
- **Document root:** `/var/zpanel/hostdata/kasparik/public_html/kasparikova_cz`
- **Databáze:** MySQL přes PDO/MySQLi (mysqlnd)
- **Rozšíření dostupná:** curl, gd, mbstring, intl, json, openssl, redis, apcu, soap, zip, sodium, amqp, …

## Workflow při nasazení na produkci
1. Přepnout `.env` na MySQL (odkomentovat DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
2. Nastavit `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://kasparikova.cz`
3. Nastavit SMTP pro odesílání e-mailů (MAIL_MAILER, MAIL_HOST, atd.)
4. `npm run build` – vygeneruje `public/build/`
5. `npm run tinymce:publish` – zkopíruje TinyMCE do `public/tinymce/`
6. Nahrát přes FTP: celý obsah `src/` do document rootu
7. Na serveru: `php artisan migrate --force` + `php artisan db:seed`
8. `php artisan storage:link` pro veřejné soubory

## Klíčová pravidla
- Kód píšeme v češtině (komentáře, commit messages, proměnné dle konvence projektu).
- Zdrojáky patří výhradně do `src/`.
- Necommitovat `.env`, hesla, upload složky s uživatelským obsahem, `public/tinymce/`.
- Před úpravou souboru ho vždy nejprve přečíst.
- Nepřidávat zbytečné abstrakce ani funkce navíc – jednoduchost a přehlednost.
- Commitovat pouze na explicitní žádost uživatele.
- Po změnách CSS/JS vždy spustit `npm run build`.
