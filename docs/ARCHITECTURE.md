# Platforma — arhitektura (WordPress-builder za sopstvene klijente)

Cilj: **jedan backend, mnogo frontend-a.** Backend se ne menja između klijenata;
svaki novi sajt je novi (tanak) frontend + par podešavanja. Kao WordPress builder,
ali ti vlasnik celog stacka: Laravel na backendu, bilo koji frontend (Blade / Next.js /
Vue) na vrhu, spojeni preko **stabilnog JSON API-ja**.

## Tri stuba

| Stub | Repo | Menja se po klijentu? |
|---|---|---|
| **site-core** (backend + admin) | jedan, deljen | ne (samo `.env` + sadržaj u adminu) |
| **frontend template** | jedan po framework-u (Astro/Next/Vue) | kloniraš i brendiraš |
| **klijent-sajt** | jedan po klijentu | da — to je proizvod |

**Ovaj repo (Law-Office) = referentni Primer #1** — monolit (Blade ugrađen). Ne dira se;
služi kao dokaz da model radi i kao izvor komponenti za kopiranje.

## Ključne odluke (ne preispitivati bez razloga)

1. **Backend postaje headless.** site-core izlaže `GET /api/v1/...` (sadržaj) i
   `POST /api/v1/contact`. Oblik odgovora je **ugovor** — frontend-i se oslanjaju na njega.
   → detalji u [BACKEND.md](BACKEND.md).
2. **Frontend je potrošan, backend je stabilan.** Blade/Next/Vue su zamenljivi; svi
   troše isti API. → [FRONTEND.md](FRONTEND.md).
3. **Admin ostaje u backend repo-u (Filament).** Ne cepati admin u zaseban repo —
   server-rendered je i vezan za Laravel; cepanje = mnogo bola bez koristi.
4. **SQLite je baza.** Jedan fajl, bez servera, backup = kopiranje fajla. Dovoljno za
   male biznise. MySQL samo ako klijent stvarno preraste.
5. **Brend kroz podešavanja, ne kroz kod.** Naziv biznisa, boje, logo, foto vlasnika,
   Calendly link — sve u adminu (Settings) → API ih servira → frontend ih primeni.
   Tako "upišeš biznis, ubaciš sliku, obrišeš stock pozadinu → sajt gotov". → [DESIGN.md](DESIGN.md).

## Topologija repo-a

```
site-platform/                      (workspace / meta — opciono git submodule-i)
├── site-core/                      # BACKEND: Laravel 12 + Filament v4 + SQLite + /api/v1
├── frontends/
│   ├── front-astro/                # default za content sajtove (SEO, statika)
│   ├── front-next/                 # kad treba app-interaktivnost
│   └── front-vue/                  # alternativa (Nuxt)
└── clients/
    ├── law-office/                 # PRIMER #1 — ovaj repo (monolit, ostaje kakav je)
    ├── local-media/                # site-core klon + front-next klon
    └── ...
```

Po klijentu: kloniraš `site-core` (backend) + jedan frontend template → povežeš ih
preko `API_BASE_URL` → brendiraš u adminu → deploy.

## "WordPress-builder" tok za novi sajt (cilj: ispod 1h do produkcije)

1. `git clone site-core client-x/backend` → `php artisan site:new` (wizard: naziv,
   email, boje, Calendly) → seed.
2. Admin: dodaj foto vlasnika, tekst "O nama", oblasti/usluge, prve blogove.
3. `git clone front-astro client-x/frontend` → `.env`: `API_BASE_URL` na backend.
4. Deploy backend (VPS/Forge) + frontend (Netlify/Vercel/statik). Webhook: publish → rebuild.

## Šta koji sajt uvek dobija (reusable core)

Front page (hero + tekst) · O vlasniku/autoru (foto, bio) · Usluge/rad · Blog/priče
(tekst, slika, YouTube/video) · Kontakt forma (mail) · Zakazivanje (Calendly embed) ·
Admin na tajnoj ruti (`/admin-x7k2p9`) sa `admin`/`editor` ulogama.

## Build faze

| Faza | Rezultat |
|---|---|
| 1 | Iz site-core izvuci **API sloj** (`/api/v1`) — bez diranja postojećeg Blade fronta |
| 2 | **front-astro** template koji troši API (blog, o nama, kontakt) |
| 3 | **Brend tokeni** kroz Settings → API → CSS varijable na frontu |
| 4 | `php artisan site:new` onboarding komanda + deploy recept |
| 5 | **front-next** template (isti ugovor) |
| 6 | Opcioni moduli: Google Calendar OAuth, newsletter, galerija |

## Naredni koraci
- Pročitaj [BACKEND.md](BACKEND.md) za API ugovor, [FRONTEND.md](FRONTEND.md) za
  template strategiju, [DESIGN.md](DESIGN.md) za dizajn sistem.
- Ovaj repo se ne refaktoriše — nove stvari idu u `site-core` (novi repo).
