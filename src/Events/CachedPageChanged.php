<?php

namespace SiteOrigin\PageCache\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\PageCache;

class CachedPageChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var \SiteOrigin\PageCache\PageCache
     */
    private PageCache $cache;

    /**
     * @var \Illuminate\Http\Request
     */
    private Request $request;

    /**
     * @var \Illuminate\Http\Response
     */
    private Response $response;

    /**
     * Create a new event instance.
     *
     * @param \SiteOrigin\PageCache\PageCache $cache
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     */
    public function __construct(PageCache $cache, Request $request, Response $response)
    {
        $this->cache = $cache;
        $this->request = $request;
        $this->response = $response;
    }

    public function getPageCache(): PageCache
    {
        return $this->cache;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}