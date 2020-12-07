<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Console\Command;
use SiteOrigin\PageCache\Crawler\InfoCrawlObserver;
use Spatie\Crawler\Crawler;

class Install extends Command
{
    protected $signature = "page-cache:install";
    protected $description = "Install the .htaccess for this file.";

    public function handle()
    {

    }
}