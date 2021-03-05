<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\CacheHelpers;
use SiteOrigin\PageCache\Facades\PageCache;
use SiteOrigin\PageCache\Page;

class InstallApache extends Command
{
    protected $signature = "page-cache:install:apache";
    protected $description = "Installation instructions for Apache servers.";

    public function handle()
    {
        $fs = Storage::disk(config('page-cache.filesystem', 'page-cache'));
        $folder = Str::replaceFirst(storage_path('app/public/'), '', $fs->path(''));

        $info = <<< EOL
# Serve static cached pages if available...
RewriteCond %{REQUEST_URI} ^/?$
RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.min.html -f
RewriteRule .? storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.min.html [L]

RewriteCond %{REQUEST_URI} ^/?$
RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.html -f
RewriteRule .? storage/{{folder}}/{{index_alias}}__%{QUERY_STRING}.html [L]

RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.min.html -f
RewriteRule . storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.min.html [L]

RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.html -f
RewriteRule . storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.html [L]

RewriteCond %{DOCUMENT_ROOT}/storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.json -f
RewriteRule . storage/{{folder}}%{REQUEST_URI}__%{QUERY_STRING}.json [L]
EOL;
        $info = str_replace(['{{folder}}', '{{index_alias}}'], [$folder, Page::INDEX_ALIAS], $info);

        $above = <<< EOL
# Send Requests To Front Controller...
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
EOL;

        // Output all the necessary informatio
        $this->line('Open the file ' . public_path('.htaccess'));
        $this->line("\n\nFind the following lines:");
        $this->line($above, 'fg=blue');
        $this->line("\n\nAdd the following lines above them:");
        $this->line($info, 'fg=blue');

    }
}