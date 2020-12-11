<?php

namespace SiteOrigin\PageCache\Crawler\Observer;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SiteOrigin\KernelCrawler\Crawler\CrawlUrl;
use SiteOrigin\KernelCrawler\Crawler\Observer\CrawlObserver;
use SiteOrigin\PageCache\CacheableExchange;
use SiteOrigin\PageCache\Events\CachedPageChanged;
use SiteOrigin\PageCache\Facades\PageCache;

class PageCacheCrawlObserver extends CrawlObserver
{
    public function afterRequest(CrawlUrl $url, Request $request, Response $response)
    {
        $exchange = new CacheableExchange($request, $response);
        $fs = PageCache::getFilesystem();

        if(
            $exchange->shouldCache() &&
            $exchange->hasChanged($fs)
        ) {
            CachedPageChanged::dispatch($exchange);
            $exchange->write($fs);
        }
    }
}