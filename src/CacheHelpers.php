<?php

namespace SiteOrigin\PageCache;

use Illuminate\Support\Str;

class CacheHelpers
{
    const INDEX_ALIAS = '__pc_index';

    public static function baseUrl($url)
    {
        return Str::replaceFirst(url('/'), '', $url);
    }

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