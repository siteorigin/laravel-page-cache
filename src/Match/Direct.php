<?php

use SiteOrigin\PageCache\CacheHelpers;

class Direct extends Condition
{
    protected function check($url): bool
    {
        return $url == CacheHelpers::baseUrl($this->condition);
    }
}