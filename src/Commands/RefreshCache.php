<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Console\Command;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\PageCollection;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh all known pages.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        $pages = new PageCollection();
        $changed = 0;
        $this->withProgressBar($pages, function(Page $page) use (&$changed){
            if ($page->requestPage()) $changed++;
        });
        $this->info('Pages refreshed: ' .  $changed);
    }

}