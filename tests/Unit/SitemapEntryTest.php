<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Tests\Unit;

use ITHilbert\Sitemap\Data\SitemapEntry;
use ITHilbert\Sitemap\Enums\Changefreq;
use PHPUnit\Framework\TestCase;

final class SitemapEntryTest extends TestCase
{
    public function test_creates_entry_with_valid_priority(): void
    {
        $entry = new SitemapEntry(
            url: 'https://example.com/',
            priority: '0.7',
            changefreq: Changefreq::Weekly,
            lastmod: '2026-05-22T00:00:00+00:00',
        );

        $this->assertSame('https://example.com/', $entry->url);
        $this->assertSame('0.7', $entry->priority);
        $this->assertSame(Changefreq::Weekly, $entry->changefreq);
        $this->assertSame('2026-05-22T00:00:00+00:00', $entry->lastmod);
    }

    public function test_rejects_priority_above_one(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SitemapEntry('https://example.com/', '1.5', Changefreq::Weekly);
    }

    public function test_rejects_priority_below_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SitemapEntry('https://example.com/', '-0.1', Changefreq::Weekly);
    }

    public function test_rejects_non_numeric_priority(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SitemapEntry('https://example.com/', 'high', Changefreq::Weekly);
    }

    public function test_accepts_boundary_values(): void
    {
        $zero = new SitemapEntry('https://example.com/', '0.0', Changefreq::Weekly);
        $one  = new SitemapEntry('https://example.com/', '1.0', Changefreq::Weekly);

        $this->assertSame('0.0', $zero->priority);
        $this->assertSame('1.0', $one->priority);
    }
}
