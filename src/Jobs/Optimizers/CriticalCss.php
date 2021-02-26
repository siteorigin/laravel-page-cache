<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use Symfony\Component\Process\Process;

class CriticalCss extends BaseOptimizer
{
    public function handle()
    {
        $contents = $this->getFileContents();

        $process = new Process([
            $this->config['command'],
            '--css='.$this->config['css'],
            '--minify'
        ]);

        $process->setInput($contents);
        $process->run();

        if ($process->isSuccessful()) {
            $this->putFileContents(
                $this->injectCriticalCss($process->getOutput())
            );
        }
    }

    protected function injectCriticalCss($css)
    {
        $contents = $this->getFileContents();

        return str_replace('</head>', "<style>{$css}</style>".PHP_EOL."</head>", $contents);
    }
}
