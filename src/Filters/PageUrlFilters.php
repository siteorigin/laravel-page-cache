<?php

namespace SiteOrigin\PageCache\Filters;

use Illuminate\Support\Str;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\PageCollection;

trait PageUrlFilters
{
    /**
     * Filter for pages that have an exact match URL.
     *
     * @param string $url
     * @param false $ignoreQuery
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function filterPageUrlIs(string $url, $ignoreQuery=false): PageCollection
    {
        $url = Page::baseUrl($url);

        return $this->filter(function(Page $page) use ($url, $ignoreQuery){
            $pageUrl = $page->getUrl();
            if($ignoreQuery) $pageUrl = preg_replace('/\?.*/', '', $pageUrl);

            return $url == $pageUrl;
        });
    }

    /**
     * Filter for pages that have a URL that starts with the given URL
     *
     * @param string $url
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function filterPageUrlStartsWith(string $url): PageCollection
    {
        return $this->filter(function(Page $page) use ($url){
            return Str::startsWith($page->getUrl(), Page::baseUrl($url));
        });
    }

    /**
     * Filter pages using a URL regex
     *
     * @param string $regex
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function filterPageUrlMatch(string $regex)
    {
        return $this->filter(function(Page $page) use ($regex){
            return preg_match($regex, $page->getUrl());
        });
    }
}