<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Facades;

use Illuminate\Support\Facades\Facade;
use ITHilbert\Sitemap\Data\SitemapEntry;
use ITHilbert\Sitemap\Services\SitemapService;

/**
 * @method static void addGenerator(callable $generator)
 * @method static void clearGenerators()
 * @method static array<string, list<SitemapEntry>> getEntriesGroupedByFile()
 * @method static string generateXml(list<SitemapEntry> $entries)
 *
 * @see SitemapService
 */
final class Sitemap extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return SitemapService::class;
    }
}
