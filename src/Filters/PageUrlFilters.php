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
     * @param string|array $url
     * @param false $ignoreQuery
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function filterPageUrlIs($url, $ignoreQuery=false): PageCollection
    {
        $url = is_array($url) ? array_map([Page::class, 'baseUrl'], $url) :  Page::baseUrl($url);

        return $this->filter(function(Page $page) use ($url, $ignoreQuery){
            $pageUrl = $page->getUrl();
            if($ignoreQuery) $pageUrl = preg_replace('/\?.*/', '', $pageUrl);

            return is_array($url) ? in_array($pageUrl, $url) : $url == $pageUrl;
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
    public function filterPageUrlMatch(string $regex): PageCollection
    {
        return $this->filter(function(Page $page) use ($regex){
            return preg_match($regex, $page->getUrl());
        });
    }

    /**
     * Filter for page URLs and don't worry if there's a query string. Mainly for pagination.
     *
     * @param $url
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function filterPageUrlWithQuery($url): PageCollection
    {
        return $this->filterPageUrlIs($url, true);
    }
}