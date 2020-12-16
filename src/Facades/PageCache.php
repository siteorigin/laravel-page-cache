<?php

namespace SiteOrigin\PageCache\Facades;

use Illuminate\Support\Facades\Facade;

class PageCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \SiteOrigin\PageCache\Manager::class;
    }
}