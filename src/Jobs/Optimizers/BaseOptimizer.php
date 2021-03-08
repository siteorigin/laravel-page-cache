<?php

namespace SiteOrigin\PageCache\Jobs\Optimizers;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class BaseOptimizer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filename;

    protected array $config;

    private array $replacedVueAttributes = [];

    public function __construct($filename, $config)
    {
        $this->filename = $filename;
        $this->config = $config;
    }

    abstract public function handle();

    /**
     * @return string The name of the file we're optimizing
     */
    protected function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string The contents of the file that we're optimizing.
     */
    protected function getFileContents(): string
    {
        return file_get_contents($this->filename);
    }

    /**
     * @param string $contents The contents we want to set for the current file.
     * @return bool
     */
    public function putFileContents(string $contents): bool
    {
        return file_put_contents($this->filename, $contents);
    }

    /**
     * Convert Vue Attributes into safe attributes.
     *
     * @param $html
     * @return string|string[]|null
     */
    protected function encodeVueAttributes($html)
    {
        $html = preg_replace_callback('/(@[\w\.]+)=/', function($match){
            $md5 = md5($match[1]);
            $this->replacedVueAttributes[$md5] = $match[1];

            return '_safe_' . $md5 . '=';
        }, $html);

        return $html;
    }

    /**
     * Convert safe attributes from the previous step into
     *
     * @param $html
     * @return mixed|string|string[]
     */
    protected function decodeVueAttributes($html)
    {
        foreach($this->replacedVueAttributes as $md5 => $attr) {
            $html = str_replace('_safe_' . $md5 . '=', $attr . '=', $html);
        }

        return $html;
    }
}