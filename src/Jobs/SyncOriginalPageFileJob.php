<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use SiteOrigin\PageCache\Page;

class SyncOriginalPageFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Page $page;

    private string $tempFilename;

    public function __construct(Page $page, $tempFilename)
    {
        $this->page = $page;
        $this->tempFilename = $tempFilename;
    }

    public function handle()
    {
        if ($content = $this->getTemporaryFileContent()) {
            $this->page->putFileContents($content);
        }

        $this->deleteTemporaryFile();
    }

    protected function getTemporaryFileContent(): string
    {
        return Storage::get($this->tempFilename);
    }

    protected function deleteTemporaryFile(): bool
    {
        return Storage::delete($this->tempFilename);
    }
}
