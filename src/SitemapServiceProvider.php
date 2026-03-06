<?php

namespace ITHilbert\Sitemap;

use Illuminate\Support\ServiceProvider;
use ITHilbert\Sitemap\Commands\SitemapGenerateCommand;
use ITHilbert\Sitemap\Macros\RouteSitemapMacro;
use ITHilbert\Sitemap\Services\SitemapService;

class SitemapServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        // Route-Macro registrieren
        RouteSitemapMacro::register();

        // Config publizieren
        $this->publishes([
            __DIR__ . '/Config/sitemap.php' => config_path('sitemap.php'),
        ], 'sitemap-config');

        // Command registrieren
        $this->registerCommands();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Sicherstellen, dass die Default-Config geladen ist
        $this->mergeConfigFrom(__DIR__ . '/Config/sitemap.php', 'sitemap');

        // Service als Singleton binden
        $this->app->singleton(SitemapService::class);
    }

    /**
     * Artisan Commands registrieren.
     */
    protected function registerCommands(): void
    {
        $this->commands([
            SitemapGenerateCommand::class,
        ]);
    }
}
