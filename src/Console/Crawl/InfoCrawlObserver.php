<?php

namespace SiteOrigin\PageCache\Console\Crawl;

use Illuminate\Support\Facades\Artisan;
use Spatie\Crawler\CrawlObservers\CrawlObserver as CrawlObserverBase;

class InfoCrawlObserver extends CrawlObserverBase
{
    private \Illuminate\Console\Command $command;

    public function __construct(\Illuminate\Console\Command $command)
    {
        $this->command = $command;
    }

    public function crawled(
        \Psr\Http\Message\UriInterface $url,
        \Psr\Http\Message\ResponseInterface $response,
        ?\Psr\Http\Message\UriInterface $foundOnUrl = null
    ) {
        $this->command->info(
            'Crawled: ' . $url->getPath() .
            ( $url->getQuery() ? '?' . $url->getQuery() : '' )
        );
    }

    public function crawlFailed(
        \Psr\Http\Message\UriInterface $url,
        \GuzzleHttp\Exception\RequestException $requestException,
        ?\Psr\Http\Message\UriInterface $foundOnUrl = null
    ) {
    }
}