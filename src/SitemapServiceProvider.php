<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap;

use Illuminate\Support\ServiceProvider;
use ITHilbert\Sitemap\Commands\SitemapGenerateCommand;
use ITHilbert\Sitemap\Macros\RouteSitemapMacro;
use ITHilbert\Sitemap\Services\SitemapService;

class SitemapServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RouteSitemapMacro::register();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/sitemap.php' => config_path('sitemap.php'),
            ], 'sitemap-config');

            $this->commands([
                SitemapGenerateCommand::class,
            ]);
        }
    }

    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/sitemap.php', 'sitemap');

        $this->app->singleton(SitemapService::class);
        $this->app->alias(SitemapService::class, 'Sitemap');
    }
}
