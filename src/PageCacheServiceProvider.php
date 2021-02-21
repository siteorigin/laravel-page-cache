<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\PageCache\Commands\InstallApache;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Commands\InstallNginx;
use SiteOrigin\PageCache\Commands\RefreshCache;
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
                RefreshCache::class,
                InstallApache::class,
                InstallNginx::class,
            ]);
        }

        $this->app->bind(PageCollection::class, function(){
            return new PageCollection(null, config('page-cache.filesystem', 'page-cache'));
        });

        // Create a dynamic filesystem called page-cache if one doesn't already exist
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
        ], 'config');

        Route::aliasMiddleware('page-cache', CacheResponse::class);

        // Use view composers to make sure we don't store 404 pages
        View::composer('errors::404', function($view){
            $page = Page::fromUrl(Request::path());
            if ($page->fileExists()){
                $page->deleteFile();
            }
        });

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'page-cache');
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/assets'),
        ], 'assets');
    }
}
