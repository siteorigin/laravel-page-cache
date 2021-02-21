@php
    $info = <<< EOL
# Serve static cached pages if available...

RewriteCond %{REQUEST_URI} ^/?$
RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.html -f
RewriteRule .? storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.html [L]

RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.html -f
RewriteRule . storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.html [L]

RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.json -f
RewriteRule . storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.json [L]
EOL;
    $info = str_replace(['{{folder}}', '{{index_alias}}'], [$folder, SiteOrigin\PageCache\Page::INDEX_ALIAS], $info);

    $above = <<< EOL
# Send Requests To Front Controller...

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
EOL;
@endphp

<div class="mt-10">
    <div class="mb-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
            Installation instructions for Apache servers.
        </h3>
        <p class="text-gray-500">
            Open the file
            <input
                readonly
                class="font-medium text-gray-900 px-3 py-2 bg-gray-100 rounded-md w-full block my-2 focus:outline-none"
                value="{{ public_path('.htaccess') }}"
            >
            Find the following lines:
        </p>
    </div>
    <div class="w-full">
        <pre><code class="language-apache p-5 text-sm">{{ $above }}</code></pre>
    </div>
</div>

<div class="mt-8">
    <div class="mb-5">
        <p class="text-gray-500">
            Add the following lines above them:
        </p>
    </div>
    <div class="w-full divide-y divide-gray-200">
        <pre><code class="language-apache p-5 text-sm">{{ $info }}</code></pre>
    </div>
</div>

{{--<div class="flex items-center mt-8">--}}
{{--    <button type="submit"--}}
{{--            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-blue-500">--}}
{{--        Check--}}
{{--    </button>--}}
{{--    <svg class="w-5 h-5 ml-3 text-green-700" viewBox="0 0 20 20" fill="currentColor">--}}
{{--        <path fill-rule="evenodd"--}}
{{--              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"--}}
{{--              clip-rule="evenodd"/>--}}
{{--    </svg>--}}
{{--    <svg class="w-5 h-5 ml-3 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"--}}
{{--              d="M6 18L18 6M6 6l12 12"/>--}}
{{--    </svg>--}}
{{--</div>--}}
