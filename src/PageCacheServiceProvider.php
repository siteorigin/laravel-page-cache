<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\ServiceProvider;
use SiteOrigin\PageCache\Console\ClearCache;
use SiteOrigin\PageCache\Console\Touch;

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
            Touch::class
        ]);

        $this->app->singleton(Cache::class, function () {
            $instance = new Cache($this->app->make('files'));

            return $instance->setContainer($this->app);
        });
    }
}
