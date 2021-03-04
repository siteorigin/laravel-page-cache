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
     * @var \SiteOrigin\PageCache\Page
     */
    protected Page $page;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * Create a new event instance.
     *
     * @param string $filename
     * @param \SiteOrigin\PageCache\Page $page
     */
    public function __construct(Page $page, string $filename)
    {
        $this->filename = $filename;
        $this->page = $page;
    }

    /**
     * @return \SiteOrigin\PageCache\Page The page object being optimized.
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @return string The temporary filename being optimized.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
