<?php

namespace ITHilbert\Sitemap\Services;

use Illuminate\Support\Facades\Route;

class SitemapService
{
    /**
     * Liste aller registrierten Generatoren-Callbacks.
     * @var array<callable>
     */
    protected array $generators = [];

    /**
     * Fügt einen neuen Generator (Closure/Callback) hinzu,
     * der zur Laufzeit von `sitemap:generate` ausgeführt wird,
     * um Datenbank-Einträge hinzuzufügen.
     *
     * @param callable $generator
     */
    public function addGenerator(callable $generator): void
    {
        $this->generators[] = $generator;
    }

    /**
     * Liefert alle Sitemap-fähigen Routen, gruppiert nach Ziel-Dateiname.
     *
     * @return array<string, array<array{url: string, priority: string, changefreq: string, lastmod: string|null}>>
     */
    public function getEntriesGroupedByFile(): array
    {
        $defaultFilename   = config('sitemap.default_filename', 'sitemap.xml');
        $defaultPriority   = config('sitemap.default_priority', '0.5');
        $defaultChangefreq = config('sitemap.default_changefreq', 'monthly');
        $defaultLastmod    = config('sitemap.default_lastmod', true);
        $urlBase           = rtrim(config('sitemap.url_base', config('app.url')), '/');

        $groups = [];

        // 1. Statische Laravel-Routen verarbeiten
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction();

            if (!isset($action['sitemap_meta'])) {
                continue;
            }

            if (!in_array('GET', $route->methods())) {
                continue;
            }

            $meta     = $action['sitemap_meta'];
            $filename = $meta['file'] ?? $defaultFilename;

            $groups[$filename][] = [
                'url'        => $urlBase . '/' . ltrim($route->uri(), '/'),
                'priority'   => $meta['priority']   ?? $defaultPriority,
                'changefreq' => $meta['changefreq'] ?? $defaultChangefreq,
                'lastmod'    => $this->resolveLastmod($meta['lastmod'] ?? $defaultLastmod),
            ];
        }

        // 2. Dynamische Generatoren verarbeiten (DB Einträge)
        foreach ($this->generators as $generator) {
            $entries = call_user_func($generator);
            if (!is_array($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                // Notwendige Felder absichern
                if (!isset($entry['url'])) {
                    continue;
                }

                $filename = $entry['file'] ?? $defaultFilename;

                $groups[$filename][] = [
                    'url'        => $entry['url'], // Url wird vom Generator fertig (!) geliefert (inkl. https://)
                    'priority'   => $entry['priority']   ?? $defaultPriority,
                    'changefreq' => $entry['changefreq'] ?? $defaultChangefreq,
                    'lastmod'    => $this->resolveLastmod($entry['lastmod'] ?? $defaultLastmod),
                ];
            }
        }

        return $groups;
    }

    /**
     * Generiert das XML für eine Liste von Einträgen.
     *
     * @param array<array{url: string, priority: string, changefreq: string, lastmod: string|null}> $entries
     */
    public function generateXml(array $entries): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($entries as $entry) {
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>' . htmlspecialchars($entry['url'], ENT_XML1, 'UTF-8') . '</loc>' . PHP_EOL;

            if ($entry['lastmod']) {
                $xml .= '        <lastmod>' . $entry['lastmod'] . '</lastmod>' . PHP_EOL;
            }

            $xml .= '        <changefreq>' . $entry['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '        <priority>' . $entry['priority'] . '</priority>' . PHP_EOL;
            $xml .= '    </url>' . PHP_EOL;
        }

        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    }

    /**
     * Erstellt den Lastmod-String.
     */
    private function resolveLastmod(mixed $lastmod): ?string
    {
        if ($lastmod === true) {
            return now()->toAtomString();
        }

        if (is_string($lastmod) && !empty($lastmod)) {
            return $lastmod;
        }

        return null;
    }
}

