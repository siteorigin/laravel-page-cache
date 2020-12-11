<?php

namespace SiteOrigin\PageCache;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class PageCache
{
    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter|null
     */
    private FilesystemAdapter $filesystem;

    /**
     * PageCache constructor.
     *
     * @param string $disk The name of the disk to use for storing cache files.
     */
    public function __construct(string $disk = 'page-cache')
    {
        $this->filesystem = Storage::disk($disk);
    }

    /**
     * Get the current Filesystem adapter.
     *
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    public function getFilesystem(): FilesystemAdapter
    {
        return $this->filesystem;
    }

    /**
     * Delete all, or just a single file.
     *
     * @param string|null $url
     */
    public function clear(?string $url = null)
    {
        if($url) {
            $this->filesystem->delete([
                CacheHelpers::urlToCachePath($url, 'html'),
                CacheHelpers::urlToCachePath($url, 'json'),
            ]);
        }
        else {
            $this->filesystem->delete($this->filesystem->allFiles());
            foreach ($this->filesystem->allDirectories() as $dir) {
                $this->filesystem->deleteDirectory($dir);
            }
        }
    }

    /**
     * Refresh all cached files
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshAll()
    {
        return $this->refreshCacheFiles($this->getAllFiles(), true);
    }

    /**
     * Refresh all files that have the given base URLs.
     *
     * @param array|string $urls A list of URLs we want to refresh.
     * @param false $withLinking Should we perform a second refresh run, looking at the incoming links.
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshByUrls($urls, $withLinking = false)
    {
        $startUrls = array_map(fn($url) => CacheHelpers::baseUrl($url), !is_array($urls) ? [$urls] : $urls);
        $toRefresh = $this->getAllFiles(fn($url, $file) => in_array($url, $startUrls))
            ->merge();

        return $this->refreshCacheFiles($toRefresh, $withLinking);
    }

    /**
     * Refresh all files that have a base URL that starts with a given prefix.
     *
     * @param array|string $prefixes
     * @param false $withLinking
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshByUrlPrefix(array $prefixes, $withLinking = false)
    {
        $startPrefixes = array_map(
            fn($url) => CacheHelpers::baseUrl($url), !is_array($prefixes) ? [$prefixes] : $prefixes
        );

        $toRefresh = $this->getAllFiles(
            fn($url, $file) => array_sum(array_map(fn($prefix) => Str::startsWith($url, $prefix), $startPrefixes))
        );

        return $this->refreshCacheFiles($toRefresh, $withLinking);
    }

    /**
     * Get all files that have a base URL that matches a given prefix.
     *
     * @param array|string $regexes
     * @param false $withLinking
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function refreshByUrlRegex(array $regexes, $withLinking = false)
    {
        $this->allFiles = $this->getAllFiles();

        $toRefresh = $this->getAllFiles(
            fn($url, $file) => array_sum(array_map(
                fn($regex) => preg_match($regex, $url), !is_array($regexes) ? [$regexes] : $regexes
            ))
        );

        return $this->refreshCacheFiles($toRefresh, $withLinking);
    }

    public function refreshByModelCollection()
    {
        $r = new Request();
        Route::dispatch();
    }

    /**
     * Get all the cached files that link to the original collection of files, but are outside this collection.
     *
     * @param Collection $files Find any cached files that link to this file.
     * @param Collection|null $exclude Exclude these files from the results.
     * @return Collection
     */
    protected function getLinking(Collection $files, Collection $exclude=null): Collection
    {
        // Create a simple expression that can find linking files
        $expression = $files
            ->map(fn($url, $file) => [$url, url($url)])
            ->flatten()
            ->unique()
            ->map(
                fn($url) => preg_quote($url, '/')
            )
            ->join('|');
        $expression = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(' . $expression . ')?\1/i';

        // Return any files that have the simple regex.
        return $this
            ->getAllFiles()
            //->diffKeys($exclude)
            ->diffKeys($files)
            ->filter(fn($url, $file)  => preg_match($expression, $this->filesystem->get($file)));
    }

    /**
     * Get a list of all known files.
     *
     * @param callable|null $filter
     * @return \Illuminate\Support\Collection
     */
    protected function getAllFiles(callable $filter = null): Collection
    {
        $allFiles = collect($this->filesystem->allFiles())->toFileUrlMapping();
        return !is_null($filter) ? $allFiles->filter($filter) : $allFiles;
    }

    /**
     * @param Collection $toRefresh
     * @param bool $withLinking
     * @return Collection
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function refreshCacheFiles(Collection $toRefresh, $withLinking = false): Collection
    {
        $kernel = app()->make(HttpKernel::class);

        // Lets create the requests
        $refreshed = $toRefresh->filter(function($url, $file) use ($kernel) {
            $modified = $this->filesystem->lastModified($file);

            // Just generate a request on this URL and let the CacheResponse Middleware handle refreshing
            $symfonyRequest = SymfonyRequest::create(url($url));
            $request = Request::createFromBase($symfonyRequest);
            $response = $kernel->handle($request);

            if($response->getStatusCode() == 404) {
                // Delete files that are no longer found
                $this->filesystem->delete($file);
            }

            // Return true if the file has changed
            return ! $this->filesystem->exists($file) || $this->filesystem->lastModified($file) != $modified;
        });

        if ($withLinking && $refreshed->count()) {
            // Add in the linking files, then refresh them too, but with $withLinking disabled.
            $linking = $this->getLinking($refreshed, $toRefresh);
            $refreshed = $refreshed->merge($this->refreshCacheFiles($linking, false));
        }

        return $refreshed;
    }
}