<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use DOMDocument;
use DOMElement;
use Symfony\Component\Process\Process;

class CriticalCss extends BaseOptimizer
{
    protected DOMDocument $dom;

    public function handle()
    {
        // Load the DomDocument
        $this->dom = new DOMDocument();
        @$this->dom->loadHTML($this->getFileContents());

        $links = [];
        foreach($this->dom->getElementsByTagName('link') as $link) {
            if ($link->getAttribute('rel') == 'stylesheet' && $link->hasAttribute('data-critical-defer')) {
                $links[] = $link;
            }
        }

        if($links) {
            $css = $this->getCriticalCss($links);

            if($css) {
                // Add the critical CSS before the first link
                $this->injectCriticalCss($css, $links[0]);
                $this->deferNonCriticalCss();
            }

            $this->saveDom($this->getFilename());
        }
    }

    protected function getCriticalCss($links): string
    {
        $command = [$this->config['command']];
        foreach($links as $link) {
            $href = $link->getAttribute('href');
            if ($path = $this->getCssFilePath($href)) {
                $command[] = '--css='.$path;
            }
        }
        $command[] = '--minify';

        $process = new Process($command);
        $process->setInput($this->getFileContents());
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }

    protected function getCssFilePath($href): ?string
    {
        $url = parse_url($href);

        if (! isset($url['path'])) {
            return null;
        }

        return public_path($url['path']);
    }

    /**
     * Inject the body specific CSS at the top of the body tag.
     *
     * @param string $css
     * @param DOMElement $before
     * @return $this
     */
    protected function injectCriticalCss(string $css, DOMElement $before): CriticalCss
    {
        // Create the critical style
        $style = $this->dom->createElement('style', $css);
        $style->setAttribute('id', 'critical-css-' . md5($css));

        $body = $this->dom->getElementsByTagName('head')->item(0);
        $body->insertBefore($style, $before);

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
                $noscript = $this->dom->createElement('noscript');
                $noscript->appendChild($this->dom->createCDATASection($html));

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
