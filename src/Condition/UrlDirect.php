<?php

namespace SiteOrigin\PageCache\Condition;

use SiteOrigin\PageCache\CacheHelpers;

class UrlDirect extends Condition
{
    protected static function filterCondition($condition)
    {
        return CacheHelpers::baseUrl($condition);
    }

    public function __invoke($url, $file): bool
    {
        if ( !empty($this->args['ignore_query']) ) {
            $url = preg_replace('/\?.*/', '', $url);
        }

        return $url == CacheHelpers::baseUrl($this->condition);
    }
}