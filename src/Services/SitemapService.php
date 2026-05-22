<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;
use ITHilbert\Sitemap\Data\SitemapEntry;
use ITHilbert\Sitemap\Enums\Changefreq;
use XMLWriter;

final class SitemapService
{
    /**
     * @var array<callable>
     */
    private array $generators = [];

    public function addGenerator(callable $generator): void
    {
        $this->generators[] = $generator;
    }

    public function clearGenerators(): void
    {
        $this->generators = [];
    }

    /**
     * Liefert alle Sitemap-Einträge gruppiert nach Ziel-Datei.
     *
     * @return array<string, list<SitemapEntry>>
     */
    public function getEntriesGroupedByFile(): array
    {
        $defaults = $this->loadDefaults();

        $groups = [];
        $this->collectRouteEntries($groups, $defaults);
        $this->collectGeneratorEntries($groups, $defaults);

        return $groups;
    }

    /**
     * @param list<SitemapEntry> $entries
     */
    public function generateXml(array $entries): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('    ');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($entries as $entry) {
            $writer->startElement('url');
            $writer->writeElement('loc', $entry->url);

            if ($entry->lastmod !== null) {
                $writer->writeElement('lastmod', $entry->lastmod);
            }

            $writer->writeElement('changefreq', $entry->changefreq->value);
            $writer->writeElement('priority', $entry->priority);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }

    /**
     * @return array{filename: string, priority: string, changefreq: Changefreq, lastmod: bool|string, url_base: string}
     */
    private function loadDefaults(): array
    {
        return [
            'filename'   => (string) config('sitemap.default_filename', 'sitemap.xml'),
            'priority'   => (string) config('sitemap.default_priority', '0.5'),
            'changefreq' => Changefreq::fromInput((string) config('sitemap.default_changefreq', 'monthly')),
            'lastmod'    => config('sitemap.default_lastmod', true),
            'url_base'   => rtrim((string) config('sitemap.url_base', (string) config('app.url')), '/'),
        ];
    }

    /**
     * @param array<string, list<SitemapEntry>> $groups
     * @param array{filename: string, priority: string, changefreq: Changefreq, lastmod: bool|string, url_base: string} $defaults
     */
    private function collectRouteEntries(array &$groups, array $defaults): void
    {
        foreach (Route::getRoutes() as $route) {
            $action = $route->getAction();

            if (! isset($action['sitemap_meta'])) {
                continue;
            }
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            if ($this->hasUriParameters($route->uri())) {
                continue;
            }

            $meta     = $action['sitemap_meta'];
            $filename = $meta['file'] ?? $defaults['filename'];

            $groups[$filename][] = new SitemapEntry(
                url: $defaults['url_base'] . '/' . ltrim($route->uri(), '/'),
                priority: $meta['priority'] ?? $defaults['priority'],
                changefreq: isset($meta['changefreq'])
                    ? Changefreq::fromInput($meta['changefreq'])
                    : $defaults['changefreq'],
                lastmod: $this->resolveLastmod($meta['lastmod'] ?? $defaults['lastmod']),
            );
        }
    }

    /**
     * @param array<string, list<SitemapEntry>> $groups
     * @param array{filename: string, priority: string, changefreq: Changefreq, lastmod: bool|string, url_base: string} $defaults
     */
    private function collectGeneratorEntries(array &$groups, array $defaults): void
    {
        foreach ($this->generators as $generator) {
            $entries = $generator();
            if (! is_iterable($entries)) {
                continue;
            }

            foreach ($entries as $entry) {
                if (! is_array($entry) || ! isset($entry['url'])) {
                    continue;
                }

                $filename = $entry['file'] ?? $defaults['filename'];

                $groups[$filename][] = new SitemapEntry(
                    url: (string) $entry['url'],
                    priority: (string) ($entry['priority'] ?? $defaults['priority']),
                    changefreq: isset($entry['changefreq'])
                        ? Changefreq::fromInput((string) $entry['changefreq'])
                        : $defaults['changefreq'],
                    lastmod: $this->resolveLastmod($entry['lastmod'] ?? $defaults['lastmod']),
                );
            }
        }
    }

    private function hasUriParameters(string $uri): bool
    {
        return str_contains($uri, '{');
    }

    private function resolveLastmod(mixed $lastmod): ?string
    {
        if ($lastmod === true) {
            return now()->toAtomString();
        }

        if ($lastmod instanceof CarbonInterface) {
            return $lastmod->toAtomString();
        }

        if ($lastmod instanceof \DateTimeInterface) {
            return $lastmod->format(\DateTimeInterface::ATOM);
        }

        if (is_string($lastmod) && $lastmod !== '') {
            return $lastmod;
        }

        return null;
    }
}
