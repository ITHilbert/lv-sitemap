# ITHilbert Sitemap

Laravel-Package zur einfachen Verwaltung und Generierung von XML-Sitemaps direkt aus der Route-Definition.

## Features

- Routen direkt als Sitemap-Einträge deklarieren (Macro `->sitemap()`)
- Fluente API: `priority()`, `changefreq()`, `lastmod()`, `file()`
- Mehrere Sitemap-Dateien parallel generieren
- Dynamische Einträge via Generator-Callback (DB-Chunking-tauglich)
- Konfigurierbare Standardwerte (Priority, Changefreq, Lastmod, Url-Base)
- Saubere XML-Erzeugung via `XMLWriter` (korrektes Escaping)
- Validation: `priority` zwingend 0.0–1.0, `changefreq` als Enum
- Parameter-Routen (`/blog/{slug}`) werden automatisch übersprungen
- Artisan-Command `sitemap:generate` (optional mit `--path=`)

## Installation

```bash
composer require ithilbert/sitemap
php artisan vendor:publish --tag=sitemap-config   # optional
```

Service Provider und Facade werden automatisch über Laravel Package-Discovery registriert.

## Verwendung

### Routen deklarieren

```php
use ITHilbert\Sitemap\Enums\Changefreq;

// 1. Standardwerte aus Config
Route::get('/blog', [BlogController::class, 'index'])
    ->name('blog')
    ->sitemap();

// 2. Array-Syntax
Route::get('/impressum', [PageController::class, 'impressum'])
    ->sitemap(['priority' => '0.3', 'changefreq' => 'yearly']);

// 3. Fluente Syntax (mit Validation)
Route::get('/kontakt', [PageController::class, 'kontakt'])
    ->sitemap()
    ->priority('0.5')
    ->changefreq(Changefreq::Monthly);

// 4. Andere Ziel-Datei (String-Syntax)
Route::get('/laravel-guide', [PageController::class, 'laravel'])
    ->sitemap('laravel-sitemap.xml');

// 5. Fluente Syntax mit Datei
Route::get('/laravel-advanced', [PageController::class, 'advanced'])
    ->sitemap()
    ->file('laravel-sitemap.xml')
    ->priority('0.8');
```

Routen mit URL-Parametern (`/blog/{slug}`) erscheinen **nicht** in der statischen Sitemap — dynamische URLs gehören in einen Generator (siehe unten).

### Sitemap generieren

```bash
php artisan sitemap:generate
php artisan sitemap:generate --path=/custom/path
```

## Dynamische Einträge (z. B. aus der Datenbank)

Für zehntausende Einträge nicht in `web.php` registrieren, sondern erst beim `sitemap:generate`-Aufruf aus der DB laden:

```php
use ITHilbert\Sitemap\Facades\Sitemap;
use App\Models\Landingpage;

public function boot(): void
{
    Sitemap::addGenerator(function () {
        $entries = [];

        Landingpage::chunk(1000, function ($pages) use (&$entries) {
            foreach ($pages as $page) {
                $entries[] = [
                    'url'        => url('/berater/' . $page->slug),
                    'lastmod'    => $page->updated_at,    // Carbon, DateTime, string oder null
                    'priority'   => '0.8',
                    'changefreq' => 'weekly',
                    // 'file'    => 'landingpages.xml',   // optional
                ];
            }
        });

        return $entries;
    });
}
```

Akzeptierte `lastmod`-Typen: `string` (ISO 8601), `bool` (true=heute), `Carbon\CarbonInterface`, `DateTimeInterface`, `null`.

## Konfiguration (`config/sitemap.php`)

```php
return [
    'default_filename'   => 'sitemap.xml',
    'default_priority'   => '0.5',         // 0.0 – 1.0
    'default_changefreq' => 'monthly',     // always|hourly|daily|weekly|monthly|yearly|never
    'default_lastmod'    => true,          // true=heute, false=keins, string=fix
    'url_base'           => env('APP_URL'),
];
```

## Struktur

```
packages/lv-sitemap/
├── composer.json
├── README.md
├── LICENSE
├── src/
│   ├── SitemapServiceProvider.php
│   ├── Builders/SitemapRouteBuilder.php   # Fluente API + Validation
│   ├── Commands/SitemapGenerateCommand.php
│   ├── Config/sitemap.php
│   ├── Data/SitemapEntry.php              # Readonly DTO
│   ├── Enums/Changefreq.php
│   ├── Facades/Sitemap.php
│   ├── Macros/RouteSitemapMacro.php
│   └── Services/SitemapService.php        # Kern-Logik + XMLWriter
└── tests/
    ├── Unit/                              # Enum, DTO
    └── Feature/                           # Service, Builder, Command
```

## Lizenz

MIT – siehe [LICENSE](LICENSE)
