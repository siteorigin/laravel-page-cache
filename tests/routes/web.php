<?php

use Illuminate\Support\Facades\Route;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\App\ArticleController;

// Routes that should be cached.

Route::prefix('site/')->middleware(['bindings', 'page-cache', 'cache.headers:public;max_age=2628000;etag'])->group(function(){
    Route::get('home', function(){
        return view('home');
    });

    Route::get('articles', function(){
        return view('articles.index', [
            'articles' => Article::paginate(2)
        ]);
    })->name('articles.index');

    //Route::get('articles/{article}', function(Article $article){
    Route::get('articles/{article}', function(Article $article){
        return view('articles.show', [
            'article' => $article
        ]);
    })->name('articles.show');
});

Route::get('test/page', function(){
    return file_get_contents(__DIR__.'/../html/page__.html');
});

Route::get('/', function(){
    return view('home');
});

// Routes that shouldn't be cached because of their headers
Route::prefix('nocache/')->middleware('page-cache')->group(function(){
    Route::get('home', function(){
        return view('home');
    });
});