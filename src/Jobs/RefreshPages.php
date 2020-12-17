<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\Facades\PageCache;

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

    public function __construct(array $conditions, $withLinking = false)
    {
        $this->conditions = $conditions;
        $this->withLinking = $withLinking;
    }

    public function handle()
    {
        // Just pass everything directly to the PageCache refresh function.
        PageCache::refresh($this->conditions, $this->withLinking);
    }
}