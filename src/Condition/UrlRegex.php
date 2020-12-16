<?php

namespace SiteOrigin\PageCache\Condition;

class UrlRegex extends Condition
{
    public function __invoke($url, $file): bool
    {
        return preg_match($this->condition, $url);
    }
}