<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Commands\InstallApache;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Commands\Refresh;
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
                Refresh::class,
            ]);
        }

        $this->app->singleton(PageCache::class, function () {
            return new PageCache(config('page-cache.filesystem', 'page-cache'));
        });

        // Create a dynamic filesystem called page-cache
        Config::set('filesystems.disks.page-cache', array(
            'driver' => 'local',
            'root' => storage_path('app/public/page-cache'),
        ));
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/page-cache.php' => config_path('page-cache.php'),
        ]);

        Route::aliasMiddleware('page-cache', CacheResponse::class);
        Crawler::aliasObserver('page-cache', PageCacheCrawlObserver::class);

        Collection::macro('toFileUrlMapping', function(){
            return $this->mapWithKeys(
                fn($f) => [$f => CacheHelpers::cachePathToUrl($f)]
            );
        });
    }
}
