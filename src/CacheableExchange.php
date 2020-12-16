<?php

namespace SiteOrigin\PageCache;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Class CacheableExchange A Request/Response pair
 *
 * @package SiteOrigin\PageCache
 */
class CacheableExchange
{
    /**
     * @var \Illuminate\Http\Request
     */
    public Request $request;

    /**
     * @var \Illuminate\Http\Response
     */
    public Response $response;

    /**
     * CacheableExchange constructor.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function cachePath(): string
    {
        Return Page::urlToFilename($this->request->getRequestUri(), $this->guessFileExtension());
    }

    /**
     * Check that the combination of request and response is cacheable.
     *
     * @return bool
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
        return $this->request->isMethod('GET') &&
            $this->response->getStatusCode() == 200 &&
            ! array_intersect(
                ['no-cache', 'private'],
                array_map('trim', explode(',', $this->response->headers->get('cache-control')))
            );
    }

    /**
     * Check if the response has changed in the given filesystem.
     *
     * @param \Illuminate\Filesystem\FilesystemAdapter $filesystem
     * @return bool
     */
    public function hasChanged(FilesystemAdapter $filesystem): bool
    {
        $path = $this->cachePath();
        return (
            ! $filesystem->exists($path) ||
            md5_file($filesystem->path($path)) !== md5($this->getContent())
        );
    }

    /**
     * Guess the file extension.
     *
     * @return string
     */
    protected function guessFileExtension(): string
    {
        return $this->response instanceof JsonResponse ? 'json' : 'html';
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getContent(): string
    {
        return $this->response->getContent();
    }

    public function write(FilesystemAdapter $filesystem)
    {
        return $filesystem->put(
            $this->cachePath(),
            $this->getContent()
        );
    }
}