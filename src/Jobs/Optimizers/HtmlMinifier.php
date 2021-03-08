<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use Symfony\Component\Process\Process;

class HtmlMinifier extends BaseOptimizer
{
    static array $defaultConfig = [
        "collapseWhitespace" => true,
        "removeOptionalTags" => true,
        "removeRedundantAttributes" => true,
        "removeTagWhitespace" => true,
        "html5" => true,
        "removeAttributeQuotes" => false,
        "removeComments" => true,
        "removeEmptyAttributes" => false,
        "removeEmptyElements" => false
    ];

    public function handle()
    {
        $config = tmpfile();
        fwrite($config, json_encode($this->getMinifierConfig()));
        $process = new Process([
            $this->config['command'],
            '--config-file='. stream_get_meta_data($config)['uri']
        ]);

        $process->setInput($this->getFileContents());
        $process->run();

        if ($process->isSuccessful()) {
            $this->putFileContents($process->getOutput());
        }

        fclose($config);
    }

    protected function getMinifierConfig()
    {
        return $this->config['config'] ?? static::$defaultConfig;
    }
}
