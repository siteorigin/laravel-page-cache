![Laravel Page Cache](.github/logo.svg)

# Laravel Page Cache

Laravel Page Cache is a static page caching package. It's heavily inspired by, and forked from [Joseph Silber](https://github.com/JosephSilber)'s [Page Cache](https://github.com/JosephSilber/page-cache) package. This package goes in a slightly different direction, so you should check both to decide which is best for you.

If you combine this package, with [our prefetching Turbolinks package](https://github.com/siteorigin/laravel-turbo), a fast CDN and [Livewire](https://github.com/livewire/livewire) for any dynamic components of your site - you can get a dynamic site, with static site speeds, all without leaving the comfort of Laravel.

## Installation

*Installation instructions will follow once this is published on Packagist.*

You can publish the configuration file using.

`php artisan vendor:publish --provider="SiteOrigin\PageCache\PageCacheServiceProvider" --tag="config"`

By default, cached files are stored in your app storage public path. So you need to make sure this is linked to your actual public path. To do this, just run `php artisan storage:link`. You can read more [here](https://laravel.com/docs/8.x/filesystem#the-public-disk).


## Web Server Configuration

You need to tell your web server to look for 

## Middleware

You can add page caching to all your web routes by adding `\SiteOrigin\PageCache\Middleware\CacheResponse::class` to the [web middleware group](https://laravel.com/docs/8.x/middleware#middleware-groups).

You can also use the `page-cache` alias to add caching to individual routes. Something like the following:

```php
Route::get('/', function () {
    return view('welcome');
})->name('home')->middleware('cache.headers:public;max_age=2628000;etag', 'page-cache');
```

The `page-cache` alias is registered for you, so you don't need to add this again.

## Caching Query Strings

This package has support for caching query strings too. This is especially useful for caching paginated URLs. By default, it supports pagination query strings, but if you have other that you'd like to cache, then you can add custom configurations.

### Validating Query Strings

It's important that you validate each request, and return something besides a `200` response if the request isn't formed properly. This prevents an attack where someone makes a lot of requests to random query string URLs, thus filling up your server's disk.

For example, for paginated URLs, you should return a 404 if the page number is out of range.

```php
public function index(Request $request)
{
    $articles = Article::simplePaginate(5);

    return view('articles.index', [
        'articles' => $articles->count() ? $articles : abort(404)
    ]);
}
```

The default page caching already makes sure the `page` query string argument is an integer.

### Disabling Pagination Caching

If you don't want to cache paginated URLs, you can disable this in the `page-cache.php` config file.

Change the following line to false `'cache_pagination' => true,`.

### Caching Custom Query String URLs

If you want to cache a custom query string URL, you can do this by adding a custom regular expression to `'query_patterns' => [ ... ]` in the `page-cache.php` config file.

Any query string that matches one of the expressions in this list, will be cached. You should do as much validation as possible in the query string as possible, and any additional validation in the controller. Return a `404` if the request seems invalid.

## Using a Custom Disk

By default, this package's service provider will register a `page-cache` local disk that just points to `storage_path('app/public/page-cache')`. You can change this behavior by adding your own `page-cache` disk to `filesystems.php`.

I'll update this section once I've tested this myself, but you could use this to store cached pages on cloud storage, and then use multi source CDN to check that cloud storage for cached files, and only make requests to your web server if its not found. This might be possible with Fastly using [Multiple Backends](https://docs.fastly.com/en/guides/checking-multiple-backends-for-a-single-request) and [URL Rewrites](https://developer.fastly.com/solutions/recipes/rewrite-url-path).

## Console Commands

### Clearing Page Cache

Clear your entire page cache using `php artisan page-cache:clear`. You can also recrawl your site to warm the cache using the `--touch` option.

### Refreshing Pages

Refresh every page on your site using `php artisan page-cache:refresh`. This will just go through all existing cached pages and refresh any that have changed.

### Installation Instructions

Run `php artisan page-cache:install:apache` for PHP installation instructions or `php artisan page-cache:install:nginx` for Nginx installation instructions.
