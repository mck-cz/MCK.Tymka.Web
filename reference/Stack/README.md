# MCK.Kasparikova.Web

Webová stránka **kasparikova.cz** – prezentační web MVDr. Martiny Kašpaříkové, veterinární chiropraktičky.

**Aktuální verze: v1.12.0**

## Produkční prostředí

| Parametr | Hodnota |
|---|---|
| Doména | kasparikova.cz |
| Server | Apache + PHP-FPM/FastCGI |
| PHP | 8.4 |
| OS | RHEL 8.10 (Red Hat Enterprise Linux) |
| Document root | `/var/zpanel/hostdata/kasparik/public_html/kasparikova_cz` |
| Databáze | MySQL |
| Nasazení | FTP (bez SSH) |

## Struktura projektu

```
MCK.Kasparikova.Web/
├── src/              # Zdrojové kódy webu (Laravel 12)
├── reference/        # Referenční materiály (fotky, loga, about.txt)
├── CHANGELOG.md      # Historie verzí a FTP návod k nasazení
├── CLAUDE.md         # Instrukce pro Claude Code
├── README.md         # Tento soubor
└── .gitignore
```

## Tech stack

- **Framework:** Laravel 12 (PHP 8.4)
- **CSS:** Tailwind CSS v4 + Vite
- **JS:** Alpine.js, Leaflet.js
- **Editor:** TinyMCE 6 self-hosted
- **DB lokálně:** SQLite; **produkce:** MySQL

## Vývoj

```bash
cd src/
npm run dev          # Vite dev server
npm run build        # Produkční build (CSS + JS)
```

Zdrojové kódy patří do složky `src/`. Nasazení na produkci probíhá nahráním obsahu `src/` do document rootu přes FTP.

## Nasazení

Viz [CHANGELOG.md](CHANGELOG.md) – sekce „Co nahrát na FTP" pro každou verzi.
