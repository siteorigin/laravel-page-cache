<?php

namespace SiteOrigin\PageCache\Condition;

class Regex extends Condition
{
    public function __invoke($url): bool
    {
        return preg_match($this->condition, $url);
    }
}