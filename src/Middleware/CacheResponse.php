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
    protected $cache;

    /**
     * Regular expression patterns that validate URLs with query strings.
     *
     * @var array
     */
    protected $queryStringCachePatterns = [];

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

        if ($this->shouldCache($request, $response)) {
            // Try triggering a change event before.
            $this->triggerChangeEvent($request, $response);
            $this->cache->cache($request, $response);
        }

        return $response;
    }

    /**
     * Determines whether the given request/response pair should be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    protected function shouldCache(Request $request, Response $response)
    {
        if ($request->getQueryString()) {
            $matches = collect($this->queryStringCachePatterns)
                ->merge(config('page-cache.query_patterns'))
                ->map(fn($pattern) => preg_match($pattern, $request->getRequestUri()))
                ->sum();

            if (!$matches) return false;
        }

        return $request->isMethod('GET') && $response->getStatusCode() == 200;
    }

    protected function triggerChangeEvent(Request $request, Response $response)
    {
        $path = join('/', $this->cache->getDirectoryAndFilename($request, $response));
        if (file_exists($path) && md5($response->getContent()) != md5_file($path)) {
            CachedPageChanged::dispatch($this->cache, $request, $response);
        }
    }
}
