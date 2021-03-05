<?php

use SiteOrigin\PageCache\PageCollection;

if (!class_exists('page_collection')) {
    function page_collection(): PageCollection
    {
        return new PageCollection();
    }
}