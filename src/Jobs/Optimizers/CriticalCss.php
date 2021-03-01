<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use DOMDocument;
use Symfony\Component\Process\Process;

class CriticalCss extends BaseOptimizer
{
    protected DOMDocument $dom;

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
            // Load the DomDocument
            $this->dom = new DOMDocument();
            @$this->dom->loadHTML($this->getFileContents());

            // Run all the processing steps
            $this->injectCriticalCss($process->getOutput())
                ->deferNonCriticalCss()
                ->saveDom($this->filename);
        }
    }

    /**
     * Inject the body specific CSS at the top of the body tag.
     *
     * @param $css
     * @return $this
     */
    protected function injectCriticalCss($css): CriticalCss
    {
        $style = $this->dom->createElement('style', $css);
        $style->setAttribute('id', 'critical-' . rand(0, getrandmax()));

        $body = $this->dom->getElementsByTagName('body')->item(0);
        $body->insertBefore($style, $body->firstChild);

        return $this;
    }

    /**
     * Defer all non critical CSS marked with `data-critical-defer`.
     *
     * @return $this
     */
    protected function deferNonCriticalCss(): CriticalCss
    {
        foreach($this->dom->getElementsByTagName('link') as $link) {
            if($link->getAttribute('rel') == 'stylesheet' && $link->hasAttribute('data-critical-defer')) {
                $html = $this->dom->saveHTML($link);
                $html = str_replace('data-critical-defer', '', $html);
                $noscript = $this->dom->createElement('noscript', $html);

                $link->parentNode->insertBefore($noscript, $link);

                // Everything we need to defer this stylesheet
                $link->setAttribute('rel', 'preload');
                $link->setAttribute('as', 'style');
                $link->setAttribute('onload', "this.onload=null;this.rel='stylesheet'");
            }
        }
        return $this;
    }

    /**
     * @param string $filename The file to save the HTML to.
     * @return $this
     */
    protected function saveDom(string $filename): CriticalCss
    {
        $this->dom->saveHTMLFile($filename);
        return $this;
    }
}
