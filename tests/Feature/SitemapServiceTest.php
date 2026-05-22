<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Tests\Feature;

use Illuminate\Support\Facades\Route;
use ITHilbert\Sitemap\Data\SitemapEntry;
use ITHilbert\Sitemap\Enums\Changefreq;
use ITHilbert\Sitemap\Services\SitemapService;
use Tests\TestCase;

final class SitemapServiceTest extends TestCase
{
    private SitemapService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sitemap.default_filename', 'sitemap.xml');
        config()->set('sitemap.default_priority', '0.5');
        config()->set('sitemap.default_changefreq', 'monthly');
        config()->set('sitemap.default_lastmod', false);
        config()->set('sitemap.url_base', 'https://example.com');

        $this->service = new SitemapService();
    }

    public function test_route_without_sitemap_meta_wird_ignoriert(): void
    {
        Route::get('/intern', fn () => 'x');

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertArrayNotHasKey('sitemap.xml', $groups);
    }

    public function test_route_mit_sitemap_meta_landet_in_default_datei(): void
    {
        Route::get('/blog', fn () => 'x')->sitemap();

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertCount(1, $groups['sitemap.xml']);
        $this->assertSame('https://example.com/blog', $groups['sitemap.xml'][0]->url);
        $this->assertSame('0.5', $groups['sitemap.xml'][0]->priority);
        $this->assertSame(Changefreq::Monthly, $groups['sitemap.xml'][0]->changefreq);
    }

    public function test_param_routen_werden_ausgeschlossen(): void
    {
        Route::get('/blog/{slug}', fn () => 'x')->sitemap();

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertSame([], $groups);
    }

    public function test_fluente_syntax_setzt_priority_und_changefreq(): void
    {
        Route::get('/kontakt', fn () => 'x')
            ->sitemap()
            ->priority('0.9')
            ->changefreq('yearly');

        $groups = $this->service->getEntriesGroupedByFile();
        $entry  = $groups['sitemap.xml'][0];

        $this->assertSame('0.9', $entry->priority);
        $this->assertSame(Changefreq::Yearly, $entry->changefreq);
    }

    public function test_string_argument_setzt_eigene_datei(): void
    {
        Route::get('/produkt-a', fn () => 'x')->sitemap('produkte.xml');

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertArrayHasKey('produkte.xml', $groups);
        $this->assertArrayNotHasKey('sitemap.xml', $groups);
    }

    public function test_array_argument_wird_zusammengef_uehrt(): void
    {
        Route::get('/impressum', fn () => 'x')
            ->sitemap(['priority' => '0.3'])
            ->changefreq('yearly');

        $entry = $this->service->getEntriesGroupedByFile()['sitemap.xml'][0];

        $this->assertSame('0.3', $entry->priority);
        $this->assertSame(Changefreq::Yearly, $entry->changefreq);
    }

    public function test_generator_callback_wird_einbezogen(): void
    {
        $this->service->addGenerator(fn () => [
            ['url' => 'https://example.com/db/1', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => 'https://example.com/db/2', 'file' => 'produkte.xml'],
        ]);

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertCount(1, $groups['sitemap.xml']);
        $this->assertCount(1, $groups['produkte.xml']);
        $this->assertSame('0.8', $groups['sitemap.xml'][0]->priority);
        $this->assertSame(Changefreq::Daily, $groups['sitemap.xml'][0]->changefreq);
    }

    public function test_generator_eintraege_ohne_url_werden_verworfen(): void
    {
        $this->service->addGenerator(fn () => [
            ['priority' => '0.5'],
            ['url' => 'https://example.com/x'],
        ]);

        $groups = $this->service->getEntriesGroupedByFile();

        $this->assertCount(1, $groups['sitemap.xml']);
        $this->assertSame('https://example.com/x', $groups['sitemap.xml'][0]->url);
    }

    public function test_generate_xml_erzeugt_validen_sitemap_namespace(): void
    {
        $entries = [
            new SitemapEntry('https://example.com/a', '0.7', Changefreq::Weekly, '2026-05-22T00:00:00+00:00'),
            new SitemapEntry('https://example.com/b', '0.3', Changefreq::Yearly),
        ];

        $xml = $this->service->generateXml($entries);

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $xml);
        $this->assertStringContainsString('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', $xml);
        $this->assertStringContainsString('<loc>https://example.com/a</loc>', $xml);
        $this->assertStringContainsString('<lastmod>2026-05-22T00:00:00+00:00</lastmod>', $xml);
        $this->assertStringContainsString('<changefreq>yearly</changefreq>', $xml);
        $this->assertStringContainsString('<priority>0.3</priority>', $xml);

        $doc = new \DOMDocument();
        $this->assertTrue($doc->loadXML($xml));
        $this->assertSame('urlset', $doc->documentElement->nodeName);
        $this->assertCount(2, $doc->getElementsByTagName('url'));
    }

    public function test_generate_xml_escaped_ampersand_in_url(): void
    {
        $entries = [
            new SitemapEntry('https://example.com/?a=1&b=2', '0.5', Changefreq::Monthly),
        ];

        $xml = $this->service->generateXml($entries);

        $this->assertStringContainsString('https://example.com/?a=1&amp;b=2', $xml);
        $this->assertStringNotContainsString('a=1&b=2', $xml);
    }

    public function test_clear_generators(): void
    {
        $this->service->addGenerator(fn () => [['url' => 'https://example.com/x']]);
        $this->service->clearGenerators();

        $this->assertSame([], $this->service->getEntriesGroupedByFile());
    }
}
