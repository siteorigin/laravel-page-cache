<?php

namespace SiteOrigin\PageCache\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SiteOrigin\PageCache\Events\PageRefreshed;
use SiteOrigin\PageCache\Listeners\OptimizeHtml;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PageRefreshed::class => [
            OptimizeHtml::class,
        ]
    ];

}
