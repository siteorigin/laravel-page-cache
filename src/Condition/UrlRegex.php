<?php

namespace SiteOrigin\PageCache\Condition;

class UrlRegex extends Condition
{
    public function filter(string $url, string $file): bool
    {
        return preg_match($this->condition, $url);
    }
}