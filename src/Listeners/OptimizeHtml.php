<?php

namespace SiteOrigin\PageCache\Listeners;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\Events\PageRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use SiteOrigin\PageCache\Page;

class OptimizeHtml implements ShouldQueue
{
    public function handle(PageRefreshed $event)
    {
        $page = $event->getPage();

        if ($this->hasOptimizers()) {
            // Create a temporary file with the original filename
            $filename = tempnam('', 'page-cache');
            file_put_contents($filename, $page->getFileContents());

            // Run each of the optimizations on the file
            Bus::chain($this->getOptimizers($filename))->onQueue('sync')->dispatch();

            // Store the optimized file if it exists
            $optimized = file_get_contents($filename);

            if($optimized) {
                $page->putFileContents($optimized, 'min');
            }

            // Clean up the temp file
            unlink($filename);
        }
    }

    protected function getOptimizers($filename): array
    {
        return collect(config('page-cache.optimizers', []))
            ->map(function($optimizer, $key) use ($filename){
                return $optimizer['enabled'] ? app($optimizer['class'], [
                    'filename' => $filename,
                    'config' => $optimizer
                ]) : null;
            })
            ->filter()
            ->toArray();
    }

    protected function hasOptimizers(): bool
    {
        $optimizers = config('page-cache.optimizers', []);
        return is_array($optimizers) && count($optimizers);
    }
}
