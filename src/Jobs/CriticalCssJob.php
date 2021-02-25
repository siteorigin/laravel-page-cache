<?php

namespace SiteOrigin\PageCache\Jobs;

use Symfony\Component\Process\Process;

class CriticalCssJob extends OptimizeHtmlJob
{
    public function handle()
    {
        $contents = $this->page->getFileContents();

        $process = new Process([
            'critical',
            '--css='.public_path('css/app.css'),
            '--minify'
        ]);

        $process->setInput($contents);
        $process->run();

        if ($process->isSuccessful()) {
            $this->page->putFileContents($this->injectCriticalCss($contents, $process->getOutput()));
        }
    }

    protected function injectCriticalCss($contents, $css)
    {
        return str_replace('</head>', "<style>{$css}</style>" . PHP_EOL . "</head>", $contents);
    }
}
