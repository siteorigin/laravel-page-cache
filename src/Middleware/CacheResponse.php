<?php

namespace SiteOrigin\PageCache\Middleware;

use Closure;
use SiteOrigin\PageCache\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @var \SiteOrigin\PageCache\Cache
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
     * @var \SiteOrigin\PageCache\Cache  $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Handle other middleware first.
        $response = $next($request);

        if ($this->shouldCache($request, $response)) {
            $this->cache->cache($request, $response);
        }

        return $response;
    }

    /**
     * Determines whether the given request/response pair should be cached.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return bool
     */
    protected function shouldCache(Request $request, Response $response)
    {
        if ($request->getQueryString()) {
            $matches = collect($this->queryStringCachePatterns)
                ->merge(config('pagecache.query_patterns'))
                ->map(fn($pattern) => preg_match($pattern, $request->getRequestUri()))
                ->sum();

            if (!$matches) return false;
        }

        return $request->isMethod('GET') && $response->getStatusCode() == 200;
    }
}
