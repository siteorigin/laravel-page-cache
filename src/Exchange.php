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
class Exchange
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
    public function shouldCache(): bool
    {
        // First check if the request is valid
        if ($this->request->getQueryString()) {
            // Reject any requests that have a query string that doesn't match our patterns
            $patterns = collect(config('page-cache.query_patterns'));
            // Accept all pagination query strings.
            if(config('page-cache.cache_pagination')) $patterns->push('/.*\?page=[0-9]+$/');

            // Make sure there's a match here.
            $matches = $patterns->map(fn($pattern) => preg_match($pattern, $this->request->getRequestUri()))->sum();

            if (!$matches) return false;
        }

        // Now check if the response is valid
        return $this->request->isMethod('GET') &&
            $this->response->getStatusCode() == 200;
    }

    /**
     * Check if the response has changed in the given filesystem.
     *
     * @param \SiteOrigin\PageCache\Page|null $page
     * @return bool
     */
    public function hasChanged(?Page $page = null): bool
    {
        if(is_null($page)) $page = $this->getPage();
        return ( !$page->fileExists() || $page->getFileMd5() !== md5($this->getContent()) );
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

    public function getPage(): Page
    {
        return Page::fromUrl($this->request->getRequestUri());
    }
}