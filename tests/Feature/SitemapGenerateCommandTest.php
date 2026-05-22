<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Route;
use ITHilbert\Sitemap\Services\SitemapService;
use Tests\TestCase;

final class SitemapGenerateCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sitemap.default_filename', 'sitemap.xml');
        config()->set('sitemap.default_priority', '0.5');
        config()->set('sitemap.default_changefreq', 'monthly');
        config()->set('sitemap.default_lastmod', false);
        config()->set('sitemap.url_base', 'https://example.com');

        $this->tempDir = sys_get_temp_dir() . '/sitemap-test-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_command_schreibt_xml_in_zielverzeichnis(): void
    {
        Route::get('/a', fn () => 'x')->sitemap();
        Route::get('/b', fn () => 'x')->sitemap('produkte.xml');

        $exit = $this->artisan('sitemap:generate', ['--path' => $this->tempDir])->run();

        $this->assertSame(0, $exit);
        $this->assertFileExists($this->tempDir . '/sitemap.xml');
        $this->assertFileExists($this->tempDir . '/produkte.xml');

        $xml = file_get_contents($this->tempDir . '/sitemap.xml');
        $this->assertStringContainsString('https://example.com/a', $xml);
    }

    public function test_command_meldet_warnung_wenn_keine_eintraege(): void
    {
        $this->app->forgetInstance(SitemapService::class);

        $this->artisan('sitemap:generate', ['--path' => $this->tempDir])
            ->expectsOutput('Generiere Sitemap...')
            ->expectsOutputToContain('Keine Sitemap-Einträge gefunden.')
            ->assertExitCode(0);
    }

    public function test_command_erstellt_zielverzeichnis_falls_fehlt(): void
    {
        Route::get('/a', fn () => 'x')->sitemap();

        $nested = $this->tempDir . '/nested/level';

        $exit = $this->artisan('sitemap:generate', ['--path' => $nested])->run();

        $this->assertSame(0, $exit);
        $this->assertFileExists($nested . '/sitemap.xml');
    }
}
