<?php

namespace SiteOrigin\PageCache\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SiteOrigin\PageCache\Facades\PageCache;

class Refresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:refresh {--url=*} {--prefix=*} {--regex=*} {--with-linking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the cached version for the URL, and any URLs that link to it.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $refreshed = Collection::make([]);

        if (!empty($this->option('url'))) {
            $refreshed = $refreshed->merge(
                PageCache::refreshByUrls($this->option('url'), $this->option('with-linking'))
            );
        }
        else if (!empty($this->option('prefix'))) {
            $refreshed = $refreshed->merge(
                PageCache::refreshByUrlPrefix($this->option('prefix'), $this->option('with-linking'))
            );
        }
        else if (!empty($this->option('regex'))) {
            $refreshed = $refreshed->merge(
                PageCache::refreshByUrlRegex($this->option('regex'), $this->option('with-linking'))
            );
        }
        else {
            $refreshed = $refreshed->merge(
                PageCache::refreshAll()
            );
        }

        $this->info('Pages Refreshed: ' . $refreshed->count());
        $refreshed->each(fn($url, $file) => $this->info($url));
    }

}