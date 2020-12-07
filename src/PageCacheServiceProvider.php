<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SiteOrigin\PageCache\Commands\Install;
use SiteOrigin\PageCache\Commands\ClearCache;
use SiteOrigin\PageCache\Events\CachedPageChanged;
use SiteOrigin\PageCache\Listeners\TestListener;

class PageCacheServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            ClearCache::class,
            Install::class,
        ]);

        $this->app->singleton(PageCache::class, function () {
            $instance = new PageCache($this->app->make('files'));
            return $instance->setContainer($this->app);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/page-cache.php' => config_path('page-cache.php'),
        ]);
    }
}
