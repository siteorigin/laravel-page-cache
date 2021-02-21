<?php

use Illuminate\Support\Facades\Route;
use SiteOrigin\PageCache\Http\Controllers\ShowInstallPage;

Route::prefix('page-cache')->middleware('web')->group(function () {
    Route::get('install', ShowInstallPage::class);
});

