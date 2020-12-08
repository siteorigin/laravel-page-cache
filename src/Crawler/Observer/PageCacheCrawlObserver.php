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
        if($exchange->shouldCache()) {
            if(PageCache::hasChanged($exchange)) {
                CachedPageChanged::dispatch($exchange);
                PageCache::write($exchange);
            }
        }
    }
}