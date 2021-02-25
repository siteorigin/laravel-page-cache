<?php

return [
    'query_patterns' => [
        // Add patterns that can validate any request URIs with query strings
        // Eg: '#^/articles\?page=[0-9]+$#'
    ],

    'cache_pagination' => false,

    'filesystem' => 'page-cache',

    'optimizers' => [
        \SiteOrigin\PageCache\Jobs\CriticalCssJob::class,
        \SiteOrigin\PageCache\Jobs\HTMLMinifierJob::class,
    ]
];
