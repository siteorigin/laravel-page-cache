<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use SiteOrigin\PageCache\Filters\PageFileFilters;
use SiteOrigin\PageCache\Filters\PageUrlFilters;

class PageCollection extends LazyCollection
{
    use PageUrlFilters, PageFileFilters;

    private string $disk;

    public function __construct($source=null, string $disk=null)
    {
        $this->disk = $disk ?: config('page-cache.filesystem', 'page-cache');

        if (is_null($source)) {
            $source =  function(){
                foreach($this->disk()->allFiles() as $file) {
                    $base = basename($file);
                    if (empty($base) || $base[0] == '.') continue;
                    yield Page::fromFilename($file, $this->disk);
                }
            };
        }

        parent::__construct($source);
    }

    /**
     * Get the filesystem this PageCollection is using.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function deletePages(): PageCollection
    {
        return $this->each(function(Page $page){
            $this->disk()->delete($page->getFilename());
        });
    }

    /**
     * Request all pages, and return only the ones that have changed.
     *
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function requestPages(): PageCollection
    {
        return $this->each(function(Page $page){
            return $page->requestPage();
        });
    }

    /**
     * Make a request to each of the pages.
     *
     * @return \SiteOrigin\PageCache\PageCollection
     */
    public function requestedPages(): PageCollection
    {
        return $this->filter(function(Page $page){
            return $page->requestPage();
        });
    }
}