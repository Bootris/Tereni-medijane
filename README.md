# Advokatska kancelarija — sajt

Spreman sajt za advokatsku kancelariju sa admin panelom (Filament v4). Ovo je prva
implementacija ponovo upotrebljivog "site-core" šablona: Laravel monolit, SQLite,
sadržaj se uređuje kroz admin, frontend je Blade + Tailwind.

## Brzi start

```bash
./start.sh              # pokreće sajt na http://127.0.0.1:8000
PORT=8080 ./start.sh    # na drugom portu
```

Skripta sama odradi: `composer install`, `npm run build`, migracije i `storage:link`
ako nešto od toga nedostaje.

## Provera da li backend radi

```bash
./check-backend.sh          # brza provera: PHP ekstenzije, baza, sve HTTP rute
./check-backend.sh --full   # + kompletan test suite (php artisan test)
```

Skripta podiže privremeni server na portu 8123, proveri sve rute i ugasi ga.
Izlazni kod je 0 ako je sve u redu (može u CI).

## Admin panel

| | |
|---|---|
| URL | `http://127.0.0.1:8000/admin` |
| Email | `borisboncic95@gmail.com` |
| Lozinka | `password` |

> ⚠ **Odmah promeni lozinku** (Admin → Users → izmeni korisnika). Lozinka dolazi iz
> `SEED_ADMIN_PASSWORD` env promenljive pri seed-ovanju — podrazumevano je `password`.

**Uloge:** `admin` (sve) i `editor` (samo sadržaj — ne vidi Users ni Settings).

**Šta se uređuje u adminu:**
- **Articles** — blog tekstovi: rich-text editor sa slikama, istaknuta slika,
  YouTube/Vimeo link (automatski embed), kategorija, draft/objavljeno + zakazivanje
  (`published_at` u budućnosti), SEO polja
- **Categories** — kategorije bloga (filteri na `/blog`)
- **Team** — profili tima: fotografija, pozicija, biografija, kontakti, redosled prevlačenjem
- **Inbox** — poruke sa kontakt forme (badge za nepročitane, odgovor na email)
- **Users** — nalozi i uloge (samo admin)
- **Site settings** — naziv sajta, telefon, email, adresa, radno vreme, društvene
  mreže, Google Maps embed, Calendly link (samo admin)

## Ručno pokretanje (bez skripte)

```bash
composer install
cp .env.example .env          # SQLite je podrazumevan — DB_* kredencijali nisu potrebni
php artisan key:generate
touch database/database.sqlite # kreiraj praznu SQLite bazu (fajl)
php artisan migrate --seed    # seed: admin korisnik, kategorije, podrazumevana podešavanja
php artisan storage:link
npm install && npm run build
php artisan serve
```

## Struktura

- **Frontend:** `/{sr|en}` početna (hero, oblasti prakse, tim, blog, kontakt),
  `/blog` + `/blog/{slug}`, `/sitemap.xml`. Kontakt forma šalje na `/contact`
  (honeypot + rate limit), čuva poruku u Inbox i šalje email.
- **Dizajn tokeni:** boje i fontovi u `resources/css/app.css` (`@theme` blok) —
  rebrend za novog klijenta je uglavnom izmena tu + tekstovi u
  `resources/lang/{sr,en}/lawyer.php`.
- **Baza:** SQLite — jedan fajl `database/database.sqlite` (bez servera, bez
  kredencijala; otvara se i u TablePlus-u kao SQLite). Tabele `posts`, `categories`,
  `team_members`, `contact_messages`, `settings` (key/value, dostupno u svim
  view-ovima kao `$site`). Prebacivanje na MySQL: `DB_CONNECTION=mysql` + `DB_*` u `.env`.
- **Stari YAML sadržaj** (iz `storage/content/posts/`) može ponovo da se uveze:
  `php artisan site:import-yaml` (idempotentno).

## Testovi

```bash
php artisan test
```

Feature testovi pokrivaju javne stranice, kontakt formu (uključujući honeypot) i
admin panel (uloge, sve resurse). Testovi koriste zasebnu bazu `site_test`
(podešeno u `phpunit.xml`) — ne diraju pravu bazu.

## Produkcija

- Postavi nasumičan `ADMIN_PATH` (npr. `admin-x7k2p9`) u `.env`.
- Postavi prave `MAIL_*` SMTP kredencijale (lokalno mail ide u `storage/logs/laravel.log`).
- `APP_URL` mora biti tačan da bi URL-ovi slika radili.
- Redovan backup SQLite baze — samo kopiraj fajl `database/database.sqlite`
  (npr. cron `cp` ili `litestream` za kontinuirani backup).
