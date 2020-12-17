<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Filters\UrlDirect;
use SiteOrigin\PageCache\Jobs\RefreshPages;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class RefreshCacheJob extends TestCase
{
    public function test_refresh_cache_job()
    {
        Article::factory()->count(2)->create();
        $article = Article::all()->first();

        $this->get(route('articles.show', $article));

        $page = Page::fromUrl(route('articles.show', $article));

        $this->assertStringContainsString($article->title, $page->getFileContents());

        // Now lets change the title, and have the RefreshFiles job refresh things.
        $article->title = 'Some New Title';
        $article->save();
        RefreshPages::dispatch([
            UrlDirect::fromString('/site/articles/' . $article->id)
        ]);
        $this->assertStringContainsString($article->title, $page->getFileContents());
    }
}