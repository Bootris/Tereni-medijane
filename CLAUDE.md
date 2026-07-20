# CLAUDE.md

Guidance for Claude Code working in this repo.

## What this is

A **reusable "site-core"** — a Laravel backend + admin panel you clone once per
client to build small business sites (local media, law office, artist, shop…).
Backend stays the same; only branding, texts, and the frontend change per client.

> **Platform plan** (one backend, many frontends): [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
> · backend/API contract [docs/BACKEND.md](docs/BACKEND.md) · frontend templates
> [docs/FRONTEND.md](docs/FRONTEND.md) · design system [docs/DESIGN.md](docs/DESIGN.md).
> **This repo is reference Example #1 (Blade monolith) — do not refactor it.**

**Stack:** Laravel 12 · Filament v4 (admin) · **SQLite** (single-file DB) · Blade + Tailwind (frontend) · PHP 8.2.

## Core decisions (don't relitigate)

- **SQLite is the database, on purpose.** One file `database/database.sqlite`, zero
  server config, backup = copy the file. Enough for small-business traffic. Real
  Eloquent/migrations/relations — no MySQL needed. (README still mentions MySQL from
  the first draft; SQLite is the current default via `DB_CONNECTION=sqlite`.)
- **Filament for the admin.** CRUD, image uploads, rich text, roles — out of the box.
  Never hand-roll admin UI.
- **Admin path is secret & per-client.** Route comes from `config('site.admin_path')`
  (set `ADMIN_PATH=admin-x7k2p9` in `.env`). Never hardcode `/admin`.
- **Two roles:** `admin` (everything) and `editor` (content only — no Users/Settings).
- **Integrations stay thin.** Mail via Laravel mailer; meetings via a **Calendly embed
  URL** stored in Settings. Google Calendar OAuth is an optional phase-2 module — skip
  unless asked.

## Data model (`app/Models/`)

`Post` (blog/stories: rich text, image, YouTube/Vimeo embed, category, draft +
`published_at` scheduling, SEO) · `Category` · `TeamMember` (photo, bio, contacts,
drag-order) · `ContactMessage` (contact-form inbox) · `Setting` (key/value site
config, exposed to all views as `$site`) · `User` (accounts + role).

Each model has a matching Filament resource in `app/Filament/Resources/`.
Site settings live in `app/Filament/Pages/ManageSettings.php`.

## Commands

```bash
./start.sh                 # install + build + migrate + serve → :8000
php artisan migrate --seed # admin user, categories, default settings
php artisan site:import-yaml   # (re)import posts/team from storage/content/posts/
php artisan test           # feature tests (uses separate site_test DB)
./check-backend.sh         # smoke-test every route, exit 0 if healthy
```

**Admin:** `/{ADMIN_PATH}` · seeded login `borisboncic95@gmail.com` / `password`
(`SEED_ADMIN_PASSWORD`) — change immediately.

## Frontend

- **This repo** ships a Blade + Tailwind frontend (`resources/views/`, langs in
  `resources/lang/{sr,en}/`). Good default for one-repo sites.
- **Multi-client plan:** backend exposes a small read-only JSON API; each client site
  is a **separate thin frontend repo** (recommend **Astro** for static content sites;
  Next.js if you prefer one ecosystem — same API contract). Rebuild on publish via webhook.

## Rebranding for a new client

1. Colors/fonts: `@theme` block in `resources/css/app.css`.
2. Texts: `resources/lang/{sr,en}/lawyer.php`.
3. Site name/contacts/socials/Maps/Calendly: **Admin → Site settings** (no code).
4. `.env`: fresh `APP_KEY`, random `ADMIN_PATH`, real `MAIL_*`, correct `APP_URL`.

## Conventions

- Blog URLs stay unprefixed (`/blog/{slug}`) for stable SEO; other pages are locale-
  prefixed (`/{sr|en}`).
- Match existing code style; keep new admin work inside Filament resources.
- Tests must pass (`php artisan test`) before considering a change done.
