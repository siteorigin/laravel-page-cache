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
    private Page $page;

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

    public function getExchange(): Exchange
    {
        return $this->exchange;
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}