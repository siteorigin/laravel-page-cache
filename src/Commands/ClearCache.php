<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use SiteOrigin\KernelCrawler\Commands\StartCrawler;
use SiteOrigin\PageCache\Facades\PageCache;
use SiteOrigin\PageCache\PageCollection;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:clear {--touch : Crawl site from the home page to warm the cache.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the page cache.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $pages = new PageCollection();
        $pages->deletePages();

        if ($this->option('touch')) {
            $this->call('crawler:start', []);
        }
    }
}
