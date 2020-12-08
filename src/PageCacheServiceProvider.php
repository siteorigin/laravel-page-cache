<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Commands\InstallApache;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Crawler\Observer\PageCacheCrawlObserver;
use SiteOrigin\PageCache\Middleware\CacheResponse;

class PageCacheServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearCache::class,
                InstallApache::class,
            ]);
        }

        $this->app->singleton(PageCache::class, function () {
            return new PageCache(config('page-cache.filesystem', 'public'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/page-cache.php' => config_path('page-cache.php'),
        ]);

        Route::aliasMiddleware('cache.page', CacheResponse::class);
        Crawler::aliasObserver('page-cache', PageCacheCrawlObserver::class);

    }
}
