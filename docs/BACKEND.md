# Reusable backend (site-core)

Jedan Laravel backend koji služi **sve** klijentske frontend-e. Frontend-agnostičan:
javni sadržaj ide kroz JSON API, uređivanje kroz Filament admin. Menja se samo `.env`
i sadržaj — nikad oblik API-ja.

**Stack:** Laravel 12 · Filament v4 (admin) · SQLite · PHP 8.2 · Sanctum (opciono, za tokene).

## Odgovornosti

- **Public API** (`/api/v1/*`) — read-only sadržaj + prijem kontakt forme. Keširan.
- **Admin** (Filament na `config('site.admin_path')`) — sav CRUD, upload slika, uloge.
- **Media** — `storage:link`, konverzije slika (thumb/hero), servira apsolutne URL-ove.
- **Integracije** — mail (Laravel mailer), Calendly (embed URL u Settings),
  Google Calendar (opcioni modul, faza 6).

## Modeli (core — svaki sajt ih ima)

`Post` · `Category` · `TeamMember` · `ContactMessage` · `Setting` (key/value → brend
i kontakt) · `User` (uloge `admin`/`editor`). Svaki ima Filament resource.
Per-klijent dodaci (npr. `Service`, `GalleryItem`) idu kao opcioni moduli — vidi dole.

## API ugovor (`/api/v1`) — ovo je stabilni interfejs

Sve `GET` rute su javne i keširane; oblik se **ne menja** bez verzije (`v2`).

| Metoda | Ruta | Vraća |
|---|---|---|
| GET | `/api/v1/settings` | brend + kontakt: `{ name, tagline, logo_url, theme:{primary,accent,font}, contact:{email,phone,address,hours}, socials, maps_embed, calendly_url }` |
| GET | `/api/v1/posts?page=&category=&locale=` | paginirano: `{ data:[Post...], meta:{page,total,per_page} }` |
| GET | `/api/v1/posts/{slug}` | jedan `Post` (full body) |
| GET | `/api/v1/categories` | `[{ id, slug, name, count }]` |
| GET | `/api/v1/team` | `[TeamMember...]` (sortirano po `order`) |
| POST | `/api/v1/contact` | prima `{name,email,message,_honeypot}`; throttle `5,1`; → `{ ok:true }` |

**Post oblik:**
```json
{
  "slug": "...", "title": "...", "excerpt": "...", "body_html": "...",
  "cover_url": "https://.../hero.jpg", "video_embed": "https://youtube.com/embed/...",
  "category": { "slug": "...", "name": "..." },
  "published_at": "2026-07-21T10:00:00Z", "seo": { "title": "...", "description": "..." }
}
```

### Pravila API-ja
- **Verzija u putanji** (`/api/v1`). Promena oblika = novi `/api/v2`, stari ostaje.
- **Samo objavljeni sadržaj** (`published_at <= now`, nije draft). Draft preview: token.
- **Keš:** `Cache-Control` + ETag; invalidacija na `Post::saved`/`Setting::saved`.
- **CORS:** dozvoli origine klijentskih frontend-a (`config/cors.php`, iz `.env`).
- **Apsolutni URL-ovi** za slike (koristi `APP_URL`) — frontend je na drugom domenu.

## Auth model

- **Read (GET)** — javno, bez tokena (keširano na ivici).
- **Contact (POST)** — bez tokena, ali honeypot + rate limit (`throttle:5,1`).
- **Write/uređivanje** — isključivo kroz Filament (session auth), **ne** kroz API.
- **Preview draftova** (opciono) — Sanctum token u `X-Preview-Token` header-u.

Tako frontend nikad ne drži kredencijale koji nešto menjaju.

## Dodavanje novog tipa sadržaja (recept)

1. `php artisan make:model Service -m` → migracija (SQLite).
2. `php artisan make:filament-resource Service` → admin CRUD.
3. Dodaj `GET /api/v1/services` + API Resource (oblik odgovora).
4. Invalidiraj keš na `saved`/`deleted`. Frontend ga pokupi kad zatreba.

Core ostaje isti; ovo su **opcioni moduli** koje uključuješ po potrebi klijenta.

## Media
- `php artisan storage:link`; slike u `storage/app/public`.
- Generiši `thumb` i `hero` konverzije pri uploadu (spatie/medialibrary ili ručno).
- API vraća apsolutne URL-ove; postavi tačan `APP_URL` po klijentu.

## Onboarding komanda (cilj: `php artisan site:new`)
Interaktivno: naziv biznisa, email, primarna boja, Calendly URL, admin slug →
upiše u `settings` + generiše `ADMIN_PATH` + kreira admin nalog. Zameni ručni seed.

## Deploy & backup
- Backend: VPS + Nginx + PHP-FPM (ili Laravel Forge). Jedan po klijentu ili shared s
  odvojenim `.env`.
- **Backup = kopiraj `database/database.sqlite`** (cron `cp`, ili `litestream` za
  kontinuirani backup na S3). Media backup = `storage/app/public`.
- Produkcija checklist: nasumičan `ADMIN_PATH`, pravi `MAIL_*`, tačan `APP_URL`,
  `APP_DEBUG=false`.
