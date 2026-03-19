# Tymka — Product Requirements Document

**Platforma pro správu sportovních týmů a kroužků**
MVP Specification — verze 1.1 — 18. března 2026
Michal

---

## 1. Úvod

### 1.1 Účel dokumentu

Tento Product Requirements Document (PRD) definuje funkční a technické požadavky pro MVP platformy Tymka — moderní řešení pro správu sportovních týmů a kroužků. Dokument slouží jako hlavní referenční bod pro vývoj, design a testování produktu.

### 1.2 Kontext projektu

Tymka vzniká jako odpověď na identifikované mezery v existujících řešeních na českém trhu (EOS, Týmuj, Spond). Klíčovým diferenciátorem je cross-sport rodičovský dashboard, smart nominace s deadlines a moderní mobile-first UX. Detailní competitive analysis je v samostatném dokumentu.

### 1.3 Cílový trh

Primárně Česká republika a Slovensko. Mládežnické sportovní oddíly (fotbal, plavání, florbal, basketbal, atletika aj.), TJ/Sokol organizace a zájmové kroužky. Sekundárně dospělé amatérské týmy.

### 1.4 Cílové persony

- **Rodič (primární uživatel):** Má 1–3 děti na různých sportech/kroužcích. Potřebuje jednotný přehled, rychle potvrdit účast, vědět kdy/kde/co vzít. Nechce instalovat 5 appek. Často méně tech-savvy — musí fungovat i přes email.
- **Trenér / vedoucí:** Spravuje 1–3 týmy. Potřebuje plánovat tréninky, nominovat na zápasy, evidovat docházku, komunikovat s rodiči. Chce minimum administrativy. Může být zároveň rodič jiného dítěte v klubu.
- **Správce klubu / owner:** Zodpovídá za celý klub — více týmů, příspěvky, GDPR, dotace. Potřebuje přehled o členské základně a financích.
- **Sportovec (starší dítě):** Teenager, který si chce sám spravovat svůj kalendář. Postupná autonomie — od plné rodičovské správy po vlastní login.
- **Organizátor rekreačního sportu:** Dospělý, který organizuje pravidelný florbal/volejbal/fotbálek pro partu kamarádů. Potřebuje: kdo přijde, automatické rozpočítání pronájmu tělocvičny podle docházky, vyúčtování na konci měsíce. Často je zároveň rodič dětí v jiných klubech → cross-sport dashboard.
- **Vedoucí nesportovního kroužku:** Skauti, šachy, modeláři, hasiči-mládež. Nemají zápasy ani nominace, ale potřebují: pravidelné schůzky, docházku, členské příspěvky, GDPR souhlasy, komunikaci s rodiči.

---

## 2. Produktová vize

### 2.1 Positioning statement

Pro rodiče sportujících dětí, trenéry mládežnických týmů a organizátory rekreačního sportu v ČR/SK, kteří potřebují přehledně řídit tréninky, zápasy, nominace, příspěvky a pronájmy. Na rozdíl od EOS (komplexní, ale těžkopádný a drahý), Týmuj (jednoduchý, ale omezený) a WhatsApp (chaos), Tymka nabízí moderní, sportově specifickou platformu s jedinečným rodičovským dashboardem napříč všemi sporty dětí — a zároveň řeší i potřeby dospělých rekreačních skupin.

### 2.2 Klíčové diferenciátory Tymka

- **Cross-sport rodičovský dashboard:** Jeden login, všechny sporty všech dětí. Filtrovaný kalendář s barevným odlišením klubů/týmů. Personalizované iCal feedy sdílitelné s partnerem.
- **Smart nominace s deadlines:** Trenér nominuje → hráči potvrzují → připomínkový řetězec 48h/12h → auto-posun náhradníků → finální sestava. Žádný WhatsApp chaos.
- **Dvouvrstvá docházka:** RSVP předem + reálná prezenčka trenérem na místě. Statistika spolehlivosti (řekl že přijde a přišel vs. nepřišel). Hromadné omluvenky na období.
- **Sdílená identita:** Jeden user = trenér U7 + rodič v U12 + sportovec v chlapech. Kontextové role per tým, ne per účet.
- **Email response:** Rodič potvrdí účast jedním klikem z emailu bez nutnosti otevřít appku. Signed URL, žádný login. Klíčové pro adopci.
- **Freemium self-service:** Free tier bez kontaktu s obchodníkem, self-registrace, ceník na webu. Žádný aktivační poplatek.
- **Czech-native:** České banky (QR platby), GDPR souhlasy, sportově specifické šablony a checklisty. Nativní, ne lokalizace.

---

## 3. Technická architektura

### 3.1 Tech stack

| Vrstva | Technologie | Poznámka |
|--------|-------------|----------|
| Backend | Laravel 12 / PHP 8.4 | Sdílený stack s Chireo, Sanctum pro API auth |
| Databáze | MySQL 8 | Single DB, club_id scopování, sdílená users vrstva |
| Frontend | Blade + Alpine.js + Tailwind CSS + Livewire | Reactive komponenty |
| Mobilní | PWA | Nativní appky (RN/Flutter) ve fázi 2 |
| Push notifikace | Firebase Cloud Messaging | + Laravel Notifications |
| Geocoding | LocationIQ / Geoapify | OSM-based, free tier 5 000 req/den |
| Mapy | Leaflet.js | Náhled v detailu události |
| Platby | QR Payment standard | FIO API auto-párování ve fázi 2 |
| Hosting | Laravel Forge + Hetzner | EU GDPR-compliant |
| Storage | S3-compatible | Pro fotky a dokumenty |
| iCal | .ics feedy | Signed URL per uživatel |
| Email | Postmark / Mailgun | Signed URL pro one-click response |

### 3.2 Multi-tenancy model

Single database s `club_id` foreign key na všech club-scoped tabulkách. Globální entity (`users`, `user_guardians`, `conversations`, `messages`) žijí mimo club scope — umožňují cross-club rodičovský dashboard. Laravel global scope na Eloquent modelech zajišťuje automatické filtrování per klub.

Důvod volby single DB: potřeba cross-tenant queries pro rodičovský dashboard (rodič vidí data z více klubů najednou). Separate DB by vyžadovalo cross-database JOINy nebo synchronizaci.

### 3.3 Autentizace

- **Klasický login:** Email + heslo pro trenéry a správce
- **Magic link:** Alternativní login pro rodiče — klikni na link v emailu, jsi přihlášený. Žádné heslo k zapamatování.
- **Signed URL response:** One-click potvrzení účasti přímo z emailu. Nevyžaduje přihlášení, token je unikátní a časově omezený.
- **QR kód onboarding:** Trenér ukáže QR kód na tréninku, rodič naskenuje a je v týmu.

### 3.4 Lokalizace (i18n)

Tymka je od prvního dne budováno jako dvojjazyčná aplikace s architekturou připravenou na další jazyky.

- **Primární jazyk:** čeština (cs)
- **Sekundární jazyk:** angličtina (en)
- **Přepínač jazyka:** V UI (header/footer) + v `users.locale` (ukládá se per uživatel)
- **Implementace:** Laravel lang files (`/lang/cs/*.php`, `/lang/en/*.php`). Všechny UI stringy v překladových tabulkách — žádný hardcoded český text v Blade šablonách, komponentách ani JS.
- **Blade šablony:** Výhradně `{{ __('messages.key') }}` nebo `@lang('messages.key')`. Nikdy ne `{{ "Český text" }}`.
- **JavaScript/Alpine:** Překlad přes `window.translations` objekt předaný z backendu, nebo Blade `@json` pro inline stringy.
- **Email šablony:** Paralelní verze per locale (`emails/cs/event_created.blade.php`, `emails/en/event_created.blade.php`).
- **Validační hlášky:** Laravel výchozí lang soubory pro cs/en.
- **Datumové formáty:** cs = `d.m.Y`, en = `Y-m-d` / `M d, Y`. Carbon locale.
- **Příprava na další jazyky:** SK (slovenština), DE (němčina), PL (polština). Přidání nového jazyka = zkopírování lang adresáře + překlad. Žádné změny v kódu.
- **Klubový default:** `clubs.settings.default_locale` — jazyk pro nové členy, kteří se přidají do klubu. Člen si může přepnout individuálně.

### 3.5 Design systém a brand

**Barevné schéma: "Field Green"**

| Token | Hex | Použití |
|-------|-----|---------|
| `--color-primary` | #1B6B4A | Primární akce, navigace, aktivní stavy |
| `--color-primary-dark` | #0F4A32 | Sidebar, tmavé pozadí, hover na primární |
| `--color-primary-light` | #E8F5EE | Badge pozadí, vybrané řádky, success light |
| `--color-accent` | #E8793A | CTA tlačítka, notifikace, upozornění, oranžové badge |
| `--color-accent-light` | #FFF3EC | Accent badge pozadí, warning light |
| `--color-text` | #1A1A18 | Primární text |
| `--color-text-secondary` | #6B6B65 | Sekundární text, placeholdery |
| `--color-bg` | #F7F7F5 | Pozadí stránky |
| `--color-surface` | #FFFFFF | Karty, modály, input pozadí |
| `--color-border` | #E8E8E3 | Okraje, oddělovače |

**Typografie:**
- Font: Inter (400/500/600) — fallback system-ui, sans-serif
- H1: 24px/600, H2: 20px/600, H3: 16px/500, Body: 14px/400
- Monospace (kód, VS): JetBrains Mono

**Komponenty:**
- Border radius: 8px (inputy, badge), 12px (karty), 16px (modály)
- Shadows: žádné v MVP — flat design. Pouze focus ring na inputech.
- Spacing scale: 4/8/12/16/24/32/48px

---

## 4. Datový model

Celkem 47 tabulek pro MVP-core, 4 tabulky pro MVP-extended (galerie, výsledky zápasů).

### 4.1 Globální entity (bez club_id)

#### `users`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| first_name | string | |
| last_name | string | |
| nickname | string nullable | Přezdívka, volitelné |
| email | string UK nullable | Nullable pro děti bez loginu |
| phone | string nullable | |
| password_hash | string nullable | Nullable pro děti a magic-link uživatele |
| avatar_path | string nullable | Profilová fotka, viditelná jen se souhlasem GDPR |
| address | string nullable | Dobrovolné |
| birth_date | date nullable | |
| is_minor | boolean | Automaticky z birth_date |
| can_self_manage | boolean | false dokud nemá email+heslo; rodič spravuje |
| locale | enum | cs / sk / en |
| notification_preferences | json nullable | Per-tým nastavení push/email/nic, tichý režim |
| created_at | timestamp | |

#### `user_guardians`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| guardian_id | uuid FK → users | Rodič / zákonný zástupce |
| child_id | uuid FK → users | Dítě (is_minor = true) |
| relationship | enum | mother / father / guardian / other |
| is_primary | boolean | Primární kontakt pro trenéra |
| created_at | timestamp | |

> Více guardianů per dítě. Oba rodiče mají stejná práva — oba vidí kalendář, oba potvrzují účast, oba můžou přepsat odpověď druhého (s notifikací). Dítě je taky user — s `is_minor=true` a `can_self_manage=false` dokud si nepřidá email.

#### `user_preferences`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| user_id | uuid FK | |
| key | string | calendar_feeds, default_view, filters... |
| value | json | Konfigurovatelná nastavení per uživatel |

#### `conversations`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| created_at | timestamp | |

#### `conversation_participants`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| conversation_id | uuid FK | |
| user_id | uuid FK | |
| joined_at | timestamp | |
| last_read_at | timestamp nullable | Pro indikátor nepřečtených |

#### `messages`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| conversation_id | uuid FK | |
| sender_id | uuid FK | |
| body | text | |
| created_at | timestamp | |

### 4.2 Kluby a členství

#### `clubs`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| name | string | Název klubu |
| slug | string UK | URL-friendly identifikátor |
| primary_sport | enum nullable | Hlavní sport pro seedování šablon |
| address | string nullable | Sídlo klubu |
| logo_url | string nullable | |
| color | string nullable | HEX barva pro UI odlišení |
| bank_account | string nullable | IBAN pro QR platby |
| settings | json | `assistant_can_create_training` aj. |
| billing_plan | enum | starter / pro / club |
| created_at | timestamp | |

#### `club_memberships`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| user_id | uuid FK | |
| club_id | uuid FK | |
| role | enum | owner / admin / member |
| status | enum | active / pending / suspended |
| joined_at | timestamp | |

> Owner je vždy právě 1 per klub (přenositelný). Admin může být více. Klubová role je nezávislá na týmové roli — member v klubu může být head_coach v týmu.

#### `invitations`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK nullable | Pokud pozvánka přímo do týmu |
| invited_by | uuid FK | |
| email | string | |
| intended_role | enum | head_coach / assistant_coach / athlete |
| status | enum | pending / accepted / expired |
| token | string UK | V URL pozvánky |
| expires_at | timestamp | |
| created_at | timestamp | |

#### `join_requests`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| user_id | uuid FK | Žadatel |
| club_id | uuid FK | |
| team_id | uuid FK nullable | |
| requested_role | enum | |
| message | text nullable | Zpráva pro správce |
| status | enum | pending / approved / rejected |
| reviewed_by | uuid FK nullable | |
| created_at | timestamp | |

### 4.3 Týmy a sezóny

#### `teams`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| season_id | uuid FK nullable | |
| name | string | U7 přípravka, Plavci 2018... |
| sport | enum | football / swimming / athletics / floorball / basketball / volleyball / futsal / water_polo / tennis / other |
| age_category | enum nullable | U7–U19, adults |
| color | string nullable | Override klubové barvy |
| is_active | boolean | |
| is_archived | boolean | Pro sezónní archivaci |

#### `team_memberships`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| team_id | uuid FK | |
| user_id | uuid FK | |
| role | enum | head_coach / assistant_coach / athlete |
| status | enum | active / invited / pending |
| position | string nullable | Brankář, útočník — sportově specifické |
| joined_at | timestamp | |

> Jeden user může mít N záznamů v `team_memberships` s různými rolemi i v rámci jednoho klubu. Head coach může přiřazovat role v rámci svého týmu. Sport je na úrovni týmu, ne klubu.

#### `seasons`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| name | string | 2025/2026 |
| start_date | date | |
| end_date | date | |

### 4.4 Lokace

#### `venues`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| name | string | Hlavní hřiště, Hala TJ... |
| address | string nullable | |
| latitude | decimal nullable | Z geocodingu nebo kliknutí na mapu |
| longitude | decimal nullable | |
| geocoding_source | enum | manual / locationiq / geoapify |
| sport_type | enum nullable | Filtr per sport |
| notes | text nullable | |
| is_favorite | boolean | |
| sort_order | integer | |

> Trenér zadá venue jednou → uloží do oblíbených → příště vybírá ze seznamu (0 API requestů). Malý Leaflet.js náhled + možnost otevřít v Google/Apple Maps.

### 4.5 Události a docházka

#### `events`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | Vždy vyplněný — klub ke kterému událost patří |
| team_id | uuid FK nullable | null = klubová událost (závody, soustředění) |
| venue_id | uuid FK nullable | Ze seznamu venues |
| location | string nullable | Fallback pokud není venue |
| created_by | uuid FK | |
| event_type | enum | training / match / competition / tournament |
| title | string | |
| surface_type | string nullable | grass / turf / hall / pool25 / pool50 |
| starts_at | datetime | UTC v DB, lokální zóna v UI |
| ends_at | datetime | |
| recurrence_rule_id | uuid FK nullable | Pro opakující se události |
| rsvp_deadline | datetime nullable | Měkký deadline pro potvrzení |
| nomination_deadline | datetime nullable | Tvrdý deadline — po něm auto-odmítnutí |
| min_capacity | integer nullable | Minimální počet hráčů |
| max_capacity | integer nullable | Maximální počet |
| instructions | text nullable | Volný text od trenéra |
| notes | text nullable | Interní poznámky |
| status | enum | scheduled / cancelled / rescheduled |
| cancel_reason | text nullable | |
| rescheduled_to | uuid FK nullable | Odkaz na novou událost |
| cancelled_by | uuid FK nullable | |
| cancelled_at | timestamp nullable | |
| created_at | timestamp | |

> **Scope událostí:**
> - **Týmová událost** (`team_id` vyplněný) — trénink, zápas konkrétního týmu. Nominace primárně z daného týmu + cross-team doplnění.
> - **Klubová událost** (`team_id` = null) — klubové závody, soustředění, turnaj. Účastní se hráči z více/všech týmů klubu. Nominace/přihlášky napříč celým klubem.
>
> **Bulk nominace:** Trenér může jedním klikem nominovat celý tým (předvyplní se soupiska), pak odškrtne hráče které nechce a doplní z jiných týmů. U klubových událostí může přidat celé týmy najednou.

#### `recurrence_rules`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK | |
| name | string nullable | "Úterní trénink hala", "Čtvrtek venku" |
| event_type | enum | training / match / meeting / other |
| frequency | enum | weekly / biweekly / monthly_nth_day / monthly_nth_weekday / custom_interval |
| interval | integer | default 1. Pro biweekly=2, každý 3. týden=3 |
| day_of_week | integer | 0=po, 6=ne |
| week_parity | enum nullable | odd / even / null — lichý/sudý týden (ISO week) |
| nth_weekday | integer nullable | 1–5 — pro monthly_nth_weekday: 1. pondělí, 3. pátek atd. |
| time_start | time | |
| time_end | time | |
| venue_id | uuid FK nullable | Výchozí místo |
| surface_type | string nullable | grass / turf / hall / pool25 / pool50 |
| instructions_template_id | uuid FK nullable | Výchozí šablona instrukcí |
| equipment_template_id | uuid FK nullable | Výchozí šablona vybavení |
| auto_create_days_ahead | integer | default 14. Kolik dní předem systém vygeneruje událost |
| auto_rsvp | boolean | default true. Automaticky vytvořit attendance (pending) pro celý tým |
| valid_from | date | Začátek platnosti (typicky začátek sezóny) |
| valid_until | date nullable | Konec platnosti (konec sezóny). null = do odvolání |
| is_active | boolean | default true. Pozastavení bez smazání |
| created_by | uuid FK | |

#### `recurrence_exclusions`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| recurrence_rule_id | uuid FK | |
| excluded_date | date | Konkrétní datum kdy se nehraje |
| reason | string nullable | "Státní svátek", "Prázdniny", "Zavřená hala" |
| created_by | uuid FK | |

> **Auto-generování událostí:**
> - Cron job denně kontroluje `recurrence_rules` kde `is_active=true` a `valid_from <= today + auto_create_days_ahead`
> - Pro každé pravidlo vygeneruje `events` záznamy na příštích N dní (default 14, konfigurovatelné per pravidlo)
> - Přeskočí data v `recurrence_exclusions`
> - Přeskočí české státní svátky (hardcoded / konfigurovatelné per klub v `clubs.settings`)
> - Každé nové události automaticky přiřadí venue, equipment template, instructions template z pravidla
> - Pokud `auto_rsvp=true`, vytvoří `attendances` s `rsvp_status=pending` pro všechny aktivní členy týmu
> - Trenér může vygenerovanou událost individuálně upravit (změna místa, času) bez dopadu na pravidlo
> - Middleware při vytvoření nové události kontroluje `absence_periods` a automaticky nastaví `rsvp_status=declined` pro omluvené hráče
>
> **Podporované vzory opakování:**
> - `weekly` + `interval=1` — každý týden (úterý a čtvrtek = 2 pravidla)
> - `weekly` + `interval=2` — ob týden (biweekly)
> - `weekly` + `week_parity=odd` — jen liché týdny
> - `weekly` + `interval=3` — každý třetí týden
> - `monthly_nth_weekday` + `nth_weekday=1` + `day_of_week=0` — každé první pondělí v měsíci
> - `monthly_nth_weekday` + `nth_weekday=3` + `day_of_week=4` — každý třetí pátek
> - `monthly_nth_day` — každého 15. v měsíci (pro pravidelné schůzky)
>
> **Jeden tým může mít více pravidel** — každé s jiným dnem, časem, místem a šablonou. Typický příklad: FK Zlín U9 má pravidlo "Úterý 17:00 hala" + "Čtvrtek 16:30 venku" + "Sobota 10:00 zápas (jen liché týdny)".

#### `attendances`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| team_membership_id | uuid FK | |
| rsvp_status | enum | confirmed / declined / pending |
| rsvp_note | text nullable | Důvod omluvy |
| responded_by | uuid FK nullable | Kdo odpověděl (rodič nebo sportovec) |
| responded_at | timestamp nullable | |
| actual_status | enum nullable | present / absent — trenér vyplní na místě |
| checked_by | uuid FK nullable | Kdo udělal prezenčku |
| checked_at | timestamp nullable | |

> Dvouvrstvá docházka: `rsvp_status` = odpověď předem, `actual_status` = reálná přítomnost. Kombinace dává statistiku spolehlivosti.

#### `event_reminders`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| remind_before_hours | integer | 48, 12, 2... |
| reminder_type | enum | rsvp / nomination |
| target_filter | enum | all / unanswered / below_capacity |
| sent_at | timestamp nullable | null = ještě neposláno |

#### `nominations`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| team_membership_id | uuid FK | Hráč — může být z jiného týmu než event |
| source_team_id | uuid FK nullable | Původní tým hráče, pokud cross-team nominace |
| status | enum | nominated / accepted / declined / replaced |
| priority | integer | 1 = základní sestava, 2 = náhradník |
| nominated_by | uuid FK | Trenér |
| responded_by | uuid FK nullable | Rodič nebo sportovec |
| responded_at | timestamp nullable | |

> **Cross-team nominace:** Trenér může nominovat hráče z jiných týmů v rámci klubu (např. hráč z U7 na zápas U9). Tuto akci může provést pouze `head_coach` nebo `admin`. Při nominaci trenér vidí "Můj tým" (default) + "Přidat z jiného týmu" (výběr dalšího týmu v klubu). `source_team_id` umožňuje sledovat, odkud hráč přišel — důležité pro statistiky a přehledy.

#### `absence_periods`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| created_by | uuid FK | Rodič / sportovec |
| reason_type | enum | illness / vacation / injury / other |
| reason_note | text nullable | |
| starts_at | date | |
| ends_at | date | |
| apply_to_teams | json nullable | null = všechny týmy |
| created_at | timestamp | |

#### `absence_period_members`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| absence_period_id | uuid FK | |
| user_id | uuid FK | Dítě / sportovec |
| team_membership_id | uuid FK nullable | null = všechny týmy uživatele |

### 4.6 Vybavení a instrukce

#### `equipment_templates`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | Patří klubu, plně editovatelné |
| event_type | enum | training / match / competition |
| name | string | Venkovní zápas fotbal, Trénink plavání... |
| sort_order | integer | |

#### `equipment_template_items`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| template_id | uuid FK | |
| label | string | Kopačky, chrániče, plavky, ploutve... |
| is_default | boolean | Předvybrané vs. volitelné |
| sort_order | integer | |

#### `event_equipment`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| label | string | Konkrétní položka pro tuto událost |
| is_required | boolean | |

#### `instruction_templates`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | Plně editovatelné, seedované při onboardingu |
| event_type | enum | |
| name | string | Venkovní zápas, Trénink v hale... |
| body | text | Text s placeholdery: Sraz: [čas], Odjezd: [čas]... |
| sort_order | integer | |

> Šablony se seedují při vytvoření klubu, ale jsou plně konfigurovatelné — žádné systémové položky natvrdo. Klub si může komplet přestavět checklisty i instrukční šablony.

### 4.7 Komunikace

#### `event_comments`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| user_id | uuid FK | |
| body | text | |
| created_at | timestamp | |

#### `event_comment_watchers`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| user_id | uuid FK | |
| watching_since | timestamp | Auto-přidání při napsání komentáře |

#### `team_posts`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| team_id | uuid FK | |
| user_id | uuid FK | |
| body | text | |
| post_type | enum | message / poll |
| created_at | timestamp | |

#### `team_post_comments`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| post_id | uuid FK | |
| user_id | uuid FK | |
| body | text | |
| created_at | timestamp | |

#### `poll_options`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| post_id | uuid FK | |
| label | string | |
| sort_order | integer | |

#### `poll_votes`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| option_id | uuid FK | |
| user_id | uuid FK | |
| created_at | timestamp | |

### 4.8 GDPR a souhlasy

#### `consent_types`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| name | string | Souhlas s focením, GDPR zpracování... |
| description | text | Plný text souhlasu |
| is_required | boolean | Povinný pro členství |
| sort_order | integer | |

#### `consents`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| consent_type_id | uuid FK | |
| user_id | uuid FK | Kdo udělil (rodič) |
| child_id | uuid FK nullable | Za koho (dítě) |
| granted | boolean | |
| granted_by | uuid FK | |
| granted_at | timestamp | |
| revoked_at | timestamp nullable | |

> Rodič v portálu vidí sekci Souhlasy kde odklikne per dítě. Bez souhlasu s focením se fotky dítěte nezobrazují ostatním.

### 4.9 Platby

#### `payment_requests`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK nullable | null = celý klub |
| created_by | uuid FK | |
| name | string | Příspěvky podzim 2026 |
| description | text nullable | |
| amount | decimal | Částka v CZK |
| currency | string | CZK default |
| payment_type | enum | membership / one_time |
| due_date | date | |
| variable_symbol_prefix | string | Pro generování unikátních VS |
| bank_account | string | IBAN — z klubu nebo override |
| status | enum | active / closed / cancelled |
| created_at | timestamp | |

#### `member_payments`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| payment_request_id | uuid FK | |
| user_id | uuid FK | Kdo platí (rodič) |
| child_id | uuid FK nullable | Za koho (dítě) |
| variable_symbol | string UK | Unikátní VS pro párování |
| amount | decimal | |
| status | enum | pending / paid / overdue / cancelled |
| paid_at | timestamp nullable | |
| confirmed_by | uuid FK nullable | Admin který odklikl zaplaceno |
| thanked_at | timestamp nullable | Odesláno poděkování |
| qr_payload | text | Cached QR Payment string |
| notes | text nullable | |
| created_at | timestamp | |

#### `payment_receipts`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| member_payment_id | uuid FK | |
| file_path | string | Cesta k PDF |
| generated_at | timestamp | |

> Admin vytvoří `payment_request` → systém hromadně vygeneruje `member_payments` → každý s unikátním VS → rodič vidí QR kód → zaplatí → admin odklikne zaplaceno (s volitelným poděkováním) → rodič stáhne PDF potvrzení.

### 4.10 Vyúčtování pronájmu (rozpočítání nákladů)

Funkce primárně pro rekreační skupiny dospělých, ale použitelná i pro oddíly co platí pronájem haly.

#### `venue_costs`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK nullable | null = celý klub |
| name | string | "Pronájem haly ZŠ Zlín", "Umělka Malenovice" |
| cost_per_event | decimal | Náklady za jednu akci (např. 1200 Kč za halu) |
| currency | string | default CZK |
| split_method | enum | per_attendance / equal_monthly / fixed_per_member |
| billing_period | enum | monthly / seasonal / per_event |
| include_event_types | json | ["training"] — jaké typy událostí se počítají |
| bank_account | string nullable | IBAN — pokud jiný než klubový |
| is_active | boolean | default true |
| created_by | uuid FK | |

#### `venue_cost_settlements`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| venue_cost_id | uuid FK | |
| period_from | date | Začátek zúčtovacího období |
| period_to | date | Konec |
| total_events | integer | Kolik tréninků proběhlo |
| total_cost | decimal | total_events × cost_per_event |
| total_attendances | integer | Celkem člověko-účastí (z actual_status=present) |
| cost_per_attendance | decimal | total_cost / total_attendances |
| status | enum | draft / sent / settled |
| generated_at | timestamp | |
| sent_at | timestamp nullable | Kdy rozesláno členům |
| created_by | uuid FK | |

#### `venue_cost_member_shares`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| settlement_id | uuid FK | |
| user_id | uuid FK | Člen |
| attendance_count | integer | Kolikrát přišel (actual_status=present) |
| amount_due | decimal | attendance_count × cost_per_attendance |
| variable_symbol | string | Pro párování platby |
| qr_payload | string nullable | Cached QR string |
| status | enum | pending / paid / cancelled |
| paid_at | timestamp nullable | |
| confirmed_by | uuid FK nullable | |

> **Flow vyúčtování pronájmu:**
> 1. Admin nastaví `venue_costs` — hala stojí 1200 Kč/trénink, rozpočítat podle docházky, měsíční vyúčtování
> 2. Na konci měsíce (nebo manuálně) admin klikne "Vyúčtovat období"
> 3. Systém spočítá: 8 tréninků × 1200 = 9600 Kč, celkem 47 účastí (+ 3 penalizované) = 50 podílů → 192 Kč/podíl
> 4. Pro každého člena: Pepa přišel 7× = 1344 Kč, Honza 3× = 576 Kč, Karel (2× neomluvená absence) = 384 Kč
> 5. Admin zkontroluje draft → potvrdí → systém rozešle notifikaci s QR kódem
> 6. Členové zaplatí převodem, admin potvrdí (nebo FIO API auto-match v Phase 2)
>
> **Split metody:**
> - `per_attendance` — každý platí poměrně podle toho, kolikrát přišel + penalizované absence (default pro rekreační party)
> - `equal_monthly` — náklady se dělí rovným dílem mezi všechny aktivní členy bez ohledu na docházku
> - `fixed_per_member` — fixní částka za hlavu za trénink (např. 90 Kč/osoba, přebytek/schodek se akumuluje)
>
> **Důležité:** Vyúčtování se počítá z `actual_status=present` (reálná prezenčka) + penalizované absence, ne z RSVP. Kdo přišel jako náhradník, platí.

#### `penalty_rules`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK nullable | null = platí pro celý klub |
| name | string | "Pokuta za neomluvený trénink" |
| trigger | enum | no_show / late_cancel / no_response |
| penalty_type | enum | count_as_attended / fixed_amount / percentage_surcharge |
| amount | decimal nullable | Pro fixed_amount (např. 200 Kč), pro percentage_surcharge (např. 150 = 1.5×) |
| late_cancel_hours | integer nullable | Kolik hodin před tréninkem je omluva "pozdní" (např. 4 = omluva míň než 4h předem) |
| grace_count | integer | Kolik prohřešků za období je bez pokuty (default 0) |
| is_active | boolean | default true |
| created_by | uuid FK | |

#### `penalties`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| penalty_rule_id | uuid FK | |
| event_id | uuid FK | Ke kterému tréninku |
| user_id | uuid FK | Komu |
| trigger_type | enum | no_show / late_cancel / no_response |
| original_rsvp | enum nullable | confirmed / pending — co řekl předem |
| amount | decimal nullable | Konkrétní částka (pro fixed_amount) |
| count_as_attendance | boolean | Má se počítat jako účast pro vyúčtování? |
| waived | boolean | default false — admin může prominout |
| waived_by | uuid FK nullable | |
| waived_reason | string nullable | |
| created_at | timestamp | |

> **Systém pokut — tři základní triggery:**
>
> 1. **No-show** (`no_show`): Hráč potvrdil účast (rsvp=confirmed), ale na tréninku nebyl (actual_status=absent). Neomluvená absence.
> 2. **Pozdní omluva** (`late_cancel`): Hráč se omluvil méně než N hodin před tréninkem (konfigurovatelné, default 4h). Skupina s ním počítala, teď je málo hráčů.
> 3. **Žádná odpověď** (`no_response`): Hráč vůbec nereagoval na pozvánku (rsvp zůstal pending do začátku události). Organizátor nevěděl, s kým počítat.
>
> **Typy penalizace:**
> - `count_as_attended` — nejběžnější pro rekreační party. Neomluvená absence se počítá jako účast ve vyúčtování. "Neomluvilses? Platíš jako bys přišel." Snižuje to cenu pro ostatní.
> - `fixed_amount` — pevná pokuta (např. 200 Kč). Jde do společné kasy / snižuje náklady ostatním.
> - `percentage_surcharge` — příplatek k běžné ceně za účast (např. 150 % = platí 1.5× normální podíl).
>
> **Grace count:** Admin může nastavit 1–2 "bezplatné" prohřešky za období. "Jednou za měsíc se může stát, podruhé platíš."
>
> **Waive (prominutí):** Admin může individuálně prominout pokutu s důvodem (např. "byl v nemocnici").
>
> **Integrace s vyúčtováním:** Při generování `venue_cost_settlements` systém automaticky načte `penalties` za dané období a přičte `count_as_attendance` podíly k celkovému výpočtu, nebo přidá fixed_amount k member_share částce.

### 4.11 iCal feedy

#### `calendar_feeds`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| user_id | uuid FK | |
| name | string | Můj fotbalový kalendář, Emin plavání... |
| token | string UK | Kryptograficky náhodný, v URL feedu |
| include_teams | json | Array team_ids |
| include_event_types | json | Array event_types |
| is_default | boolean | Výchozí feed — automaticky vytvořený |
| is_active | boolean | Deaktivace = přegenerování tokenu |
| created_at | timestamp | |

> U zápasů se do feedu zahrnují jen ty, kde je dítě přihlášené/nominované. Název události v kalendáři obsahuje jméno dítěte: "Tomáš — FK Zlín vs. SK Kroměříž". Feed URL je sdílitelný bez registrace.

### 4.12 Audit a notifikace

#### `attendance_log`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| team_membership_id | uuid FK | |
| changed_by | uuid FK | Kdo změnil |
| old_status | string | |
| new_status | string | |
| changed_at | timestamp | Trigger pro notifikaci druhému rodiči |

#### `event_changes_log`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| changed_by | uuid FK | |
| field_name | string | starts_at / location / status... |
| old_value | text nullable | |
| new_value | text nullable | |
| changed_at | timestamp | |

#### `notifications`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| user_id | uuid FK | Příjemce |
| type | string | event_created / nomination / rsvp_changed... |
| channel | enum | push / email / in_app |
| payload | json | Kontext: event_id, team_id, child_name... |
| read_at | timestamp nullable | |
| sent_at | timestamp nullable | |
| created_at | timestamp | |

### 4.13 MVP-extended tabulky (měsíc po launchi)

#### `albums`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| club_id | uuid FK | |
| team_id | uuid FK nullable | |
| event_id | uuid FK nullable | |
| title | string | |
| created_by | uuid FK | |
| created_at | timestamp | |

#### `photos`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| album_id | uuid FK | |
| uploaded_by | uuid FK | |
| file_path | string | S3 storage |
| thumbnail_path | string | |
| caption | string nullable | |
| created_at | timestamp | |

#### `event_results`

| Column | Type | Notes |
|--------|------|-------|
| id | uuid PK | |
| event_id | uuid FK | |
| score_home | integer nullable | |
| score_away | integer nullable | |
| opponent_name | string nullable | |
| result | enum nullable | win / loss / draw |
| notes | text nullable | |
| recorded_by | uuid FK | |
| created_at | timestamp | |

> Fotky dítěte se zobrazují pouze pokud má aktivní GDPR souhlas s focením a zveřejňováním.

---

## 5. Permission model

Pro MVP jsou role fixní s přednastavenými právy. Granulární permission systém přijde ve fázi 2. Konfigurovatelné per klub: zda assistant coach může vytvářet tréninky.

### 5.1 Klubové role (`club_memberships.role`)

- **owner:** Plná správa klubu. Billing, přidávání/odebírání adminů, převod vlastnictví. Vždy právě 1 per klub.
- **admin:** Správa týmů, členů, plateb, GDPR. Může vše kromě billingu a správy adminů.
- **member:** Základní členství. Žádná admin práva na úrovni klubu.

### 5.2 Týmové role (`team_memberships.role`)

- **head_coach:** Hlavní trenér. Zakládá tréninky a zápasy, nominuje, dělá prezenčku, spravuje soupisku, přiřazuje role v rámci svého týmu.
- **assistant_coach:** Pomáhá trenérovi. Vidí docházku. Může zakládat tréninky pokud klubové nastavení povoluje (`clubs.settings.assistant_can_create_training`). Nemůže nominovat.
- **athlete:** Hráč / sportovec. Vidí své události, potvrzuje účast (pokud `can_self_manage`). U malých dětí vše dělá guardian.

### 5.3 Permission matice

| Akce | Owner | Admin | Head coach | Asst. coach | Rodič |
|------|-------|-------|------------|-------------|-------|
| Upravit klub | ✓ | ✓ | — | — | — |
| Spravovat billing | ✓ | — | — | — | — |
| Přidat/odebrat adminy | ✓ | — | — | — | — |
| Převést vlastnictví | ✓ | — | — | — | — |
| Vytvořit/smazat tým | ✓ | ✓ | — | — | — |
| Přidat/odebrat členy týmu | ✓ | ✓ | ✓ (svůj tým) | — | — |
| Přiřadit role v týmu | ✓ | ✓ | ✓ (svůj tým) | — | — |
| Vytvořit trénink | ✓ | ✓ | ✓ (svůj tým) | ⚙ konf. | — |
| Vytvořit zápas/soutěž | ✓ | ✓ | ✓ (svůj tým) | — | — |
| Nominovat na zápas | ✓ | ✓ | ✓ (svůj tým) | — | — |
| Cross-team nominace | ✓ | ✓ | ✓ (v rámci klubu) | — | — |
| Potvrdit/odmítnout nominaci | — | — | — | — | ✓ (své dítě) |
| Zobrazit docházku týmu | ✓ | ✓ | ✓ (svůj tým) | ✓ (svůj tým) | — |
| Potvrzení účasti (RSVP) | ✓ | ✓ | ✓ | ✓ | ✓ (své dítě) |
| Psát na týmovou zeď | ✓ | ✓ | ✓ (svůj tým) | ✓ (svůj tým) | ✓ (své týmy) |
| Psát na klubovou zeď | ✓ | ✓ | — | — | — |

> ⚙ konf. = konfigurovatelné per klub v `clubs.settings`

---

## 6. Klíčové uživatelské flows

### 6.1 Registrace a onboarding

#### Flow 1: Self-registrace rodiče

1. Rodič otevře tymka.cz, klikne Registrace
2. Vyplní jméno, email (nebo zvolí magic link)
3. Přidá děti — jméno, příjmení, datum narození
4. Vyhledá klub (autocomplete podle názvu/města)
5. Odešle žádost o přiřazení (`join_request`) s volbou týmu a role
6. Admin/head coach schválí → rodič + dítě jsou v týmu
7. Rodič vidí Tymka dashboard s kalendářem

#### Flow 2: Pozvánka z klubu

1. Admin/head coach zadá email nového člena a roli
2. Tymka odešle email s invite linkem (token)
3. Nový uživatel klikne → dokončí registraci
4. Automaticky přiřazen do týmu se správnou rolí

#### Flow 3: QR kód na tréninku

1. Trenér v Tymka otevře tým → Přidat členy → zobrazí QR kód
2. Rodič naskenuje QR kód mobilem
3. Otevře se registrace s předvyplněným klubem a týmem
4. Rodič dokončí registraci + přidá dítě → je v týmu

### 6.2 Plánování události

#### Flow: Vytvoření tréninku

**A) Jednorázový trénink:**
1. Trenér otevře tým → Nová událost → Trénink
2. Vybere místo ze seznamu oblíbených (venues) nebo zadá nové
3. Nastaví datum, čas, povrch/typ
4. Volitelně: vybere instrukční šablonu → předvyplní se text → upraví
5. Volitelně: vybere checklist šablonu → předvyplní se vybavení → upraví
6. Uloží → push + email všem členům týmu + rodičům

**B) Opakující se trénink (recurrence rule):**
1. Trenér otevře tým → Rozvrh tréninků → Přidat pravidelný trénink
2. Vybere vzor opakování:
   - Každý týden (např. každé úterý)
   - Ob týden / každý N-tý týden
   - Lichý/sudý týden
   - Každé N-té [pondělí/úterý/...] v měsíci (1. pondělí, 3. pátek...)
   - Každého N. dne v měsíci (pro pravidelné schůzky)
3. Nastaví čas od–do, místo, povrch
4. Vybere šablony (instrukce, vybavení) — budou se automaticky kopírovat do každé generované události
5. Nastaví "generovat X dní dopředu" (default 14)
6. Nastaví platnost: od–do (typicky začátek a konec sezóny)
7. Volitelně: vyloučí konkrétní data (svátky, prázdniny) — nebo zapne "přeskočit české svátky"
8. Uloží → systém okamžitě vygeneruje tréninky na příštích N dní → poté cron job denně dogenerovává

**Správa rozvrhu:**
- Tým může mít neomezený počet pravidel — typicky 2–3 (úterý hala, čtvrtek venku, sobota zápasy)
- Každé pravidlo má vlastní místo, čas a šablony
- Trenér vidí "Rozvrh" jako přehlednou tabulku: Po–Ne × čas × místo
- Individuální úprava jedné vygenerované události (změna místa, času) neovlivní pravidlo
- Pozastavení pravidla (is_active=false) zastaví generování bez smazání budoucích událostí
- Smazání pravidla nabídne: "Smazat i budoucí nevyřešené události?" (s volbou)

#### Flow: Vytvoření zápasu s nominací

1. Trenér otevře tým → Nová událost → Zápas
2. Vyplní místo (s mapovým náhledem), soupeře, datum, čas
3. Nastaví `nomination_deadline` (např. pátek 18:00)
4. Nastaví min/max kapacitu (11 základ, 18 max)
5. Vyplní instrukce (sraz, odjezd, předpokládaný návrat, vybavení)
6. Nominace hráčů:
   - Klikne **"Nominovat celý tým"** → předvyplní se celá soupiska
   - Odškrtne hráče, které nechce (zranění, neaktivní, nevhodní)
   - Klikne **"Přidat z jiného týmu"** → vybere tým v rámci klubu → zaškrtne hráče
   - Nastaví priority: základ (1) vs. náhradník (2) — drag & drop nebo číslo
7. Uloží → nominovaní dostanou push + email s tlačítky Potvrzuji / Omlouvám se
8. Připomínkový řetězec: 48h před deadline → 12h → 2h
9. Po deadline: neodpovězení = auto-odmítnuto, náhradníci se posunou
10. Trenér dostane finální sestavu

#### Flow: Vytvoření klubové události (závody, soustředění, turnaj)

1. Admin nebo trenér otevře klub → Nová událost → Závody / Soustředění / Turnaj
2. Událost není vázaná na konkrétní tým (`team_id` = null)
3. Vyplní místo, datum, čas, instrukce
4. Nominace / přihlášky:
   - Klikne **"Přidat celý tým"** → vybere jeden nebo více týmů → předvyplní se všichni hráči
   - Může opakovat pro další týmy ("Přidat celý tým" → U7, U9, U11)
   - Odškrtne hráče, které nechce
   - Nebo zvolí **"Otevřená přihláška"** — hráči/rodiče se sami přihlásí (vhodné pro soustředění)
5. Nastaví deadline a kapacitu
6. Uloží → notifikace všem dotčeným

### 6.3 Rodičovský dashboard

Výchozí pohled po přihlášení do Tymka pro rodiče. Agregovaný přes všechny kluby a sporty všech dětí.

- **Kalendářový přehled:** Timeline s barevným odlišením per klub/tým. Filtr: Vše / Tomáš / Ema / Moje tréninky U7.
- **Čekající odpovědi:** Badge s počtem neodpovězených událostí a nominací. One-tap potvrzení.
- **Notifikace:** Nové zprávy, změny událostí, nominace. Filtrované per tým.
- **Moje platby:** Přehled zaplaceno/nezaplaceno per dítě. QR kód, PDF potvrzení.
- **Souhlasy:** GDPR a focení per dítě.
- **Omluvenky:** Hromadná omluvenka — výběr dětí, datový rozsah, důvod, které týmy.
- **Kalendářové feedy:** Správa iCal feedů — vytvoření, filtry, sdílení URL s partnerem.

### 6.4 Trenérský dashboard

Výchozí pohled pro uživatele s rolí `head_coach` nebo `assistant_coach`. Zobrazuje všechny týmy kde trenér působí.

- **Moje tréninky a zápasy:** Kalendář událostí napříč všemi týmy, které trenér vede. Barevné odlišení per tým. Filtr: Vše / U7 přípravka / Futsal.
- **Stav potvrzení:** U každé nadcházející události přehled: kolik hráčů potvrdilo / odmítlo / neodpovědělo. Vizuální indikátor kapacity (8/11 potvrzeno).
- **Nominace čekající na odpověď:** Přehled otevřených nominací s countdown do deadline. Kdo ještě neodpověděl.
- **Docházka a spolehlivost:** Rychlý přehled docházky za poslední období. Kdo chodí pravidelně, kdo má neomluvené absence.
- **Rychlé akce:** Vytvořit trénink, vytvořit zápas, zahájit prezenčku — přístupné na 1 klik.
- **Kalendářové feedy:** Trenér si vytvoří iCal feed "Moje tréninky" filtrovaný na týmy kde je coach. Synchronizace s osobním kalendářem.

> Pokud je uživatel zároveň trenér i rodič (běžný scénář), Tymka zobrazí kombinovaný dashboard s přepínačem pohledu: "Jako trenér" / "Jako rodič" / "Vše". Výchozí pohled se nastaví v `user_preferences`.

### 6.5 Změna / zrušení události

#### Změna detailů

1. Trenér/admin upraví událost
2. Tymka zaloguje změnu do `event_changes_log`
3. Všichni s RSVP dostanou push + email se změnami
4. Systém se zeptá: "Potvrzujete účast i na nový termín?"

#### Zrušení události

1. Trenér/admin zruší událost s důvodem (počasí / soupeř odřekl)
2. Událost dostane `status=cancelled`, zůstává viditelná v historii
3. Všichni dostanou push + email se zdůvodněním
4. Trenér může vytvořit náhradní termín (`rescheduled_to`)

### 6.6 Prezenčka na tréninku

1. Trenér na místě otevře událost v Tymka → Zahájit prezenčku
2. Vidí seznam hráčů — potvrzení nahoře, omluvení dole, neodpovězení uprostřed
3. Tapem označí přítomné
4. Neoznačení + rsvp confirmed = neomluvená absence
5. Uloží → data se propíší do `attendances.actual_status`
6. Statistiky spolehlivosti se automaticky přepočítají

---

## 7. Notifikační systém

### 7.1 Kanály

- **Push (FCM):** Primární kanál pro všechny běžné události.
- **Email:** Fallback a formální komunikace. Kritické změny vždy push + email. Obsahuje signed URL pro one-click response.
- **In-app:** Všechny notifikace v `notifications` tabulce. Badge s počtem nepřečtených.

### 7.2 Preference uživatele

Každý uživatel si nastaví per-tým granulární preferences v Tymka nastavení:

- Nová událost: push / email / push+email / nic
- Změna/zrušení události: **vždy push+email** (nelze vypnout)
- Diskuze u události: volitelné sledování per událost
- Nominace: **vždy push+email** (nelze vypnout)
- Připomínky: push / nic
- Týmová zeď: push / nic
- Tichý režim: od–do (default 22:00–07:00)

### 7.3 Deduplikace a spolehlivost

Multi-channel delivery pro kritické události — push + email. Idempotentní doručování (žádné duplicitní notifikace). Kritické změny (zrušení, změna času) nelze vypnout. Potvrzení doručení trackováno v `notifications.sent_at`.

---

## 8. Obchodní model

### 8.1 Freemium pricing

| | Starter (zdarma) | Pro (~299 Kč/měs.) | Club (~799 Kč/měs.) |
|---|---|---|---|
| Počet týmů | 1 | až 5 | neomezeně |
| Počet členů | do 25 | do 150 | neomezeně |
| Tréninky & události | ✓ | ✓ | ✓ |
| Opakující se tréninky | ✓ | ✓ | ✓ |
| Docházka & potvrzení | ✓ | ✓ | ✓ |
| Vyúčtování pronájmu | ✓ | ✓ | ✓ |
| Pokuty & penalizace | ✓ | ✓ | ✓ |
| Nominace | základní | ✓ + smart + cross-team | ✓ + smart + cross-team |
| Platby & QR | ✗ | ✓ | ✓ + auto pár. |
| GDPR & souhlasy | ✗ | ✓ | ✓ |
| Galerie | ✗ | ✓ | ✓ |
| Klubový web | ✗ | ✗ | ✓ |

> **Starter je zdarma a plně funkční pro rekreační party** — 1 tým, 25 členů, tréninky, docházka, vyúčtování pronájmu, pokuty. Pokrývá 100 % potřeb skupiny chlapů co si chodí zahrát florbal. Tito uživatelé jsou zároveň rodiče, kteří vidí aktivity svých dětí v dashboardu a přirozeně doporučí Tymka trenérům svých dětí.
>
> Rodičovský dashboard Tymka je zdarma pro všechny rodiče bez ohledu na plán klubu. Rodič je ambasador, který přirozeně tlačí klub k adopci.

---

## 9. Fázování vývoje

### 9.1 MVP-core (launch)

- Registrace, login (email + magic link), QR onboarding
- Dvojjazyčné UI (CZ/EN) s přepínačem, všechny stringy v lang souborech
- Klub, týmy, sezóny, členství, role (owner/admin/member + coach/athlete)
- Venues s geocodingem (LocationIQ) a mapovým náhledem (Leaflet.js)
- Události — tréninky, zápasy, klubové události (závody, soustředění)
- Opakující se tréninky — plný rozvrh (weekly/biweekly/měsíční/lichý-sudý), auto-generování, výjimky svátků
- Docházka — RSVP + prezenčka trenérem, dvouvrstvé stavy
- Nominace s deadlines, připomínkami, auto-posun náhradníků, bulk nominace celého týmu, cross-team nominace
- Hromadné omluvenky na období
- Instrukční šablony a checklisty vybavení (plně editovatelné)
- Diskuze u událostí s volitelným sledováním
- Týmová zeď s anketami
- Přímé zprávy (DM) 1:1
- GDPR souhlasy (focení, zpracování dat)
- Platby — evidence, QR kód, ruční potvrzení, PDF potvrzení, poděkování
- Vyúčtování pronájmu — rozpočítání nákladů podle docházky, měsíční vyúčtování s QR kódy
- Pokuty a penalizace — no-show, pozdní omluva, žádná odpověď, grace count, prominutí
- iCal feedy — personalizované, filtrované per dítě/sport, sdílitelné, pro trenéry i rodiče
- Notifikace — push + email + in-app, per-tým preferences, tichý režim
- Email one-click response (signed URL)
- Rodičovský dashboard — agregovaný kalendář, filtry per dítě
- Trenérský dashboard — přehled týmů, stav potvrzení, nominace, rychlé akce
- Export docházky do Excelu
- Změna/zrušení událostí s notifikacemi a audit logem
- Statistiky spolehlivosti

### 9.2 MVP-extended (měsíc po launchi)

- Galerie fotek ze zápasů/tréninků (s GDPR respektem)
- Výsledky zápasů / skóre
- Profilové fotky hráčů

### 9.3 Fáze 2 (3–6 měsíců po launchi)

- Nativní iOS + Android appky (React Native / Flutter)
- FIO API — automatické párování plateb
- Smart nominace — AI návrh sestavy podle docházky a rotace
- Google Maps integrace — náhled + navigace přímo v appce
- Statistiky hráčů (góly, body, časy)
- Rezervace sportovišť (klubový booking systém)
- Sdílení dopravy (strukturovaný modul)
- Další sportové šablony

### 9.4 Fáze 3 (12+ měsíců)

- Napojení na sportovní svazy (FAČR, ČSP, ČFbU)
- Klubový web jako modul
- Dotační výstupy (NSA, městské granty — specializované šablony)
- SK lokalizace a expanze
- API pro třetí strany
- SMS notifikace

---

## 10. Go-to-market strategie

### 10.1 Fáze 1: Validace (měsíc 1–2)

- Osobní onboarding 5–10 oddílů ve Zlínském kraji
- Ideálně kluby kde máš děti — autentický feedback loop
- Trenér jako champion — on přivede rodiče, ne naopak
- WhatsApp koexistence — trenér pošle link na událost v Tymka do WhatsApp skupiny

### 10.2 Fáze 2: Lokální trakce (měsíc 3–6)

- Rozšíření do Moravy, online marketing (FB/IG ads cílené na trenéry mládeže)
- SEO — "správa sportovního týmu", "organizace tréninků"
- Cíl: 50 oddílů

### 10.3 Fáze 3: Národní (měsíc 6–18)

- Partnerství se sportovními svazy, PR v médiích
- EOS switch program — 3 měsíce Tymka zdarma pro kluby přecházející z EOS
- Cíl: 500 oddílů

---

## 11. Další kroky

1. **Registrace domén** — tymka.cz + tymka.online, sociální sítě
2. **Wireframy klíčových obrazovek** — Rodičovský dashboard, detail události, nominace, prezenčka
3. **Setup projektu** — Laravel 12 projekt, DB migrace, seedery pro šablony
4. **Implementace jádra** — Users, clubs, teams, memberships, events, attendances
5. **Nominační engine** — Deadlines, připomínky, auto-posun náhradníků
6. **Notifikační systém** — FCM + email + in-app s preferences
7. **Rodičovský dashboard** — Agregovaný kalendář, filtry, iCal feedy
8. **Platby + GDPR** — QR kódy, PDF potvrzení, souhlasy
9. **Alfa testování** — 2–3 oddíly ze Zlínského kraje, iterace
10. **Beta launch** — Otevřený přístup, marketing

---

*Tento dokument je živý a bude průběžně aktualizován. Tymka PRD verze 1.0, březen 2026.*
