<?php

namespace SiteOrigin\PageCache\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\Exchange;
use SiteOrigin\PageCache\Page;

class PageOptimizing
{
    use Dispatchable, SerializesModels;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var \SiteOrigin\PageCache\Page
     */
    protected Page $page;

    /**
     * Create a new event instance.
     *
     * @param string $filename
     * @param \SiteOrigin\PageCache\Page $page
     */
    public function __construct(string $filename, Page $page)
    {
        $this->filename = $filename;
        $this->page = $page;
    }

    public function getFilename(): Exchange
    {
        return $this->filename;
    }

    public function getPage(): Page
    {
        return $this->page;
    }
}
