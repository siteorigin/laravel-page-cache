<?php

namespace SiteOrigin\PageCache\Middleware;

use Closure;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\CacheableExchange;
use SiteOrigin\PageCache\Events\CachedPageChanged;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SiteOrigin\PageCache\Facades\PageCache;

/**
 * Cache response middleware. This
 *
 * @package SiteOrigin\PageCache\Middleware
 */
class CacheResponse
{
    /**
     * The cache instance.
     *
     * @var \SiteOrigin\PageCache\PageCache
     */
    protected PageCache $cache;

    /**
     * Constructor.
     *
     * @var \SiteOrigin\PageCache\PageCache  $cache
     */
    public function __construct(PageCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Handle other middleware first.
        $response = $next($request);

        $exchange = new CacheableExchange($request, $response);
        if($exchange->shouldCache()) {
            if(PageCache::hasChanged($exchange)) {
                CachedPageChanged::dispatch($exchange);
                PageCache::write($exchange);
            }
        }

        return $response;
    }

}
