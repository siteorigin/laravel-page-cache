<?php

namespace SiteOrigin\PageCache\Middleware;

use Closure;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\CacheableExchange;
use SiteOrigin\PageCache\Events\CachedPageChanged;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SiteOrigin\PageCache\Manager;

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
     * @var \SiteOrigin\PageCache\Manager
     */
    protected Manager $cache;

    /**
     * @param \SiteOrigin\PageCache\Manager $cache
     */
    public function __construct(Manager $cache)
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

        if(
            $exchange->shouldCache() &&
            $exchange->hasChanged($this->cache->getFilesystem())
        ) {
            CachedPageChanged::dispatch($exchange);
            $exchange->write($this->cache->getFilesystem());
        }

        return $response;
    }

}
