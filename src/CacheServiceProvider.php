<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Commands\InstallApache;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Commands\InstallNginx;
use SiteOrigin\PageCache\Commands\RefreshCache;
use SiteOrigin\PageCache\Crawler\Observer\PageCacheCrawlObserver;
use SiteOrigin\PageCache\Middleware\CacheResponse;

class CacheServiceProvider extends ServiceProvider
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
                RefreshCache::class,
                InstallApache::class,
                InstallNginx::class,
            ]);
        }

        $this->app->singleton(Manager::class, function () {
            return new Manager(config('page-cache.filesystem', 'page-cache'));
        });

        // Create a dynamic filesystem called page-cache
        if(! Config::has('filesystems.disks.page-cache')) {
            Config::set('filesystems.disks.page-cache', [
                'driver' => 'local',
                'root' => storage_path('app/public/page-cache'),
            ]);
        }
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
                fn($f) => [$f => Page::filenameToUrl($f)]
            );
        });
    }
}
