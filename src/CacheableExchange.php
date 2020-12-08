<?php

namespace SiteOrigin\PageCache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CacheableExchange
{
    const INDEX_ALIAS = '__pc_index';

    /**
     * @var \Illuminate\Http\Request
     */
    public Request $request;

    /**
     * @var \Illuminate\Http\Response
     */
    public Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function getCacheDirectoryAndFilename(): array
    {
        $path = pathinfo($this->aliasedUri().'.html');

        return [
            ltrim($path['dirname'], '/'),
            $path['filename'] . '.' . $this->guessFileExtension()
        ];
    }

    public function getCachePath(): string
    {
        return ltrim(join('/', $this->getCacheDirectoryAndFilename()), '/');
    }

    /**
     * Alias the filename if necessary.
     *
     * @return string
     */
    protected function aliasedUri(): string
    {
        // Handle the index
        $uri = $this->request->getRequestUri();
        if ($uri == '/') $uri = '/' . self::INDEX_ALIAS;
        else if (substr($uri, 0, 2) == '/?') $uri = str_replace('/?', '/' . self::INDEX_ALIAS . '?', $uri);

        // We'll add a fake query string for consistency
        if ( strpos($uri, '?') === false ) $uri .= '?';
        $uri = str_replace('?', '__', $uri);

        return $uri;
    }

    /**
     * @return bool Should we cache this exchange.
     */
    public function shouldCache()
    {
        // First check if the request is valid
        if ($this->request->getQueryString()) {
            // Reject any requests that have a query string that doesn't match our patterns
            $matches = collect(config('page-cache.query_patterns'))
                ->map(fn($pattern) => preg_match($pattern, $this->request->getRequestUri()))
                ->sum();

            if (!$matches) return false;
        }

        // Now check if the response is valid
        return $this->request->isMethod('GET') && $this->response->getStatusCode() == 200;
    }

    /**
     * Guess the file extension.
     *
     * @return string
     */
    protected function guessFileExtension()
    {
        return $this->response instanceof JsonResponse ? 'json' : 'html';
    }

    public function writeCacheIfNeeded(Filesystem $filesystem)
    {
        if($this->shouldCache() && $this->hasChanged()) {
            $this->writeCache($filesystem);
        }
    }

    public function writeCache(Filesystem $filesystem)
    {
        [$path, $file] = $this->getCacheDirectoryAndFilename();

        $filesystem->makeDirectory($path, 0775, true, true);

        $filesystem->put(
            $this->join([$path, $file]),
            $this->response->getContent(),
            true
        );
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getContent(): string
    {
        return $this->response->getContent();
    }
}