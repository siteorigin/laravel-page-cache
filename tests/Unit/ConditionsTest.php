<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Support\Facades\Config;
use SiteOrigin\PageCache\Condition\UrlDirect;
use SiteOrigin\PageCache\Facades\PageCache;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class ConditionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('page-cache.query_patterns', [
            '#^/site/articles\?page=[0-9]+$#',
        ]);
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');
    }

    public function test_url_conditions()
    {
        $pages = PageCache::getCacheFiles([
            new UrlDirect('/site/articles/1')
        ]);
        $this->assertCount(1, $pages);
    }
}