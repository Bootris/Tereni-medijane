# Dizajn sistem — sajtovi za male biznise

Cilj: sajtovi koji izgledaju **skupo i namenski**, a prave se za sat. Tajna je jedan
izvor dizajn tokena + fiksni set komponenti koje samo brendiraš po klijentu.

## Dizajn tokeni = jedini izvor istine

| Token | Gde živi | Ko ga menja |
|---|---|---|
| Boje brenda (`primary`, `accent`, neutralne) | Settings (admin) → API `theme` | klijent, u adminu |
| Font (heading/body) | Settings → API | klijent |
| Radius, spacing skala, senke | frontend token fajl (`@theme` / CSS vars) | ti, retko |

Podela: **brend** (boja, logo, font) dolazi iz backenda po klijentu; **struktura**
(spacing, radius, tipografska skala) je u frontend template-u i ostaje ista. Tako
promena boje ne traži deploy koda — vidi obrazac u ovom repo-u: `@theme` blok u
`resources/css/app.css`.

## Runtime primena (bilo koji frontend)
```css
:root{
  --brand: var(--from-settings, #1a3d5c);
  --accent: var(--from-settings, #c9a24a);
  --font-head: 'PlayfairDisplay', serif;
  --radius: 14px; --space: 8px;   /* struktura — fiksna */
}
```
`getSettings()` popuni `--brand/--accent/--font`; ostalo je konstanta template-a.

## Inventar komponenti (svaki mali sajt ih ima)

Hero (naslov + tagline + CTA) · O vlasniku/autoru (foto + bio) · Usluge/oblasti rada ·
Blog/priče (lista kartica) · Pojedinačan post (tekst + slika + YouTube/video) ·
Tim (opciono) · Kontakt (forma + mapa) · Zakazivanje (Calendly embed) · Footer
(kontakt, društvene mreže). Sve se pune iz API-ja — nema hardkodovanog teksta.

## Preseti sekcija po tipu biznisa
| Tip | Naglasak |
|---|---|
| Lokalni medij / autor | velika naslovna priča, "O autoru", feed priča, video embed |
| Advokat / konsultant | oblasti prakse, tim, poverenje/reference, zakazivanje |
| Umetnik / zanatlija | galerija/rad, biografija, kontakt |
| Mali shop / usluga | usluge + cene, radno vreme, mapa, poziv na akciju |

Isti kod, drugačiji redosled i tekst sekcija — biraš preset pri onboardingu.

## Rebrand checklist (novi klijent)
1. **Naziv biznisa + tagline** → Settings.
2. **Foto vlasnika/autora** → upload u adminu; **skini stock pozadine**, stavi pravu sliku.
3. **Boje** (`primary`, `accent`) + **font** → Settings.
4. **Logo** → upload; favicon iz logotipa.
5. **Tekstovi** sekcija (hero, o nama, usluge) → admin / lang fajlovi.
6. **Kontakt + Calendly + mape + društvene** → Settings.
7. Prvi 2-3 bloga/priče da sajt ne bude prazan.

## UX pravila (ne pregovarati)
- **Performanse:** cilj Lighthouse 95+; statika + optimizovane slike (WebP, lazy).
- **Responsive:** mobile-first; hero i tipografija čitljivi na telefonu.
- **Pristupačnost:** kontrast AA, alt tekst na slikama, semantički HTML, fokus stanja.
- **Suzdržan motion:** suptilne tranzicije; poštuj `prefers-reduced-motion`.
- **Tipografija nosi dizajn:** jedan izražajan heading font + čitljiv body; dosledna skala.
- **Bez "template" mirisa:** pravi foto vlasnika, pravi tekstovi, brend boja — ne default plava.

## Veza s ostatkom
Tokeni koje ovde definišeš servira backend ([BACKEND.md](BACKEND.md) `/settings`) i
primenjuje frontend ([FRONTEND.md](FRONTEND.md) brend tokeni). Celina: [ARCHITECTURE.md](ARCHITECTURE.md).
