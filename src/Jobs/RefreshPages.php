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

    public function __construct(PageCollection $pages, $withLinking = false)
    {
        // We can't serialize a PageCollection, so just store URLs
        $this->pages = $pages->pluck('url')->all();
        $this->withLinking = $withLinking;
    }

    public function handle()
    {
        $pages = new PageCollection();

        $refreshed = $pages->filterPageUrlIs($this->pages)->requestPages();

        if($this->withLinking){
            $pages->filterPageLinksTo($refreshed)->requestPages();
        }
    }
}