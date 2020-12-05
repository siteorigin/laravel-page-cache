<?php

namespace SiteOrigin\PageCache\Console;

use Illuminate\Console\Command;
use SiteOrigin\PageCache\Console\Crawl\InfoCrawlObserver;
use Spatie\Crawler\Crawler;

class Touch extends Command
{
    protected $signature = "page-cache:touch";
    protected $description = "Crawl our entire site from the home page to trigger caching of each page.";

    public function handle()
    {
        Crawler::create()
            ->setCrawlObserver(new InfoCrawlObserver($this))
            ->setParseableMimeTypes(['text/html', 'text/plain'])
            ->startCrawling(url('/'));
    }
}