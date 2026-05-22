<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use ITHilbert\Sitemap\Services\SitemapService;

final class SitemapGenerateCommand extends Command
{
    protected $signature = 'sitemap:generate {--path= : Zielverzeichnis (Default: public/)}';

    protected $description = 'Generiert die Sitemap-XML-Dateien aus deklarierten Routen und Generatoren.';

    public function __construct(
        private readonly SitemapService $sitemapService,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Generiere Sitemap...');

        $targetDir = $this->resolveTargetDir();
        $this->ensureDirectoryExists($targetDir);

        $groups = $this->sitemapService->getEntriesGroupedByFile();

        if ($groups === []) {
            $this->warn('Keine Sitemap-Einträge gefunden. Es wurden keine Dateien geschrieben.');

            return self::SUCCESS;
        }

        $totalFiles = 0;
        $totalUrls  = 0;

        foreach ($groups as $filename => $entries) {
            $xml  = $this->sitemapService->generateXml($entries);
            $path = $targetDir . DIRECTORY_SEPARATOR . $filename;

            $this->filesystem->put($path, $xml);

            $count = count($entries);
            $this->line(sprintf('  ✓ %s → %d Eintr%s', $filename, $count, $count === 1 ? 'ag' : 'äge'));

            $totalFiles++;
            $totalUrls += $count;
        }

        $this->info(sprintf(
            'Fertig! %d Datei(en) mit insgesamt %d Einträgen generiert.',
            $totalFiles,
            $totalUrls,
        ));

        return self::SUCCESS;
    }

    private function resolveTargetDir(): string
    {
        $pathOption = $this->option('path');

        if (is_string($pathOption) && $pathOption !== '') {
            return rtrim($pathOption, DIRECTORY_SEPARATOR);
        }

        return public_path();
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }
    }
}
