<?php

namespace SiteOrigin\PageCache\Condition;

use SiteOrigin\PageCache\CacheHelpers;

class FileContains extends Condition
{
    protected static function filterCondition($condition)
    {
        return CacheHelpers::baseUrl($condition);
    }

    public function __invoke($url, $file): bool
    {
        
    }
}