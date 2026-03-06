<?php

namespace ITHilbert\Sitemap\Commands;

use Illuminate\Console\Command;
use ITHilbert\Sitemap\Services\SitemapService;

class SitemapGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generiert die public/sitemap.xml aus den deklarierten Routen.';

    public function __construct(private readonly SitemapService $sitemapService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generiere Sitemap...');

        $groups     = $this->sitemapService->getEntriesGroupedByFile();
        $totalFiles = 0;
        $totalUrls  = 0;

        foreach ($groups as $filename => $entries) {
            $xml  = $this->sitemapService->generateXml($entries);
            $path = public_path($filename);

            file_put_contents($path, $xml);

            $count = count($entries);
            $this->line("  ✓ {$filename} → {$count} Einträge");

            $totalFiles++;
            $totalUrls += $count;
        }

        $this->info("Fertig! {$totalFiles} Datei(en) mit insgesamt {$totalUrls} Einträgen generiert.");

        return Command::SUCCESS;
    }
}
