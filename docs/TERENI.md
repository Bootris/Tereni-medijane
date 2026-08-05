# Tereni Medijana — module

Public map + QR reporting for municipal sports fields, built on the site-core
(Laravel 12 · Filament v4 · SQLite · Blade). A citizen scans the QR plaque on a
field's fence (or clicks the field on the map), files a report with a photo, and
watches its status move through a **publicly timestamped** ladder. That public
timeline is the product: it's the pressure that makes the municipality act.

> Strategy: ship as a **separate municipal product** that shares this site-core
> base and design system (repo `Bootris/Tereni-medijane`). This module is the
> reference implementation living inside the site-core — enable it per client
> with `SITE_FEATURE_TERENI=true`; it stays completely dormant otherwise.

## Enable

```dotenv
SITE_FEATURE_TERENI=true
# Map default centre (Medijana, Niš) + zoom
TERENI_MAP_LAT=43.3192
TERENI_MAP_LNG=21.9161
TERENI_MAP_ZOOM=14
# SMS to stewards — off by default (logs instead; see SmsSender)
TERENI_SMS_ENABLED=false
```

With the flag off, the public routes, the API routes, and the admin navigation
are all hidden.

## Data model (`app/Models/`)

| Model               | Table                    | Notes |
|---------------------|--------------------------|-------|
| `Facility`          | `facilities`             | Objekat — škola / otvoreni teren; ownership, address, coords. |
| `Court`             | `courts`                 | Teren — type, surface, dimensions, lighting, access, gallery, coords. **`slug` is the QR target.** |
| `Steward`           | `stewards`               | Zaduženo lice — notified (email + SMS) on new reports. |
| `Report`            | `reports`                | Prijava — category, photo (**required at submission**; moderation may remove it), status, moderation flags. |
| `ReportStatusChange`| `report_status_changes`  | One row per transition → the public timeline. |

Enums in `app/Enums/`: `CourtType`, `CourtAccess`, `ReportCategory`,
`ReportStatus`.

### Status ladder (`ReportStatus`)

```
Prijavljeno → Potvrđeno → U planu radova → Rešeno
                                        ↘ Odbijeno  (uz obrazloženje)
```

Transitions go through `Report::changeStatus()` so every step is recorded with a
timestamp and an optional public note. Never set `status` directly.

## Anti-spam (no registration, by design)

No registration means ~10× more reports — kept clean by four layers:

1. **Per-IP rate limit** — `throttle:5,1` on the submit routes.
2. **Mandatory photo** — proof of a real issue.
3. **Honeypot** — hidden `website` field.
4. **Moderation before public** — reports land `is_public = false`; an editor
   publishes them from the admin. Published reports also carry a citizen
   **"flag"** button (`is_flagged`) for after-the-fact abuse.

## Public routes (Blade, unprefixed like `/blog`)

| Route | Name | Purpose |
|-------|------|---------|
| `GET /mapa` | `tereni.map` | Leaflet + OSM map (no Google cost) + field list. |
| `GET /teren/{court}` | `tereni.court` | Field page: details, gallery, report form, public timeline. |
| `GET /teren/{court}/qr.svg` | `tereni.court.qr` | QR (SVG) encoding the field URL — for the printed plaque. |
| `POST /teren/{court}/prijava` | `tereni.report.store` | Submit a report (throttled). |
| `POST /prijava/{report}/flag` | `tereni.report.flag` | Flag a public report. |

## JSON API (read-only contract for a separate frontend — Astro/Next)

Under `/api/v1` (see [BACKEND.md](BACKEND.md)):

| Route | Purpose |
|-------|---------|
| `GET /api/v1/tereni` | All active, locatable fields (map feed). |
| `GET /api/v1/tereni/{court}` | One field + its public report timeline. |
| `POST /api/v1/tereni/{court}/prijave` | Submit a report (throttled, multipart). |

Reporter contact details are never exposed by the API.

## Admin (Filament, navigation group "Tereni Medijana")

- **Objekti** (`FacilityResource`) — facilities, with **Tereni** and
  **Zadužena lica** relation managers.
- **Tereni** (`CourtResource`) — fields, gallery, QR link, "public page" link.
- **Prijave** (`ReportResource`) — moderation queue. Navigation badge counts
  reports awaiting moderation. Row actions: **Objavi** (publish), **Promeni
  status** (records the timeline step + notifies the reporter). Editors can
  moderate; no "create" (reports come only from citizens).

## Notifications

`App\Support\Tereni\ReportNotifier` fans events out best-effort (a delivery
failure never breaks the request — the report is already saved):

- **New report** → each opted-in steward of the facility (email `ReportSubmitted`
  + SMS).
- **Status change** → the reporter, if they left an email/phone
  (`ReportStatusChanged` / SMS).

SMS goes through `App\Support\Sms\SmsSender`, a thin stub that **logs** by
default. Bind a real gateway (phase 2) without touching callers.

## Seed a pilot

```bash
php artisan db:seed --class=Database\\Seeders\\TereniDemoSeeder
```

Creates one school with two fields and a steward around Medijana so the map and
admin aren't empty. Idempotent.

## Phases

1. **Terenski popis** — field survey of every school in Medijana (GPS + photo +
   condition). ~15–20 facilities. The biggest, unskippable job.
2. **MVP** — map + reports + Filament, one pilot facility. *(this module)*
3. **QR plaques** — print & mount, only once the system works.

## Tests

`tests/Feature/TereniTest.php` — public pages, submission + moderation, status
history, notifications, flag, QR, the JSON API, Filament admin rendering, and
image-file cleanup (replacing/removing photos and gallery images, deleting
reports/courts/facilities — guarded so only this module's own files ever go).
Run with the site-core suite: `php artisan test`.
