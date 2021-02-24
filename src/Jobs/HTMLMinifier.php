<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use SiteOrigin\PageCache\Page;

class HTMLMinifier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    public function handle()
    {
        $process = new Process([
            'html-minifier',
            '--collapse-whitespace',
            '--remove-comments',
            '--remove-optional-tags',
            '--remove-redundant-attributes',
            '--remove-tag-whitespace'
        ]);
        $process->setInput($this->page->getFileContents());
        $process->run();

        if ($process->isSuccessful()) {
            Storage::disk($this->page->getDisk())->put($this->getFilename(), $process->getOutput());
        }
    }

    protected function getFilename(): string
    {
        return Str::replaceLast('html', 'min.html', $this->page->getFilename());
    }
}
