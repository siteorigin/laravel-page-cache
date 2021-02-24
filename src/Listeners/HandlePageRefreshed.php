<?php

namespace SiteOrigin\PageCache\Listeners;

use SiteOrigin\PageCache\Events\PageRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use SiteOrigin\PageCache\Jobs\GenerateCriticalCss;
use SiteOrigin\PageCache\Jobs\HTMLMinifier;

class HandlePageRefreshed implements ShouldQueue
{
    public function handle(PageRefreshed $event)
    {
        $page = $event->getPage();

        Bus::chain([
            new GenerateCriticalCss($page),
            new HTMLMinifier($page),
        ])->dispatch();
    }
}
