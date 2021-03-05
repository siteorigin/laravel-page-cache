<?php

namespace SiteOrigin\PageCache\Filters;

use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\PageCollection;

trait PageFileFilters
{
    public function filterPageLinksTo($pages, $excludeOriginal=true)
    {
        $urls = [];
        if($pages instanceof PageCollection) {
            $urls = $pages->pluck('url')->all();
        }
        else if ($pages instanceof Page) {
            $urls[] = $pages->getUrl();
        }

        $urls = array_map(fn($url) => [$url, url($url), Page::baseUrl($url)], $urls);
        $urls = array_unique(array_merge(...$urls));

        // Create an expression that'll find the
        $expression = array_map(fn($url) => preg_quote($url, '/'), $urls);
        $expression = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(' . implode('|', $expression) . ')?\1/i';

        // Return only pages that contain the given expression.
        $linking = $this->filter(fn(Page $page) => preg_match($expression, $page->getFileContents()));

        return $excludeOriginal ?
            $linking->reject(fn(Page $page) => in_array($page->url, $urls)) :
            $linking;
    }
}