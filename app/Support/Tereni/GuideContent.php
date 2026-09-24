<?php

namespace App\Support\Tereni;

use App\Enums\ReportCategory;
use App\Enums\ReportStatus;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Text of the public "Uputstva" page. Editors change it in the admin
 * (stored as JSON under the `tereni_guide` setting); until then the
 * defaults below are shown.
 *
 * Two placeholders inside a section body render live lists from the enums,
 * so they never drift from the report form: [kategorije] and [statusi].
 */
class GuideContent
{
    public const SETTING_KEY = 'tereni_guide';

    public const PLACEHOLDERS = ['[kategorije]', '[statusi]'];

    /** @return array{lead: string, sections: list<array{title: string, body: string}>} */
    public static function get(): array
    {
        $stored = Setting::get(self::SETTING_KEY);
        $data = is_string($stored) ? json_decode($stored, true) : null;

        if (! is_array($data) || empty($data['sections'])) {
            return self::defaults();
        }

        return [
            'lead' => (string) ($data['lead'] ?? ''),
            'sections' => array_values(array_filter(
                $data['sections'],
                fn ($s) => is_array($s) && filled($s['title'] ?? null),
            )),
        ];
    }

    /** @param array{lead?: string|null, sections?: array<mixed>} $data */
    public static function save(array $data): void
    {
        Setting::set(self::SETTING_KEY, json_encode([
            'lead' => $data['lead'] ?? '',
            'sections' => array_values($data['sections'] ?? []),
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Sections ready for the view: an anchor id, and the body with the
     * placeholders swapped for their live HTML.
     *
     * @return list<array{id: string, title: string, html: string}>
     */
    public static function renderedSections(): array
    {
        $used = [];

        return array_map(function (array $section) use (&$used) {
            $id = Str::slug($section['title']) ?: 'sekcija';
            $id = isset($used[$id]) ? $id.'-'.(++$used[$id]) : $id;
            $used[$id] ??= 1;

            return [
                'id' => $id,
                'title' => $section['title'],
                'html' => self::expandPlaceholders((string) ($section['body'] ?? '')),
            ];
        }, self::get()['sections']);
    }

    private static function expandPlaceholders(string $html): string
    {
        $blocks = [
            '[kategorije]' => self::categoriesHtml(),
            '[statusi]' => self::statusesHtml(),
        ];

        foreach ($blocks as $token => $block) {
            // The rich editor wraps a lone token in its own paragraph.
            $html = preg_replace('#<p>\s*'.preg_quote($token, '#').'\s*</p>#u', $block, $html);
            $html = str_replace($token, $block, $html);
        }

        return $html;
    }

    private static function categoriesHtml(): string
    {
        $items = array_map(
            fn (ReportCategory $c) => '<li>'.e($c->icon().' '.$c->label()).'</li>',
            ReportCategory::cases(),
        );

        return '<ul class="cats">'.implode('', $items).'</ul>';
    }

    private static function statusesHtml(): string
    {
        $help = [
            'prijavljeno' => 'Prijava je primljena i čeka proveru moderatora. Još nije javno vidljiva.',
            'potvrdjeno' => 'Opština je proverila prijavu i potvrdila da problem postoji.',
            'u_planu' => 'Popravka je uvrštena u plan radova.',
            'reseno' => 'Problem je otklonjen. Teren je ponovo u redu.',
            'odbijeno' => 'Prijava nije prihvaćena, uz javno obrazloženje zašto (npr. duplikat ili nije u nadležnosti opštine).',
        ];

        $items = array_map(fn (ReportStatus $s) => sprintf(
            '<li><span class="badge badge-%s">%s</span><span>%s</span></li>',
            $s->color(), e($s->label()), e($help[$s->value] ?? ''),
        ), ReportStatus::cases());

        return '<ul class="ladder">'.implode('', $items).'</ul>';
    }

    /** @return array{lead: string, sections: list<array{title: string, body: string}>} */
    public static function defaults(): array
    {
        return [
            'lead' => 'Tereni Medijane je javna mapa sportskih terena u opštini Medijana. Svako može da prijavi problem na terenu, bez registracije, i da javno prati šta se sa prijavom dešava.',
            'sections' => [
                [
                    'title' => 'Čemu služi',
                    'body' => '<p>Na školskim dvorištima, između zgrada i u parkovima Medijane ima mnogo terena: za košarku, mali fudbal, odbojku, tenis i atletiku. Kad se polomi koš, pocepa mreža ili pregori reflektor, do sada nije bilo jasno kome to javiti niti da li je iko to video.</p>'
                        .'<p>Tereni Medijane to rešava na tri načina:</p>'
                        .'<ul>'
                        .'<li><p><strong>Jedna mapa svih terena:</strong> gde su, kakva im je podloga, da li imaju rasvetu i da li su javno dostupni.</p></li>'
                        .'<li><p><strong>Brza prijava problema:</strong> sa fotografijom, za manje od minuta, direktno sa terena.</p></li>'
                        .'<li><p><strong>Javna vremenska linija:</strong> svaka objavljena prijava pokazuje kad je stigla i kad je prešla u sledeći status. Svi vide koliko rešavanje traje.</p></li>'
                        .'</ul>',
                ],
                [
                    'title' => 'Pronađi teren',
                    'body' => '<ul>'
                        .'<li><p><strong>Mapa:</strong> svaki teren je oznaka sa ikonicom sporta. Crveni znak <strong>!</strong> znači da teren ima otvorenu prijavu. Dugme za lokaciju pokazuje terene oko tebe.</p></li>'
                        .'<li><p><strong>Svi tereni:</strong> spisak sa pretragom (naziv, škola, naselje) i filterima po sportu, „bez prijava”, „sa problemom” i „samo javno dostupni”.</p></li>'
                        .'<li><p><strong>QR tabla na terenu:</strong> skeniraj kod kamerom telefona i odmah si na stranici tog terena, spreman za prijavu.</p></li>'
                        .'</ul>'
                        .'<p>Na stranici terena su fotografije, podaci o terenu (podloga, dimenzije, rasveta, pristup) i sve javne prijave sa njihovom istorijom.</p>',
                ],
                [
                    'title' => 'Kako prijaviti problem',
                    'body' => '<ol>'
                        .'<li><p><strong>Otvori stranicu terena</strong> preko mape, spiska ili QR table.</p></li>'
                        .'<li><p><strong>Izaberi šta nije u redu</strong>, jednu od kategorija ispod.</p></li>'
                        .'<li><p><strong>Dodaj fotografiju (obavezno).</strong> Slikaj problem tako da se jasno vidi. Bez fotografije prijava ne može da se pošalje.</p></li>'
                        .'<li><p><strong>Opiši ukratko</strong> (nije obavezno), npr. „obruč na levom košu je savijen, visi”.</p></li>'
                        .'<li><p><strong>Ostavi ime i kontakt</strong> (nije obavezno): e-mail ili telefon, ako želiš obaveštenje kad se status promeni.</p></li>'
                        .'<li><p><strong>Pošalji.</strong> Prijava odlazi na proveru, a zaduženo lice za taj objekat dobija obaveštenje.</p></li>'
                        .'</ol>'
                        .'<p><strong>Kategorije prijave</strong></p>'
                        .'<p>[kategorije]</p>'
                        .'<blockquote><p>Jedna prijava = jedan problem. Ako je na terenu više kvarova (npr. koš i rasveta), pošalji ih kao odvojene prijave. Tako se svaki prati i rešava posebno.</p></blockquote>',
                ],
                [
                    'title' => 'Šta se dešava posle prijave',
                    'body' => '<p>Svaka prijava prvo ide na <strong>moderaciju</strong>: urednik opštine proveri fotografiju i opis i tek onda je objavi na stranici terena. Tako na sajtu nema lažnih ni uvredljivih prijava.</p>'
                        .'<p>Zatim prijava prolazi kroz statuse. Svaki korak se beleži sa datumom i, gde treba, javnom napomenom:</p>'
                        .'<p>[statusi]</p>'
                        .'<p>Ako si ostavio e-mail ili telefon, dobićeš obaveštenje pri svakoj promeni statusa. Ako nisi, status uvek možeš da proveriš na stranici terena.</p>',
                ],
                [
                    'title' => 'Pravila korišćenja',
                    'body' => '<ul>'
                        .'<li><p>Prijavljuj samo <strong>stvarne probleme</strong> na terenima sa ove mape, sa fotografijom koju si sam napravio.</p></li>'
                        .'<li><p>Fotografiši <strong>teren i kvar, ne ljude</strong>. Slike na kojima se prepoznaju lica ili tablice mogu biti uklonjene.</p></li>'
                        .'<li><p>Piši pristojno i konkretno. Uvrede, reklame, politički sadržaj i lični podaci drugih ljudi nisu dozvoljeni; takve prijave se odbijaju.</p></li>'
                        .'<li><p>Ne šalji istu prijavu više puta. Ako problem već postoji na stranici terena, nova prijava nije potrebna.</p></li>'
                        .'<li><p>Broj prijava sa jedne adrese je ograničen (najviše 5 u minuti) radi zaštite od zloupotrebe.</p></li>'
                        .'<li><p>Ako vidiš neprikladnu objavljenu prijavu, klikni <strong>„Prijavi kao neprikladno”</strong> ispod nje i urednik će je ponovo pregledati.</p></li>'
                        .'</ul>'
                        .'<blockquote><p><strong>Ovo nije služba za hitne slučajeve.</strong> Ako je neko povređen ili je nešto opasno (oboren stub, ogoljeni kablovi, požar), odmah pozovi hitne službe: <strong>112</strong>, policija <strong>192</strong>, vatrogasci <strong>193</strong>, hitna pomoć <strong>194</strong>.</p></blockquote>',
                ],
                [
                    'title' => 'Privatnost',
                    'body' => '<ul>'
                        .'<li><p>Registracija nije potrebna. Ime i kontakt su dobrovoljni.</p></li>'
                        .'<li><p>Tvoj e-mail ili telefon <strong>nikada nisu javni</strong>. Vide ih samo urednici i služe isključivo za obaveštenja o tvojoj prijavi.</p></li>'
                        .'<li><p>Javno se prikazuju samo kategorija, opis, fotografija i istorija statusa.</p></li>'
                        .'<li><p>IP adresa se beleži samo radi zaštite od spama i zloupotrebe.</p></li>'
                        .'</ul>',
                ],
                [
                    'title' => 'Česta pitanja',
                    'body' => '<h3>Zašto ne vidim svoju prijavu odmah?</h3>'
                        .'<p>Svaka prijava prvo prolazi proveru urednika. Pojaviće se na stranici terena čim bude objavljena.</p>'
                        .'<h3>Zašto je fotografija obavezna?</h3>'
                        .'<p>Fotografija je dokaz da problem postoji i pomaže ekipi da unapred zna šta treba da ponese. Tako je sistem otvoren za sve, bez registracije, a ipak bez lažnih prijava.</p>'
                        .'<h3>Koliko traje popravka?</h3>'
                        .'<p>Zavisi od kvara i plana radova. Upravo zato je vremenska linija javna: svako vidi kad je prijava stigla i koliko je dugo u kom statusu.</p>'
                        .'<h3>Moj teren nije na mapi.</h3>'
                        .'<p>Tereni se dodaju postepeno, kako se obilaze škole i naselja u Medijani. Mapa će se širiti.</p>'
                        .'<h3>Šta znači „javno dostupan” teren?</h3>'
                        .'<p>Teren na koji svako može da uđe i igra. Školski tereni ponekad su otvoreni samo u određenim terminima ili samo za učenike, i to piše na stranici terena.</p>',
                ],
            ],
        ];
    }
}
