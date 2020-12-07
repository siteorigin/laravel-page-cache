<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Support\Facades\Artisan;
use SiteOrigin\PageCache\PageCache;
use Illuminate\Console\Command;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:clear {path? : URL path of page/directory to delete} {--recursive} {--touch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear (all or part of) the page cache.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $cache = app(PageCache::class);
        $recursive = $this->option('recursive');
        $path = $this->argument('path');

        if (!$path) {
            $this->clear($cache);
        } else if ($recursive) {
            $this->clear($cache, $path);
        } else {
            $this->forget($cache, $path);
        }

        if ($this->option('touch')) {
            $this->call('crawler:run');
        }
    }

    /**
     * Remove the cached file for the given path.
     *
     * @param  \SiteOrigin\PageCache\PageCache  $cache
     * @param  string  $path
     * @return void
     */
    public function forget(PageCache $cache, $path)
    {
        if ($cache->forget($path)) {
            $this->info("Page cache cleared for \"{$path}\"");
        } else {
            $this->info("No page cache found for \"{$path}\"");
        }
    }

    /**
     * Clear the full page cache.
     *
     * @param  \SiteOrigin\PageCache\PageCache  $cache
     * @param  string|null  $path
     * @return void
     */
    public function clear(PageCache $cache, $path = null)
    {
        if ($cache->clear($path)) {
            $this->info('Page cache cleared at '.$cache->getCachePath($path));
        } else {
            $this->warn('Page cache not cleared at '.$cache->getCachePath($path));
        }
    }
}
