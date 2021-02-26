<?php

namespace SiteOrigin\PageCache\Jobs;

use DOMDocument;
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
        $dom = new DOMDocument();
        @$dom->loadHTML($this->getFileContents());

        $style = $dom->createElement('style', $css);

        $head = $dom->getElementsByTagName('head')->item(0);
        $head->appendChild($style);

        return $dom->saveHTML();
    }
}
