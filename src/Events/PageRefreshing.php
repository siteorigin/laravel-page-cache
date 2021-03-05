<?php

namespace SiteOrigin\PageCache\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\Exchange;
use SiteOrigin\PageCache\Page;

class PageRefreshing
{
    use Dispatchable, SerializesModels;

    /**
     * @var \SiteOrigin\PageCache\Exchange
     */
    private Exchange $exchange;

    /**
     * @var \SiteOrigin\PageCache\Page
     */
    protected Page $page;

    /**
     * Create a new event instance.
     *
     * @param \SiteOrigin\PageCache\Exchange $exchange
     * @param \SiteOrigin\PageCache\Page $page
     */
    public function __construct(Exchange $exchange, Page $page)
    {
        $this->exchange = $exchange;
        $this->page = $page;
    }

    /**
     * Get the exchange. Will only work if the event is handled asynchronously.
     *
     * @return \SiteOrigin\PageCache\Exchange|null The current Exchange or null if handled asynchronously.
     */
    public function getExchange(): Exchange
    {
        return $this->exchange;
    }

    /**
     * @return \SiteOrigin\PageCache\Page The page being refreshed.
     */
    public function getPage(): Page
    {
        return $this->page;
    }
}
