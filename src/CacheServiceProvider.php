<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Commands\InstallApache;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Commands\InstallNginx;
use SiteOrigin\PageCache\Commands\RefreshCache;
use SiteOrigin\PageCache\Crawler\Observer\PageCacheCrawlObserver;
use SiteOrigin\PageCache\Middleware\CacheResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $this->app->bind(PageCollection::class, function(){
            return new PageCollection(null, config('page-cache.filesystem', 'page-cache'));
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

        // Use view composers to make sure we don't store 404 pages
        View::composer('errors::404', function($view){
            $page = Page::fromUrl(Request::path());
            if($page->fileExists()){
                $page->deleteFile();
            }
        });
    }
}
