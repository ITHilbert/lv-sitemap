<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Data;

use ITHilbert\Sitemap\Enums\Changefreq;

final readonly class SitemapEntry
{
    public function __construct(
        public string $url,
        public string $priority,
        public Changefreq $changefreq,
        public ?string $lastmod = null,
    ) {
        if (! self::isValidPriority($priority)) {
            throw new \InvalidArgumentException(
                "Ungültige priority: '{$priority}'. Muss im Bereich 0.0–1.0 liegen."
            );
        }
    }

    private static function isValidPriority(string $priority): bool
    {
        if (! is_numeric($priority)) {
            return false;
        }

        $value = (float) $priority;

        return $value >= 0.0 && $value <= 1.0;
    }
}
