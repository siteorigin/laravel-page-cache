<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Str;

class CacheHelpers
{
    const INDEX_ALIAS = '__pc_index';

    /**
     * Make sure a URL is in a baseURL format.
     *
     * @param $url
     * @return string
     */
    public static function baseUrl($url)
    {
        $url = Str::replaceFirst(url('/'), '', $url);
        return $url == '/' ? $url : ltrim($url, '/');
    }

    /**
     * Convert a base URL into a cache path.
     *
     * @param $url
     * @param string $extension
     * @return string
     */
    public static function urlToCachePath($url, $extension = 'html'): string
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
    public static function cachePathToUrl($path)
    {
        $path = str_replace(self::INDEX_ALIAS, '/', $path);
        $path = pathinfo($path);
        $path['filename'] = preg_replace('/__$/', '', $path['filename']);
        $path['filename'] = preg_replace('/__(.+?)$/', '?$1', $path['filename']);
        if($path['dirname'] == '.') $path['dirname'] = '/';

        $url = $path['dirname'] . '/' . $path['filename'];
        $url = ltrim($url, '/');
        if($url == '') $url = '/';

        return $url;
    }
    
}