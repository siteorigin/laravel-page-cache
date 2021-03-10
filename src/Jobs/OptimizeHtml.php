<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use SiteOrigin\PageCache\Events\PageOptimized;
use SiteOrigin\PageCache\Events\PageOptimizing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use SiteOrigin\PageCache\Page;

class OptimizeHtml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        if ($this->page->getFileExtension() == 'html' && $this->hasOptimizers()) {
            // Create a temporary file with the original filename
            $filename = tempnam('', 'page-cache');
            file_put_contents($filename, $this->page->getFileContents());

            PageOptimizing::dispatch($this->page, $filename);

            // Run each of the optimizations on the file
            $this->getOptimizers($filename)->each(fn($optimizer) => $optimizer->handle());

            // Store the optimized file if it exists
            $optimized = file_get_contents($filename);

            if($optimized) {
                $this->page->putFileContents($optimized, 'min');
            }

            // Clean up the temp file
            unlink($filename);

            PageOptimized::dispatch($this->page, $filename);
        }
    }

    /**
     * @param $filename
     * @return \Illuminate\Support\Collection
     */
    public function getOptimizers($filename): Collection
    {
        return collect(config('page-cache.optimizers', []))
            ->map(function($optimizer, $key) use ($filename){
                return !empty($optimizer['enabled']) ? app($optimizer['class'], [
                    'filename' => $filename,
                    'config' => $optimizer
                ]) : null;
            })
            ->filter();
    }

    public function hasOptimizers(): bool
    {
        $optimizers = config('page-cache.optimizers', []);
        return is_array($optimizers) && count($optimizers);
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return $this->page->url;
    }
}
