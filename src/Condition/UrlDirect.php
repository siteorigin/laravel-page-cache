<?php

namespace SiteOrigin\PageCache\Condition;

use SiteOrigin\PageCache\Page;

class UrlDirect extends Condition
{
    protected static function filterCondition($condition): string
    {
        return Page::baseUrl($condition);
    }

    /**
     * Check that the URL exactly matches
     *
     * @param string $url
     * @param string $file
     * @return bool
     */
    public function filter(string $url, string $file): bool
    {
        if ( !empty($this->args['ignore_query']) ) {
            $url = preg_replace('/\?.*/', '', $url);
        }

        return $url == Page::baseUrl($this->condition);
    }
}