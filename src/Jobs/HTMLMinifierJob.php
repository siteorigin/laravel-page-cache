<?php

namespace SiteOrigin\PageCache\Jobs;

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
        $process->setInput($this->getFileContents());
        $process->run();

        if ($process->isSuccessful()) {
            $this->putFileContents($process->getOutput());
        }
    }
}
