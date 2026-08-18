<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Tereni\CourtController as TereniCourtController;
use App\Http\Controllers\Tereni\MapController as TereniMapController;
use App\Http\Controllers\Tereni\QrController as TereniQrController;
use App\Http\Controllers\Tereni\ReportController as TereniReportController;
use Illuminate\Support\Facades\Route;

// Kad je Tereni modul upaljen, mapa JE sajt — koren vodi pravo na nju.
Route::get('/', fn () => config('site.features.tereni')
    ? redirect()->route('tereni.map')
    : redirect(app()->getLocale()));

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

/*
| Tereni Medijana — public map + QR reporting. Unprefixed (like /blog) so the
| printed QR URLs stay short and locale-independent. Dormant unless the feature
| is enabled for this client.
*/
if (config('site.features.tereni')) {
    Route::get('/mapa', [TereniMapController::class, 'index'])->name('tereni.map');
    Route::get('/tereni', [TereniCourtController::class, 'index'])->name('tereni.list');
    Route::get('/teren/{court}', [TereniCourtController::class, 'show'])->name('tereni.court');
    Route::get('/teren/{court}/qr.svg', [TereniQrController::class, 'show'])->name('tereni.court.qr');

    Route::post('/teren/{court}/prijava', [TereniReportController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('tereni.report.store');

    Route::post('/prijava/{report}/flag', [TereniReportController::class, 'flag'])
        ->middleware('throttle:10,1')
        ->name('tereni.report.flag');
}

// Blog stays unprefixed for stable SEO URLs; chrome renders in the default locale.
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::post('/contact', [ContactController::class, 'submit'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');

Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'en|sr'],
    'middleware' => 'setlocale',
], function () {
    // Advokatska landing je deo site-core template-a; za Tereni proizvod i
    // locale-home vodi na mapu (ruta 'home' ostaje zbog linkova u layoutu).
    Route::get('/', function () {
        return config('site.features.tereni')
            ? redirect()->route('tereni.map')
            : app(HomeController::class)->index();
    })->name('home');
});
