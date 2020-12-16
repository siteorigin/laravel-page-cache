<?php

namespace SiteOrigin\PageCache\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SiteOrigin\PageCache\Facades\PageCache;

use SiteOrigin\PageCache\Condition\UrlDirect;
use SiteOrigin\PageCache\Condition\UrlPrefix;
use SiteOrigin\PageCache\Condition\UrlRegex;
use SiteOrigin\PageCache\Jobs\RefreshFiles;

class RefreshCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page-cache:refresh {--url=*} {--prefix=*} {--regex=*} {--with-linking} {--dispatch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the cached version for the URL, and any URLs that link to it.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        $conditions = [];
        $conditions = array_merge($conditions, UrlDirect::fromStringArray($this->option('url')));
        $conditions = array_merge($conditions, UrlPrefix::fromStringArray($this->option('prefix')));
        $conditions = array_merge($conditions, UrlRegex::fromStringArray($this->option('regex')));

        if(!$this->option('dispatch')) {
            $refreshed = PageCache::refresh($conditions, $this->option('with-linking'));
            $this->info('Pages Refreshed: '.$refreshed->count());
            $refreshed->each(fn($url, $file) => $this->info($url));
        }
        else {
            RefreshFiles::dispatch($conditions, $this->option('with-linking'));
            $this->info('Refresh dispatched to job queue.');
        }
    }

}