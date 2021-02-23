<?php

namespace SiteOrigin\PageCache\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SiteOrigin\PageCache\Page;

class ShowInstallPage
{
    /**
     * Show the install manual page.
     *
     * @param Request $request
     * @return View
     */
    public function __invoke(Request $request): View
    {
        $fs = Storage::disk(config('page-cache.filesystem', 'page-cache'));
        $folder = Str::replaceFirst(storage_path('app/public'), '', $fs->path(''));
        $indexAlias = Page::INDEX_ALIAS;

        return view('page-cache::install', compact( 'folder', 'indexAlias'));
    }
}
