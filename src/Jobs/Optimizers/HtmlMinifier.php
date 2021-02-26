<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use Symfony\Component\Process\Process;

class HtmlMinifier extends BaseOptimizer
{
    public function handle()
    {
        $process = new Process([
            $this->config['command'],
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
