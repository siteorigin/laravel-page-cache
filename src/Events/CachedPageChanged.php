<?php

namespace SiteOrigin\PageCache\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\CacheableExchange;
use SiteOrigin\PageCache\PageCache;

class CachedPageChanged
{
    use Dispatchable, SerializesModels;

    /**
     * @var \SiteOrigin\PageCache\CacheableExchange
     */
    private CacheableExchange $exchange;

    /**
     * Create a new event instance.
     *
     * @param \SiteOrigin\PageCache\CacheableExchange $exchange
     */
    public function __construct(CacheableExchange $exchange)
    {
        $this->exchange = $exchange;
    }

    public function getExchange(): CacheableExchange
    {
        return $this->exchange;
    }
}