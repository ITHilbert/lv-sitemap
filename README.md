# ITHilbert Sitemap

Ein Laravel-Package zur einfachen Verwaltung und Generierung von XML-Sitemaps direkt über die Route-Definition.

---

## Features

- Routen direkt als Sitemap-Einträge deklarieren
- Fluente API für Priorität, Changefreq, Lastmod und Zieldatei
- Mehrere Sitemap-Dateien gleichzeitig unterstützen
- Standardwerte über `config/sitemap.php` konfigurierbar
- Artisan-Command `sitemap:generate` erzeugt valides XML (sitemaps.org Standard)

---

## Installation

1. Autoload-Eintrag in der Root-`composer.json` ergänzen:

```json
"autoload": {
    "psr-4": {
        "ITHilbert\\Sitemap\\": "packages/sitemap/src"
    }
}
```

2. Autoloader neu generieren:

```bash
composer dump-autoload
```

3. ServiceProvider in `config/app.php` eintragen (alphabetisch sortiert):

```php
ITHilbert\Sitemap\SitemapServiceProvider::class,
```

4. Config publizieren (optional):

```bash
php artisan vendor:publish --provider="ITHilbert\Sitemap\SitemapServiceProvider"
```

---

## Verwendung

### Route deklarieren

```php
// 1. Mit Config-Standardwerten
Route::get('/blog', [BlogController::class, 'index'])
    ->name('blog')
    ->sitemap();

// 2. Mit eigenen Werten (Array-Syntax)
Route::get('/impressum', [PageController::class, 'impressum'])
    ->name('impressum')
    ->sitemap(['priority' => '0.3', 'changefreq' => 'yearly']);

// 3. Fluente Syntax
Route::get('/kontakt', [PageController::class, 'kontakt'])
    ->name('kontakt')
    ->sitemap()
    ->priority('0.5')
    ->changefreq('monthly');

// 4. Andere Sitemap-Datei (String-Syntax)
Route::get('/laravel-guide', [PageController::class, 'laravel'])
    ->name('laravel-guide')
    ->sitemap('laravel-sitemap.xml');

// 5. Fluente Syntax mit anderer Zieldatei
Route::get('/laravel-advanced', [PageController::class, 'advanced'])
    ->name('laravel-advanced')
    ->sitemap()
    ->file('laravel-sitemap.xml')
    ->priority('0.8');

// 6. Nicht in der Sitemap erscheinen
Route::get('/intern', [InternController::class, 'index'])
    ->name('intern');
```

### Sitemap generieren

```bash
php artisan sitemap:generate
```

Ausgabe:
```
Generiere Sitemap...
  ✓ sitemap.xml         → 4 Einträge
  ✓ laravel-sitemap.xml → 1 Eintrag
Fertig! 2 Datei(en) mit insgesamt 5 Einträgen generiert.
```

Die Dateien werden unter `public/` abgelegt.

---

## Dynamische Einträge (z. B. aus einer Datenbank)

Wenn du zehntausende von Einträgen (wie Landingpages oder Produkte) hast, solltest du diese nicht in der `web.php` registrieren, sondern speicherschonend erst dann aus der Datenbank laden, wenn der `sitemap:generate`-Befehl ausgeführt wird.

Dazu stellt das Paket einen **Generator-Callback** bereit. Du kannst Generatoren z.B. in der `boot()`-Methode des `AppServiceProvider` registrieren:

```php
use ITHilbert\Sitemap\Facades\Sitemap;
use App\Models\Landingpage;

public function boot()
{
    Sitemap::addGenerator(function () {
        $entries = [];

        // Chunking verwenden, um Speicher bei großen DB-Tabellen zu sparen!
        Landingpage::chunk(1000, function($pages) use (&$entries) {
            foreach($pages as $page) {
                $entries[] = [
                    'url'        => url('/berater/' . $page->slug), // Kompletter URL-String notwendig
                    'lastmod'    => $page->updated_at,              // Kann Carbon, String oder null sein
                    'priority'   => '0.8',
                    'changefreq' => 'weekly',
                    // 'file'    => 'landingpages.xml'              // (Optional) Eigene XML-Datei
                ];
            }
        });

        return $entries;
    });
}
```

Die Closure wird **nur beim Aufruf des Artisan-Befehls** abgearbeitet. Das normale Frontend bleibt rasant schnell, selbst bei 100.000 generierten Links.

---

## Konfiguration

Nach dem Publizieren kann `config/sitemap.php` angepasst werden:

```php
return [
    'default_filename'  => 'sitemap.xml',     // Dateiname wenn kein ->sitemap('...')  angegeben
    'default_priority'  => '0.5',             // 0.0 – 1.0
    'default_changefreq'=> 'monthly',         // always|hourly|daily|weekly|monthly|yearly|never
    'default_lastmod'   => true,              // true = heutiges Datum, false = keins, string = fixes Datum
    'url_base'          => env('APP_URL'),     // Basis-URL der generierten URLs
];
```

### changefreq-Werte

| Wert | Bedeutung |
|---|---|
| `always` | Jede Abfrage |
| `hourly` | Stündlich |
| `daily` | Täglich |
| `weekly` | Wöchentlich |
| `monthly` | Monatlich |
| `yearly` | Jährlich |
| `never` | Unveränderlich |

---

## Struktur

```
packages/sitemap/
├── composer.json
├── README.md
├── LICENSE
└── src/
    ├── SitemapServiceProvider.php
    ├── Builders/
    │   └── SitemapRouteBuilder.php   # Fluente API
    ├── Commands/
    │   └── SitemapGenerateCommand.php # php artisan sitemap:generate
    ├── Config/
    │   └── sitemap.php               # Standardwerte
    ├── Macros/
    │   └── RouteSitemapMacro.php     # Route->sitemap() Macro
    └── Services/
        └── SitemapService.php        # Kern-Logik
```

---

## Lizenz

MIT – siehe [LICENSE](LICENSE)
