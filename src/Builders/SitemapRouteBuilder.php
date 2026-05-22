<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Builders;

use Carbon\CarbonInterface;
use Illuminate\Routing\Route;
use ITHilbert\Sitemap\Enums\Changefreq;

/**
 * Fluenter Builder für Sitemap-Metadaten auf einer Route.
 *
 * Verwendung:
 *   ->sitemap()                                        // Standardwerte
 *   ->sitemap(['priority' => '0.8'])                   // Array-Syntax
 *   ->sitemap()->priority('0.5')->changefreq('yearly') // Fluente Syntax
 *   ->sitemap()->priority('0.5')->changefreq('yearly')() // mit __invoke() zur Route zurückkehren
 */
final class SitemapRouteBuilder
{
    public function __construct(private readonly Route $route)
    {
    }

    /**
     * Priorität setzen (0.0 – 1.0).
     */
    public function priority(string|float $value): static
    {
        $value = (string) $value;

        if (! is_numeric($value) || (float) $value < 0.0 || (float) $value > 1.0) {
            throw new \InvalidArgumentException(
                "Ungültige priority: '{$value}'. Muss im Bereich 0.0–1.0 liegen."
            );
        }

        return $this->setMeta('priority', $value);
    }

    /**
     * Änderungsfrequenz setzen.
     * Erlaubt: always, hourly, daily, weekly, monthly, yearly, never
     */
    public function changefreq(string|Changefreq $value): static
    {
        $enum = $value instanceof Changefreq ? $value : Changefreq::fromInput($value);

        return $this->setMeta('changefreq', $enum->value);
    }

    /**
     * Lastmod setzen. Akzeptiert ISO-String, true (=heute), Carbon oder DateTimeInterface.
     */
    public function lastmod(string|bool|CarbonInterface|\DateTimeInterface $value): static
    {
        return $this->setMeta('lastmod', $value);
    }

    /**
     * Ziel-Sitemap-Dateiname setzen.
     */
    public function file(string $filename): static
    {
        return $this->setMeta('file', $filename);
    }

    /**
     * Gibt die Route zurück.
     */
    public function __invoke(): Route
    {
        return $this->route;
    }

    private function setMeta(string $key, mixed $value): static
    {
        $action = $this->route->getAction();
        $action['sitemap_meta'][$key] = $value;
        $this->route->setAction($action);

        return $this;
    }
}
