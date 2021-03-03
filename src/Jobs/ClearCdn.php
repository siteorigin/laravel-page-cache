<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\PageCollection;

class ClearCdn implements ShouldQueue
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

    private Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        // TODO clear the CDN based on the current config.
    }
}