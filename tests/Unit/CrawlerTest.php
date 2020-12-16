<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Crawler\Observer\PageCacheCrawlObserver;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class CrawlerTest extends TestCase
{
    public function test_crawling_site_no_query_strings()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        // Test that all the cache files were properly created
        $fs = Storage::disk('page-cache');
        $files = $fs->allFiles('site');
        $this->assertCount(11, $files);
    }

    public function test_crawling_site_with_query_strings()
    {
        Config::set('page-cache.query_patterns', [
            '#^/site/articles\?page=[0-9]+$#',
        ]);
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        // Test that all the cache files were properly created
        $fs = Storage::disk('page-cache');
        $files = $fs->allFiles('site');
        $this->assertCount(16, $files);
    }
}