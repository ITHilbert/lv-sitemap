<?php

namespace ITHilbert\Sitemap\Facades;

use Illuminate\Support\Facades\Facade;
use ITHilbert\Sitemap\Services\SitemapService;

/**
 * @method static void addGenerator(callable $generator)
 *
 * @see \ITHilbert\Sitemap\Services\SitemapService
 */
class Sitemap extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SitemapService::class;
    }
}
