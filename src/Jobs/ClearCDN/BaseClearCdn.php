<?php

namespace SiteOrigin\PageCache\Jobs\ClearCDN;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SiteOrigin\PageCache\Page;

abstract class BaseClearCdn implements ClearCdn, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $conditions;

    protected bool $withLinking;

    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    abstract public function handle();
}
