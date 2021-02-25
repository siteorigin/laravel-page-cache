<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class HTMLMinifierJob extends OptimizeHtmlJob
{
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
}
