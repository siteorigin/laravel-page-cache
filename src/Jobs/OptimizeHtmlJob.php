<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\Page;

abstract class OptimizeHtmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    abstract public function handle();

    protected function getFilename(): string
    {
        return Str::replaceLast('html', 'min.html', $this->page->getFilename());
    }
}
