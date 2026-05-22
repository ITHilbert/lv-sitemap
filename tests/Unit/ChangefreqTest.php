<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Tests\Unit;

use ITHilbert\Sitemap\Enums\Changefreq;
use PHPUnit\Framework\TestCase;

final class ChangefreqTest extends TestCase
{
    public function test_from_input_accepts_known_values(): void
    {
        $this->assertSame(Changefreq::Weekly, Changefreq::fromInput('weekly'));
        $this->assertSame(Changefreq::Daily, Changefreq::fromInput('DAILY'));
        $this->assertSame(Changefreq::Never, Changefreq::fromInput('  never  '));
    }

    public function test_from_input_rejects_unknown_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Changefreq::fromInput('forever');
    }
}
