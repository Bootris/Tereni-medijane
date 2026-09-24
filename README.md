# Tereni Medijana

Javna mapa sportskih terena opštine Medijana (Niš) + prijava problema **QR kodom,
bez registracije**. Građanin skenira QR tablu na ogradi terena (ili klikne teren
na mapi), pošalje prijavu sa fotografijom, i javno prati kako se status menja:

```
Prijavljeno → Potvrđeno → U planu radova → Rešeno
                                        ↘ Odbijeno (uz obrazloženje)
```

**Javno vidljivo vreme u svakom statusu je ceo proizvod** - to je pritisak koji
tera sistem da radi. Bez toga je ovo samo formular.

Izgrađeno na site-core osnovi: Laravel 12 · Filament v4 (admin) · SQLite ·
Blade + Leaflet/OpenStreetMap (bez Google troškova). Detalji modula:
[docs/TERENI.md](docs/TERENI.md).

---

## Brzi start

```bash
./start.sh              # instalira sve što fali i pokreće sajt na :8000
PORT=8080 ./start.sh    # na drugom portu
```

Skripta sama odradi `composer install`, `npm run build`, migracije i
`storage:link`. Na kraju ispiše prave adrese (mapa + admin).

Provera zdravlja:

```bash
./check-backend.sh          # PHP ekstenzije, baza, sve HTTP rute
./check-backend.sh --full   # + kompletan test suite
```

## Admin panel

Admin ruta je **tajna i po klijentu** - dolazi iz `ADMIN_PATH` u `.env`
(npr. `admin-x7k2p9`), nikad `/admin`. `./start.sh` je ispiše pri pokretanju.

Početni nalog se seed-uje iz `.env` (`SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD`).
**Odmah promeni lozinku** posle prve prijave (avatar gore desno → Profile).

**Uloge:** `admin` (sve) · `editor` (sadržaj i moderacija - ne vidi Users ni
Settings). Prijave niko ne kreira ručno - stižu samo od građana.

Navigacija **Tereni Medijana**: **Objekti** (škole/tereni na otvorenom, sa
terenima i zaduženim licima) · **Tereni** (pojedinačna igrališta, QR link) ·
**Prijave** (red za moderaciju - badge broji neobrađene).

---

## Pravila unosa

### Objekti i tereni

1. Prvo unesi **Objekat** (školu) - naziv, vlasništvo, adresu, koordinate.
2. Unutar objekta dodaj **Terene** (relacija na formi objekta) - tip, podlogu,
   dimenzije, osvetljenje, dostupnost.
3. **Zadužena lica** (domar, nastavnik fizičkog, JKP) vezuju se za objekat;
   mejl/SMS o novim prijavama dobijaju samo lica sa uključenim prekidačem
   „Prima obaveštenja o novim prijavama".
4. Teren je na mapi samo ako je **„Aktivan"** i ima koordinate (svoje ili
   nasleđene od objekta).
5. **QR slug terena ne menjaj** posle štampe tabli - to je adresa iza QR koda
   (`/teren/{slug}`). Promena sluga = stare table vode na 404.

### Koordinate (pravi položaj na mapi)

1. Otvori [Google Maps](https://maps.google.com), nađi teren (satelitski prikaz).
2. **Desni klik tačno na teren** → klik na koordinate u meniju (kopiraju se),
   npr. `43.31923, 21.91612`.
3. Nalepi u admin: prvi broj u **lat** (geo širina), drugi u **lng** (geo dužina).

Pravila koja štede posao:

- Dovoljno je uneti koordinate **na objektu** - teren bez svojih koordinata
  nasleđuje lokaciju objekta. Za manje dvorište to je sasvim dovoljno.
- Koordinate na terenu unosi samo kad škola ima više razmaknutih terena, pa
  hoćeš poseban marker za svaki.
- Mapa se sama zumira da obuhvati sve markere; `TERENI_MAP_*` u `.env` je samo
  fallback centar dok nema nijednog terena.

### Slike

**Galerija terena** (admin → Tereni → izmeni → „Lokacija i galerija"):

- Do **12 slika**, najviše **8 MB** po slici; samo slike (jpg/png/webp…).
- **Dodavanje:** prevuci fajlove ili klikni u polje.
- **Redosled:** prevuci sličice - tako se ređaju na javnoj strani terena.
- **Izbacivanje:** klik na **X** na sličici, pa Save - fajl se **briše i sa
  diska**, ne ostaje smeće.
- Slike se čuvaju u `storage/app/public/tereni/courts/`.

**Fotografija prijave:**

- Za građane je **obavezna** pri prijavi (dokaz = anti-spam) - najviše 8 MB.
- U adminu (Prijave → izmeni) fotografija može da se **ukloni** (neprikladna)
  ili **zameni/doda** - stari fajl se automatski briše sa diska pri čuvanju.
  Prijava bez fotografije ostaje validna na javnoj strani.
- Brisanje prijave, terena ili celog objekta briše i **sve** pripadajuće
  fajlove sa diska (galerije + fotografije prijava).

### Moderacija prijava

1. Nova prijava je **sakrivena** (`Javno vidljivo` isključeno) dok je editor ne
   pregleda - badge na „Prijave" broji čekanje.
2. **Objavi** (akcija u tabeli) = prijava ide na javnu stranu terena sa
   početnim korakom „Prijavljeno".
3. Status menjaj **samo** kroz akciju **„Promeni status"** (ili polje Status na
   formi) - svaki korak se beleži u javnu istoriju sa vremenom, i prijavilac
   dobija obaveštenje ako je ostavio kontakt. Nikad ne prepravljaj istoriju.
4. „Komentar uz ovaj korak" je javan - piši ono što građanin sme da vidi
   (npr. „Izašli smo na teren", „Uvršteno u plan za mart").
5. Građani mogu da označe objavljenu prijavu kao neprikladnu (**flag**) - takve
   prijave imaju ikonicu u tabeli; ukloni fotografiju/sakrij prijavu po proceni.

Anti-spam slojevi (već ugrađeni - ne isključuj): rate limit po IP (5/min),
obavezna fotografija, honeypot polje, moderacija pre objave.

### QR table

- `/teren/{slug}/qr.svg` - vektorski QR spreman za štampu (link i u tabeli
  Tereni). Štampaj table tek kad je sistem u produkciji i slug konačan.

---

## Konfiguracija (`.env`)

```dotenv
SITE_FEATURE_TERENI=true   # ceo modul; false = advokatski site-core sajt
TERENI_MAP_LAT=43.3192     # fallback centar mape (Medijana)
TERENI_MAP_LNG=21.9161
TERENI_MAP_ZOOM=14
TERENI_SMS_ENABLED=false   # SMS zaduženima - stub, loguje umesto da šalje
ADMIN_PATH=admin-x7k2p9    # tajna admin ruta - nasumična po deployu
```

Kad je modul upaljen, `/` i `/sr|/en` vode na `/mapa`.

## JSON API (za odvojeni frontend)

Read-only ugovor pod `/api/v1` - kontakti prijavilaca se **nikad** ne izlažu:

| Ruta | Svrha |
|---|---|
| `GET /api/v1/tereni` | svi aktivni tereni sa koordinatama (feed za mapu) |
| `GET /api/v1/tereni/{slug}` | jedan teren + javna istorija prijava |
| `POST /api/v1/tereni/{slug}/prijave` | prijava (multipart, throttle) |

## Ručno pokretanje (bez skripte)

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed                                      # admin + podešavanja
php artisan db:seed --class='Database\Seeders\TereniDemoSeeder' # pilot tereni + slike (opciono)
php artisan storage:link
npm install && npm run build
php artisan serve
```

## Testovi

```bash
php artisan test
```

Pokrivaju javne stranice, prijave (anti-spam, moderacija, istorija statusa,
notifikacije), čišćenje fajlova sa diska, QR, JSON API i admin panel. Testovi
idu na SQLite u memoriji - ne diraju pravu bazu.

## Produkcija

- Nasumičan `ADMIN_PATH`, pravi `MAIL_*` SMTP podaci, tačan `APP_URL`
  (bez njega URL-ovi slika ne rade), `APP_DEBUG=false`.
- **Backup = kopiraj dva puta:** bazu `database/database.sqlite` i slike
  `storage/app/public/` (cron `cp`/`rsync`, ili `litestream` za bazu).
- SMS gateway je faza 2 - `App\Support\Sms\SmsSender` je stub koji loguje;
  pravi provajder se vezuje bez izmene pozivalaca.

## Faze projekta

1. **Terenski popis** - obilazak svih škola u Medijani (GPS + foto + stanje),
   ~15–20 objekata. Najveći posao, ne preskače se.
2. **MVP** - mapa + prijave + admin, jedan pilot objekat. *(ovo je spremno)*
3. **QR table** - štampa i montaža, tek kad sistem radi.
