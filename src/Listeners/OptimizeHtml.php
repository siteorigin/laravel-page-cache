<?php

namespace SiteOrigin\PageCache\Listeners;

use SiteOrigin\PageCache\Events\PageRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use SiteOrigin\PageCache\Page;

class OptimizeHtml implements ShouldQueue
{
    public function handle(PageRefreshed $event)
    {
        $page = $event->getPage();
        $optimizers = $this->getOptimizers($page);

        if (count($optimizers)) {
            Bus::chain($optimizers)->dispatch();
        }
    }

    public static function getOptimizers(Page $page): array
    {
        return collect(config('page-cache.optimizers', []))
            ->map(fn($optimizerClassName) => app($optimizerClassName, ['page' => $page]))
            ->toArray();
    }
}
