<?php

namespace SiteOrigin\PageCache\Middleware;

use Closure;
use SiteOrigin\PageCache\Events\CachedPageChanged;
use SiteOrigin\PageCache\PageCache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $this->cache->cacheIfNeeded($request, $response);
        return $response;
    }

}
