<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Tests\Feature;

use Illuminate\Support\Facades\Route;
use ITHilbert\Sitemap\Builders\SitemapRouteBuilder;
use ITHilbert\Sitemap\Enums\Changefreq;
use ITHilbert\Sitemap\Tests\TestCase;

final class SitemapBuilderTest extends TestCase
{
    public function test_builder_invoke_gibt_route_zurueck(): void
    {
        $route   = Route::get('/x', fn () => 'x');
        $builder = $route->sitemap();

        $this->assertInstanceOf(SitemapRouteBuilder::class, $builder);
        $this->assertSame($route, $builder());
    }

    public function test_priority_validiert_bereich(): void
    {
        $route = Route::get('/x', fn () => 'x');

        $this->expectException(\InvalidArgumentException::class);
        $route->sitemap()->priority('1.5');
    }

    public function test_changefreq_akzeptiert_enum(): void
    {
        $route = Route::get('/x', fn () => 'x');
        $route->sitemap()->changefreq(Changefreq::Daily);

        $meta = $route->getAction()['sitemap_meta'];
        $this->assertSame('daily', $meta['changefreq']);
    }

    public function test_changefreq_lehnt_unbekannten_string_ab(): void
    {
        $route = Route::get('/x', fn () => 'x');

        $this->expectException(\InvalidArgumentException::class);
        $route->sitemap()->changefreq('forever');
    }

    public function test_macro_mehrfacher_aufruf_merged_meta(): void
    {
        $route = Route::get('/x', fn () => 'x');
        $route->sitemap(['priority' => '0.8']);
        $route->sitemap(['changefreq' => 'weekly']);

        $meta = $route->getAction()['sitemap_meta'];

        $this->assertSame('0.8', $meta['priority']);
        $this->assertSame('weekly', $meta['changefreq']);
    }
}
