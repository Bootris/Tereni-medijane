# Frontend — template strategija

Načelo: **frontend je potrošan, backend je stabilan.** Svaki frontend je zaseban repo
koji troši isti [API ugovor](BACKEND.md#api-ugovor-apiv1). Menjaš izgled i framework
koliko hoćeš — backend ne dodiruješ.

## Jedan template po framework-u

| Template | Kada | Zašto |
|---|---|---|
| **front-astro** (default) | content sajtovi: media, advokat, umetnik, lokalni biznis | statika, savršen SEO/Lighthouse, jeftin hosting, rebuild na publish |
| **front-next** | treba app-interaktivnost (nalozi, dashboards, live) | React ekosistem, SSR/ISR |
| **front-vue** (Nuxt) | tim voli Vue | isti ugovor, drugi ekosistem |
| **Blade (in-repo)** | brz monolit, jedan repo, bez odvojenog fronta | Primer #1 (ovaj repo) |

Svi troše **isti** `/api/v1`. Izbor framework-a ne menja backend ni oblik podataka.

## Kako se frontend povezuje

`.env` frontend template-a:
```
API_BASE_URL=https://backend.klijent.rs      # site-core deploy
SITE_LOCALE=sr                                # default jezik
PREVIEW_TOKEN=                                # opciono, za draft preview
REBUILD_WEBHOOK=                              # backend zove na publish
```

Svaki template nosi tanak **API klijent** (`lib/api.*`) sa funkcijama:
`getSettings()`, `getPosts({page,category})`, `getPost(slug)`, `getTeam()`,
`getCategories()`, `submitContact(payload)` — mapirane 1:1 na API rute.

## Rendering strategija (content sajtovi)
- **Static generate** stranice iz API-ja (Astro `getStaticPaths` / Next `generateStaticParams`).
- **Rebuild na publish:** admin sačuva post → backend pozove `REBUILD_WEBHOOK` →
  frontend se ponovo build-uje (Netlify/Vercel deploy hook). Nema baze na frontu.
- Kontakt forma → `POST /api/v1/contact` (honeypot polje mora ostati, prazno).

## Brend tokeni na frontu
`getSettings()` vraća `theme:{primary,accent,font}`. Frontend ih na runtime ubaci kao
CSS varijable na `:root`:
```html
<style>:root{ --brand:{{primary}}; --accent:{{accent}}; --font:{{font}} }</style>
```
Tako promena boje u adminu → nova boja na sajtu bez izmene koda. → [DESIGN.md](DESIGN.md).

## Struktura frontend template-a (Astro primer)
```
front-astro/
├── src/
│   ├── lib/api.ts            # API klijent (jedini dodir s backendom)
│   ├── layouts/Base.astro    # <head>, brend tokeni, header/footer
│   ├── components/           # Hero, Owner, Services, PostCard, ContactForm...
│   └── pages/
│       ├── index.astro       # front page (settings + featured)
│       ├── blog/index.astro  # lista (getPosts)
│       ├── blog/[slug].astro # post (getPost)
│       └── kontakt.astro
├── public/
└── .env.example              # API_BASE_URL, SITE_LOCALE...
```
Next/Vue template: ista `lib/api`, iste komponente po nameni, drugi framework sintaksa.

## Zlatna pravila
- Frontend **nikad** ne piše u bazu direktno — samo API.
- Frontend **ne drži** admin kredencijale; samo (opciono) preview token.
- Nova komponenta koja treba nov podatak → prvo dodaj polje u API ([BACKEND.md](BACKEND.md)),
  pa je koristi. Ne zaobilazi ugovor.
- Isti oblik podataka na svim frontend-ima → komponente lako prenosiš između template-a.
