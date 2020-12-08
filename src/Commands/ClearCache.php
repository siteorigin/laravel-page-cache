<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Command;
use SiteOrigin\PageCache\Facades\PageCache;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:clear {--touch}';

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
        PageCache::clear();

        if ($this->option('touch')) {
            Artisan::call('crawler:start --observer=page-cache', [], $this->output);
        }
    }
}
