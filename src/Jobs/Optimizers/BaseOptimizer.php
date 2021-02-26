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
}