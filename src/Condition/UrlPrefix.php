<?php

namespace SiteOrigin\PageCache\Condition;

use Illuminate\Support\Str;
use SiteOrigin\PageCache\Page;

class UrlPrefix extends Condition
{
    protected static function filterCondition($condition): string
    {
        return Page::baseUrl($condition);
    }

    public function filter(string $url, string $file): bool
    {
        return Str::startsWith($url, CacheHelpers::baseUrl($this->condition));
    }
}