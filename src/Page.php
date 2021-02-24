<?php

namespace SiteOrigin\PageCache;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Page
{
    const INDEX_ALIAS = '__pc_index';

    public string $url;

    public string $filename;

    private string $disk;

    protected $modified = false;

    public function __construct(string $url, string $filename, string $disk = 'page-cache')
    {
        $this->url = $url;
        $this->filename = $filename;
        $this->disk = $disk;
    }

    /**
     * Create a new page from only a URL.
     *
     * @param string $url
     * @param string $disk
     * @return \SiteOrigin\PageCache\Page
     */
    public static function fromUrl(string $url, string $disk = 'page-cache'): Page
    {
        return new self($url, static::urlToFilename($url), $disk);
    }

    /**
     * Create a new page from only the filename.
     *
     * @param string $filename
     * @param string $disk
     * @return \SiteOrigin\PageCache\Page
     */
    public static function fromFilename(string $filename, string $disk = 'page-cache'): Page
    {
        return new self(static::filenameToUrl($filename), $filename, $disk);
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Return the MD5
     *
     * @return false|string The MD5 of the file contents.
     */
    public function getFileMd5()
    {
        return $this->fileExists() ? md5_file(Storage::disk($this->disk)->path($this->filename)) : null;
    }

    /**
     * Get the string contents of a page from the filesystem.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getFileContents(): string
    {
        return Storage::disk($this->disk)->get($this->filename);
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Write contents to the cache file.
     *
     * @param string $contents
     * @return bool
     */
    public function putFileContents(string $contents): bool
    {
        return Storage::disk($this->disk)->put($this->filename, $contents);
    }

    /**
     * Get the response directly from the HttpKernel.
     *
     * @param \Illuminate\Contracts\Http\Kernel|null $kernel
     * @return \SiteOrigin\PageCache\Exchange
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getExchange(?HttpKernel $kernel=null): Exchange
    {
        if (empty($kernel)) $kernel = app()->make(HttpKernel::class);
        $symfonyRequest = SymfonyRequest::create(url($this->url));
        $request = Request::createFromBase($symfonyRequest);
        $response = $kernel->handle($request);

        return new Exchange($request, $response);
    }

    /**
     * Make sure a URL is in a baseURL format.
     *
     * @param $url
     * @return string
     */
    public static function baseUrl($url)
    {
        $url = Str::replaceFirst(url('/'), '', $url);
        return $url == '/' ? $url : trim($url, '/');
    }

    /**
     * Convert a base URL into a cache path.
     *
     * @param $url
     * @param string $extension
     * @return string
     */
    public static function urlToFilename($url, $extension = 'html'): string
    {
        // Turn this into a base URL
        $path = self::baseUrl($url);

        if ($path == '/') $path = '/' . self::INDEX_ALIAS;
        else if (substr($path, 0, 2) == '/?') $path = str_replace('/?', '/' . self::INDEX_ALIAS . '?', $path);

        // We'll add a fake query string for consistency
        if ( strpos($path, '?') === false ) $path .= '?';
        $path = str_replace('?', '__', $path);

        $path = pathinfo($path);

        return join('/', [
            ltrim($path['dirname'], '/'),
            $path['filename'] . '.' . $extension
        ]);
    }

    /**
     * Convert a cache path, back into a base URL
     *
     * @param $path
     * @return string
     */
    public static function filenameToUrl($path): string
    {
        $path = str_replace(self::INDEX_ALIAS, '/', $path);
        $path = pathinfo($path);
        $path['filename'] = preg_replace('/__$/', '', $path['filename']);
        $path['filename'] = preg_replace('/__(.+?)$/', '?$1', $path['filename']);
        if($path['dirname'] == '.') $path['dirname'] = '/';

        $url = $path['dirname'] . '/' . $path['filename'];
        $url = trim($url, '/');
        if($url == '') $url = '/';

        return $url;
    }

    /**
     * @return int Last modified timestamp of the file.
     */
    public function lastModified(): int
    {
        return Storage::disk($this->disk)->lastModified($this->getFilename());
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->getFilename());
    }

    /**
     * Delete the cached file.
     */
    public function deleteFile(): bool
    {
        return Storage::disk($this->disk)->delete($this->getFilename());
    }

    /**
     * Trigger a Kernel request to the page. Rely on middleware to handle the actual refreshing.
     *
     * @param null|HttpKernel $kernel
     * @return bool Was this page modified
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function requestPage(?HttpKernel $kernel=null): bool
    {
        $originalHash = $this->getFileMd5();

        // Get the exchange as a way of requesting the page
        $this->getExchange($kernel);

        // Update the modified
        return ! $this->fileExists() || $originalHash !== $this->getFileMd5();
    }
}
