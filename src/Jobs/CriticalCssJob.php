<?php

namespace SiteOrigin\PageCache\Jobs;

use Symfony\Component\Process\Process;

class CriticalCssJob extends OptimizeHtmlJob
{
    public function handle()
    {
        $contents = $this->getFileContents();

        $process = new Process([
            'critical',
            '--css='.public_path('css/app.css'),
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
