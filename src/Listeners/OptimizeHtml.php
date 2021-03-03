<?php

namespace SiteOrigin\PageCache\Listeners;

use Illuminate\Support\Collection;
use SiteOrigin\PageCache\Events\PageRefreshed;
use Illuminate\Contracts\Queue\ShouldQueue;
use SiteOrigin\PageCache\Jobs\ClearCdn;

class OptimizeHtml implements ShouldQueue
{
    public function handle(PageRefreshed $event)
    {
        $page = $event->getPage();

        if ($page->getFileExtension() == 'html' && $this->hasOptimizers()) {
            // Create a temporary file with the original filename
            $filename = tempnam('', 'page-cache');
            file_put_contents($filename, $page->getFileContents());

            // Run each of the optimizations on the file
            $this->getOptimizers($filename)->each(fn($optimizer) => $optimizer->handle());

            // Store the optimized file if it exists
            $optimized = file_get_contents($filename);

            if($optimized) {
                $page->putFileContents($optimized, 'min');
            }

            // Clean up the temp file
            unlink($filename);

            // TODO check if we have a CDN config before dispatching this job
            ClearCdn::dispatch($page);
        }
    }

    /**
     * @param $filename
     * @return \Illuminate\Support\Collection
     */
    protected function getOptimizers($filename): Collection
    {
        return collect(config('page-cache.optimizers', []))
            ->map(function($optimizer, $key) use ($filename){
                return $optimizer['enabled'] ? app($optimizer['class'], [
                    'filename' => $filename,
                    'config' => $optimizer
                ]) : null;
            })
            ->filter();
    }

    protected function hasOptimizers(): bool
    {
        $optimizers = config('page-cache.optimizers', []);
        return is_array($optimizers) && count($optimizers);
    }
}
