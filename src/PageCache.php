<?php

namespace SiteOrigin\PageCache;

use Illuminate\Filesystem\Cache;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class PageCache
{
    const PAGE_CACHE_DIRECTORY = 'page-cache';

    /**
     * @var \Illuminate\Filesystem\FilesystemAdapter|null
     */
    private FilesystemAdapter $filesystem;

    /**
     * PageCache constructor.
     *
     * @param string $disk The name of the disk to use for storing cache files.
     */
    public function __construct(string $disk = 'public')
    {
        $this->filesystem = Storage::disk($disk);
    }

    public function clear()
    {
        $this->filesystem->deleteDirectory($this->getFolder());
    }

    public function getFolder(): string
    {
        return self::PAGE_CACHE_DIRECTORY;
    }

    public function write(CacheableExchange $exchange): bool
    {
        return $this->filesystem->put(
            $this->getFolder() . '/' .$exchange->getCachePath(),
            $exchange->getContent()
        );
    }

    public function shouldWrite(CacheableExchange $exchange): bool
    {
        return $this->hasChanged($exchange) && $exchange->shouldCache();
    }

    public function hasChanged(CacheableExchange $exchange): bool
    {
        $path = $this->getFolder() . '/' . $exchange->getCachePath();

        return (
            ! $this->filesystem->exists($path) ||
            md5_file($this->filesystem->path($path)) !== md5($exchange->getContent())
        );
    }
}