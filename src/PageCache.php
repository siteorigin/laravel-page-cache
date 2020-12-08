<?php

namespace SiteOrigin\PageCache;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use SiteOrigin\PageCache\Events\CachedPageChanged;

class PageCache
{
    const INDEX_ALIAS = '__pc_index';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container|null
     */
    protected ?Container $container = null;

    /**
     * The directory in which to store the cached pages.
     *
     * @var string|null
     */
    protected ?string $cachePath = null;

    /**
     * Constructor.
     *
     * @var \Illuminate\Filesystem\Filesystem  $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Sets the container instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Sets the directory in which to store the cached pages.
     *
     * @param  string  $path
     * @return void
     */
    public function setCachePath($path)
    {
        // Remove trailing slash
        $this->cachePath = rtrim($path, '\/');
    }

    /**
     * Gets the path to the cache directory.
     *
     * @param  string  ...$paths
     * @return string
     *
     * @throws \Exception
     */
    public function getCachePath()
    {
        $base = $this->cachePath ? $this->cachePath : $this->getDefaultCachePath();

        if (is_null($base)) {
            throw new Exception('Cache path not set.');
        }

        return $this->join(array_merge([$base], func_get_args()));
    }

    /**
     * Join the given paths together by the system's separator.
     *
     * @param  string[] $paths
     * @return string
     */
    protected function join(array $paths)
    {
        $trimmed = array_map(fn ($path) => trim($path, '/'), $paths);

        return $this->matchRelativity(
            $paths[0], implode('/', array_filter($trimmed))
        );
    }

    /**
     * Makes the target path absolute if the source path is also absolute.
     *
     * @param  string  $source
     * @param  string  $target
     * @return string
     */
    protected function matchRelativity($source, $target)
    {
        return $source[0] == '/' ? '/'.$target : $target;
    }

    /**
     * Caches the given response if we determine that it should be cache.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return $this
     * @throws \Exception
     */
    public function cacheIfNeeded(Request $request, Response $response): PageCache
    {
        if (
            $this->shouldCache($request, $response) &&
            $this->hasChanged($request, $response, true)
        ) {
            $this->cache($request, $response);
        }

        return $this;
    }

    /**
     * Determines whether the given request/response pair should be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return bool
     */
    public function shouldCache(Request $request, Response $response): bool
    {
        if ($request->getQueryString()) {
            // Reject any requests that have a query string that doesn't match our patterns
            $matches = collect(config('page-cache.query_patterns'))
                ->map(fn($pattern) => preg_match($pattern, $request->getRequestUri()))
                ->sum();

            if (!$matches) return false;
        }

        return $request->isMethod('GET') && $response->getStatusCode() == 200;
    }

    /**
     * Check if the version we have has changed compared to what we have on file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @param bool $triggerEvent
     * @return bool
     * @throws \Exception
     */
    public function hasChanged(Request $request, Response $response, $triggerEvent = false): bool
    {
        $path = join('/', $this->getDirectoryAndFilename($request, $response));
        if (! file_exists($path) || md5($response->getContent()) != md5_file($path)) {
            if ($triggerEvent) CachedPageChanged::dispatch($this, $request, $response);
            return true;
        }
        else return false;
    }

    /**
     * Cache the response to a file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     * @throws \Exception
     */
    public function cache(Request $request, Response $response)
    {
        [$path, $file] = $this->getDirectoryAndFilename($request, $response);

        $this->files->makeDirectory($path, 0775, true, true);

        $this->files->put(
            $this->join([$path, $file]),
            $response->getContent(),
            true
        );
    }

    /**
     * Remove the cached file for the given slug.
     *
     * @param string $slug
     * @return bool
     * @throws \Exception
     */
    public function forget(string $slug): bool
    {
        $deletedHtml = $this->files->delete($this->getCachePath($slug.'.html'));
        $deletedJson = $this->files->delete($this->getCachePath($slug.'.json'));

        return $deletedHtml || $deletedJson;
    }

    /**
     * Clear the full cache directory, or a subdirectory.
     *
     * @param string|null
     * @return bool
     * @throws \Exception
     */
    public function clear($path = null): bool
    {
        return $this->files->deleteDirectory($this->getCachePath($path), true);
    }

    /**
     * Get the names of the directory and file.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     * @return array
     * @throws \Exception
     */
    public function getDirectoryAndFilename(Request $request, Response $response): array
    {
        $uri = $this->aliasUri($request->getRequestUri());
        $filename = $this->uriToFilename($uri);
        $path = pathinfo($filename.'.html');

        $dirname = $path['dirname'];
        $filename = $path['filename'] . '.' . $this->guessFileExtension($response);

        return [$this->getCachePath($dirname), $filename];
    }

    /**
     * Alias the filename if necessary.
     *
     * @param string $uri
     * @return string
     */
    protected function aliasUri(string $uri): string
    {
        // Handle the index
        if ($uri == '/') $uri = '/' . self::INDEX_ALIAS;
        else if (substr($uri, 0, 2) == '/?') $uri = str_replace('/?', '/' . self::INDEX_ALIAS . '?', $uri);

        // We'll add a fake query string for consistency
        if ( strpos($uri, '?') === false ) $uri .= '?';

        return $uri;
    }

    /**
     * Convert a URI to a filename.
     *
     * @param string $uri
     * @return string|string[]
     */
    protected function uriToFilename(string $uri)
    {
        return str_replace('?', '__', $uri);
    }

    /**
     * Get the default path to the cache directory.
     *
     * @return string|null
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function getDefaultCachePath(): ?string
    {
        if ($this->container && $this->container->bound('path.public')) {
            return $this->container->make('path.public').'/page-cache';
        }
    }

    /**
     * Guess the correct file extension for the given response.
     *
     * Currently, only JSON and HTML are supported.
     *
     * @param $response
     * @return string
     */
    protected function guessFileExtension($response): string
    {
        return $response instanceof JsonResponse ? 'json' : 'html';
    }

}
