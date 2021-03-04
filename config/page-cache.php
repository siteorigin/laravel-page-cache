<?php

return [
    'query_patterns' => [
        // Add patterns that can validate any request URIs with query strings
        // Eg: '#^/articles\?page=[0-9]+$#'
    ],

    'cache_pagination' => true,

    'filesystem' => 'page-cache',

    'optimizers' => [
        'critical' => [
            'enabled' => true,
            'class' => \SiteOrigin\PageCache\Jobs\Optimizers\CriticalCss::class,
            'command' => base_path('node_modules/.bin/critical'),
        ],
        'minifier' => [
            'enabled' => true,
            'class' => \SiteOrigin\PageCache\Jobs\Optimizers\HtmlMinifier::class,
            'command' => base_path('node_modules/.bin/html-minifier'),
        ],
    ],

    // This class is triggered after each iteration in Optimize HTML,
    // must be a child of `SiteOrigin\PageCache\Jobs\ClearCDN\BaseClearCdn` or null.
    'clear_cdn_job' => null
];
