<?php

use Illuminate\Support\Str;
use SiteOrigin\PageCache\CacheHelpers;

class Prefix extends Condition
{
    protected function check($url): bool
    {
        return Str::startsWith($url, CacheHelpers::baseUrl($this->condition));
    }
}