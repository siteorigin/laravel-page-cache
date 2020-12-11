<?php

namespace SiteOrigin\PageCache\Condition;

use Illuminate\Support\Str;
use SiteOrigin\PageCache\CacheHelpers;

class Prefix extends Condition
{
    protected static function filterCondition($condition)
    {
        return CacheHelpers::baseUrl($condition);
    }

    public function __invoke($url): bool
    {
        return Str::startsWith($url, CacheHelpers::baseUrl($this->condition));
    }
}