<?php

return [
    'query_patterns' => [
        // Add patterns that can validate any request URIs with query strings
        // Eg: '#^/articles\?page=[0-9]+$#'
    ],

    'cache_pagination' => false,

    'filesystem' => 'page-cache',

    'optimizers' => [
        'critical' => [
            'enabled' => true,
            'class' => \SiteOrigin\PageCache\Jobs\Optimizers\CriticalCss::class,
            'command' => base_path('node_modules/.bin/critical'),
            'css' => public_path('css/app.css')
        ],
        'minifier' => [
            'enabled' => true,
            'class' => \SiteOrigin\PageCache\Jobs\Optimizers\HtmlMinifier::class,
            'command' => base_path('node_modules/.bin/html-minifier'),
        ],
    ]
];
