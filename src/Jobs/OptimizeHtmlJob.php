<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

abstract class OptimizeHtmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    abstract public function handle();

    protected function getFilename(): string
    {
        return $this->filename;
    }

    protected function getFileContents(): string
    {
        return Storage::get($this->filename);
    }

    public function putFileContents(string $contents): bool
    {
        return Storage::put($this->filename, $contents);
    }
}
