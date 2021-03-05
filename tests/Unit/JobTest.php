<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use SiteOrigin\PageCache\Jobs\RefreshPages;
use SiteOrigin\PageCache\PageCollection;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class JobTest extends TestCase
{
    public function test_job()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $article = Article::find(1)->first();
        $article->title = 'Some New Title';
        $article->save();

        RefreshPages::dispatch(
            (new PageCollection())->filterPageUrlIs(route('articles.show', $article)),
            true
        );

        // Check that the page and all the linking pages were
        $response = $this->get('site/articles');
        $response->assertSee($article->title);
        $response = $this->get(route('articles.show', $article));
        $response->assertSee($article->title);
    }

}