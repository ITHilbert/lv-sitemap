<?php

namespace ITHilbert\Sitemap\Macros;

use Illuminate\Routing\Route;
use ITHilbert\Sitemap\Builders\SitemapRouteBuilder;

class RouteSitemapMacro
{
    /**
     * Registriert das Route-Macro "sitemap".
     *
     * Verwendungsweisen:
     *   ->sitemap()                                           // Standardwerte aus Config
     *   ->sitemap(['priority' => '0.8'])                     // Array-Syntax
     *   ->sitemap()->priority('0.5')->changefreq('yearly')   // Fluente Syntax
     *   ->sitemap()->priority('0.5')->changefreq('yearly')() // Fluent mit __invoke()
     */
    public static function register(): void
    {
        Route::macro('sitemap', function (string|array $options = []) {
            /** @var Route $this */
            $action = $this->getAction();

            // String = Dateiname der Ziel-Sitemap
            if (is_string($options)) {
                $action['sitemap_meta'] = ['file' => $options];
            } else {
                $action['sitemap_meta'] = $options;
            }

            $this->setAction($action);

            return new SitemapRouteBuilder($this);
        });
    }
}
