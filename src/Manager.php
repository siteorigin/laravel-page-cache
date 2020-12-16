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

class Manager
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
     * Delete files that pass the given conditions or the entire folder.
     *
     * @param array|null $conditions
     */
    public function clear(array $conditions = null)
    {
        if($conditions) {
            $this->getCacheFiles($conditions)->each(function ($url, $file) {
                $this->filesystem->delete($file);
            });
        }
        else {
            $this->filesystem->delete($this->filesystem->allFiles());
            foreach ($this->filesystem->allDirectories() as $dir) {
                $this->filesystem->deleteDirectory($dir);
            }
        }
    }

    /**
     * Refresh pages and return a collection of the pages that were refreshed.
     *
     * @param array $conditions
     * @param false $withLinking
     * @return \Illuminate\Support\Collection
     */
    public function refresh(array $conditions, $withLinking=false): Collection
    {
        $toRefresh = $this->getCacheFiles($conditions);
        return $this->refreshFiles($toRefresh, $withLinking);
    }

    /**
     * Get all the cached files that link to the original collection of files, but are outside this collection.
     *
     * @param Collection $files Find any cached files that link to this file.
     * @param Collection|null $exclude Exclude these files from the results.
     * @return Collection
     */
    protected function getLinkingFiles(Collection $files, Collection $exclude=null): Collection
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
            ->getCacheFiles()
            ->diffKeys($exclude)
            ->diffKeys($files)
            ->filter(fn($url, $file)  => preg_match($expression, $this->filesystem->get($file)));
    }

    /**
     * Get a list of all known files.
     *
     * @param array|null $conditions
     * @return \Illuminate\Support\Collection
     */
    public function getCacheFiles(array $conditions = null): Collection
    {
        $conditions = array_unique($conditions);
        $allFiles = collect($this->filesystem->allFiles())->toFileUrlMapping();

        $filter = $conditions ? fn($url, $file) => array_sum(array_map(
            fn($c) => $c->setFilesystem($this->filesystem)($url, $file),
            $conditions
        )) : null;

        return !is_null($filter) ? $allFiles->filter($filter) : $allFiles;
    }

    /**
     * @param Collection $toRefresh
     * @param bool $withLinking
     * @return Collection
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function refreshFiles(Collection $toRefresh, $withLinking = false): Collection
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
            $linking = $this->getLinkingFiles($refreshed, $toRefresh);
            $refreshed = $refreshed->merge($this->refreshFiles($linking, false));
        }

        return $refreshed;
    }
}