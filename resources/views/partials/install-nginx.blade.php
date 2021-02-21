@php
    $indexTry = [
        $folder . $indexAlias . '.html',
        $folder . $indexAlias . '__$query_string.html',
        '/index.php?$query_string'
    ];

    $siteTry = [
        '$uri',
        '$uri/',
        $folder . '$uri.html',
        $folder . '$uri__$query_string.html',
        $folder . '$uri.json',
        $folder . '$uri__$query_string.json',
        '/index.php?$query_string'
    ];

    $directive = "location = / {\n\ttry_files " . implode(' ', $indexTry) . "\n}\n\n";
    $directive .= "location / {\n\ttry_files " . implode(' ', $siteTry) . "\n}";
@endphp

<div class="mt-10">
    <div class="mb-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
            Installation instructions for Nginx servers.
        </h3>
        <p class="text-gray-500">
            Update your location block's try_files directive to include a check in the page-cache
            directory:
        </p>
    </div>
    <div class="w-full">
        <pre><code class="language-apache p-5 text-sm">{{ $directive }}</code></pre>
    </div>
</div>
