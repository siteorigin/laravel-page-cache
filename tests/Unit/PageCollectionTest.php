<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use SiteOrigin\PageCache\PageCollection;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class PageCollectionTest extends TestCase
{
    public function test_page_collection_creation()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $collection = $this->app->make(PageCollection::class);
        $page = $collection->first();
        $this->assertEquals('SiteOrigin\PageCache\Page', get_class($page));
    }

    public function test_page_collection_delete()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $collection = $this->app->make(PageCollection::class);
        $collection->deletePages();

        $collection = $this->app->make(PageCollection::class);
        $this->assertEmpty($collection);
    }

    public function test_page_collection_refresh()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $article = Article::find(1);
        $article->title = 'New Title';
        $article->save();

        // Check that everything has changed
        $collection = new PageCollection();
        $changed = $collection->requestPages()->pluck('url')->all();
        $this->assertContains('site/articles/1', $changed);
        $this->assertContains('site/articles', $changed);
        $this->assertContains('site/articles?page=1', $changed);
    }

    public function test_collection_direct_url_filters()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $collection = new PageCollection();
        $article = $collection->filterPageUrlIs('site/articles/1')->pluck('url')->first();
        $this->assertEquals('site/articles/1', $article, 'Incorrect direct match.');

        $pages = $collection->filterPageUrlIs('site/articles')->pluck('url')->all();
        $this->assertCount(1, $pages, 'Incorrect page count for direct URL filter.');

        $pages = $collection->filterPageUrlIs('site/articles', true)->pluck('url')->all();
        $this->assertCount(6, $pages, 'Incorrect page count for direct page URL filter with ignored query strings.');
    }

    public function test_collection_url_prefix_filters()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');
        $collection = new PageCollection();

        $pages = $collection->filterPageUrlStartsWith('site/articles')->pluck('url')->all();
        $this->assertCount(16, $pages);

        $pages = $collection->filterPageUrlStartsWith('site/articles/1')->pluck('url')->all();
        $this->assertCount(2, $pages); // We should get articles/1 and articles/10
    }

    public function test_collection_url_match_filters()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');
        $collection = new PageCollection();

        $article = $collection->filterPageUrlMatch('/.*?\/1$/')->pluck('url')->first();
        $this->assertEquals($article, 'site/articles/1');
    }

    public function test_collection_file_links_to()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');
        $collection = new PageCollection();

        $articles = $collection->filterPageUrlIs('site/articles/5')->first();
        $links = $collection->filterPageLinksTo($articles)->pluck('url')->all();
        $this->assertCount(1, $links);
        $this->assertEquals('site/articles?page=3', $links[0]);

        // Check that we get the same result when using a PageCollection
        $articles = $collection->filterPageUrlIs('site/articles/5');
        $links = $collection->filterPageLinksTo($articles)->pluck('url')->all();
        $this->assertEquals('site/articles?page=3', $links[0]);
    }

}