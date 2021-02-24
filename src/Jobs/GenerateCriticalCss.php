<?php

namespace SiteOrigin\PageCache\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\Page;
use Symfony\Component\Process\Process;

class GenerateCriticalCss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Page $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

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

    protected function getFilename(): string
    {
        return Str::replaceLast('html', 'min.css', $this->page->getFilename());
    }
}
