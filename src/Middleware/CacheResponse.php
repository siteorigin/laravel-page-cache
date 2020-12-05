<?php

namespace SiteOrigin\PageCache\Middleware;

use Closure;
use SiteOrigin\PageCache\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        // TODO only cache with query strings if a pattern is matched
        return $request->isMethod('GET') && $response->getStatusCode() == 200;
    }
}
