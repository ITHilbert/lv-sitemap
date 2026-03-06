<?php

namespace ITHilbert\Sitemap\Builders;

use Illuminate\Routing\Route;

/**
 * Fluenter Builder für Sitemap-Metadaten auf einer Route.
 *
 * Verwendung:
 *   ->sitemap()                                        // Standardwerte
 *   ->sitemap(['priority' => '0.8'])                   // Array-Syntax
 *   ->sitemap()->priority('0.5')->changefreq('yearly') // Fluente Syntax
 *   ->sitemap()->priority('0.5')->changefreq('yearly')() // mit __invoke() zur Route zurückkehren
 */
class SitemapRouteBuilder
{
    public function __construct(private Route $route)
    {
    }

    /**
     * Priorität setzen (0.0 – 1.0).
     */
    public function priority(string $value): static
    {
        $this->route->getAction()['sitemap_meta']['priority'] ?? null;
        $action = $this->route->getAction();
        $action['sitemap_meta']['priority'] = $value;
        $this->route->setAction($action);

        return $this;
    }

    /**
     * Änderungsfrequenz setzen.
     * Erlaubte Werte: always, hourly, daily, weekly, monthly, yearly, never
     */
    public function changefreq(string $value): static
    {
        $action = $this->route->getAction();
        $action['sitemap_meta']['changefreq'] = $value;
        $this->route->setAction($action);

        return $this;
    }

    /**
     * Lastmod-Datum setzen (ISO 8601 String oder true für heutiges Datum).
     *
     * @param string|true $value
     */
    public function lastmod(string|bool $value): static
    {
        $action = $this->route->getAction();
        $action['sitemap_meta']['lastmod'] = $value;
        $this->route->setAction($action);

        return $this;
    }

    /**
     * Ziel-Sitemap-Dateiname setzen.
     * Beispiel: ->sitemap()->file('laravel-sitemap.xml')
     */
    public function file(string $filename): static
    {
        $action = $this->route->getAction();
        $action['sitemap_meta']['file'] = $filename;
        $this->route->setAction($action);

        return $this;
    }

    /**
     * Gibt die Route zurück.
     * Ermöglicht den abschließenden ()  nach der fluenten Kette:
     *   ->sitemap()->priority('0.5')->changefreq('yearly')()
     */
    public function __invoke(): Route
    {
        return $this->route;
    }
}
