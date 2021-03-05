<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\PageCollection;

class RefreshPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    private array $conditions;

    /**
     * @var false
     */
    private bool $withLinking;

    private array $pages;

    /**
     * RefreshPages constructor.
     *
     * @param \SiteOrigin\PageCache\PageCollection|array|string $pages A page collection, array of URLs or a single URL.
     * @param false $withLinking
     */
    public function __construct($pages, $withLinking = false)
    {
        if(is_array($pages)){
            $this->pages = $pages;
        }
        elseif(is_string($pages)) {
            $this->pages = [$pages];
        }
        elseif($pages instanceof PageCollection) {
            // We can't serialize a PageCollection, so just store URLs
            $this->pages = $pages->pluck('url')->all();
        }

        $this->withLinking = $withLinking;
    }

    public function handle()
    {
        $pages = new PageCollection();

        $refreshed = $pages->filterPageUrlIs($this->pages)->requestedPages()->all();

        if($this->withLinking){
            $pages->filterPageLinksTo($refreshed)->requestPages();
        }
    }
}