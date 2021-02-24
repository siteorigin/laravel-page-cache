<?php

namespace SiteOrigin\PageCache\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SiteOrigin\PageCache\Events\PageRefreshed;
use SiteOrigin\PageCache\Listeners\HandlePageRefreshed;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PageRefreshed::class => [
            HandlePageRefreshed::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
