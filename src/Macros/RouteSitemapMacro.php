<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Macros;

use Illuminate\Routing\Route;
use ITHilbert\Sitemap\Builders\SitemapRouteBuilder;

final class RouteSitemapMacro
{
    /**
     * Registriert das Route-Macro "sitemap".
     *
     * Verwendung:
     *   ->sitemap()                                           // Standardwerte aus Config
     *   ->sitemap('produkte.xml')                             // String = Zieldatei
     *   ->sitemap(['priority' => '0.8'])                      // Array-Syntax
     *   ->sitemap()->priority('0.5')->changefreq('yearly')    // Fluente Syntax
     *   ->sitemap()->priority('0.5')->changefreq('yearly')()  // Fluent mit __invoke()
     */
    public static function register(): void
    {
        Route::macro('sitemap', function (string|array $options = []) {
            /** @var Route $this */
            $action = $this->getAction();
            $existing = $action['sitemap_meta'] ?? [];

            $incoming = is_string($options)
                ? ['file' => $options]
                : $options;

            $action['sitemap_meta'] = array_merge($existing, $incoming);
            $this->setAction($action);

            return new SitemapRouteBuilder($this);
        });
    }
}
