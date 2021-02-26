<?php

namespace SiteOrigin\PageCache\Listeners;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\Events\PageRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use SiteOrigin\PageCache\Jobs\SyncOriginalPageContentJob;
use SiteOrigin\PageCache\Page;

class OptimizeHtml implements ShouldQueue
{
    public function handle(PageRefreshed $event)
    {
        $page = $event->getPage();

        if ($this->hasOptimizers()) {
            $tempFilename = $this->getTemporaryFile($page);

            Bus::chain([
                ...$this->getOptimizers($tempFilename),
                new SyncOriginalPageContentJob($page, $tempFilename)
            ])->dispatch();
        }
    }

    protected function getOptimizers($tempFilename): array
    {
        return collect(config('page-cache.optimizers', []))
            ->map(fn($optimizerClassName) => app($optimizerClassName, ['filename' => $tempFilename]))
            ->toArray();
    }

    protected function hasOptimizers(): bool
    {
        $optimizers = config('page-cache.optimizers', []);
        return is_array($optimizers) && count($optimizers);
    }

    protected function getTemporaryFile(Page $page): ?string
    {
        $filename = 'page-cache-'.Str::random(32).'.html';
        if (Storage::put($filename, $page->getFileContents())) {
            return $filename;
        }
        return null;
    }
}
