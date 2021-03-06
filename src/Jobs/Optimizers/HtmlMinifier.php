<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use Symfony\Component\Process\Process;

class HtmlMinifier extends BaseOptimizer
{
    public function handle()
    {
        $process = new Process([
            $this->config['command'],
            '-c '. realpath(__DIR__.'/../../../html-minifier.json')
        ]);

        $process->setInput($this->getFileContents());
        $process->run();

        if ($process->isSuccessful()) {
            $this->putFileContents($process->getOutput());
        }
    }
}
