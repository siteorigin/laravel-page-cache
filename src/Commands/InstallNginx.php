<?php

namespace SiteOrigin\PageCache\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\CacheHelpers;
use SiteOrigin\PageCache\Facades\PageCache;
use SiteOrigin\PageCache\Page;

class InstallNginx extends Command
{
    protected $signature = "page-cache:install:nginx";
    protected $description = "Installation instructions for Nginix servers.";

    public function handle()
    {
        $folder = '/storage/page-cache/';
        $indexAlias = Page::INDEX_ALIAS;

        $indexTry = [
            $folder . $indexAlias . '.min.html',
            $folder . $indexAlias . '__$query_string.min.html',
            $folder . $indexAlias . '.html',
            $folder . $indexAlias . '__$query_string.html',
            '/index.php?$query_string'
        ];

        $siteTry = [
            '$uri',
            '$uri/',
            $folder . '${uri}.min.html',
            $folder . '${uri}__${query_string}.min.html',
            $folder . '${uri}.html',
            $folder . '${uri}__${query_string}.html',
            $folder . '${uri}.json',
            $folder . '${uri}__${query_string}.json',
            '/index.php?$query_string'
        ];

        $directive = "location = / {\n\ttry_files " . implode(' ', $indexTry) . ";\n}\n\n";
        $directive .= "location / {\n\ttry_files " . implode(' ', $siteTry) . ";\n}";

        // Output all the necessary informatio
        $this->line("Update your location block's try_files directive to include a check in the page-cache directory:\n");
        $this->line("#############################");
        $this->line($directive, 'fg=cyan');
        $this->line("#############################");
    }
}