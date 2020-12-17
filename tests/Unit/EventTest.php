<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Support\Facades\Event;
use Mockery;
use SiteOrigin\PageCache\Events\PageRefreshed;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\PageCollection;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class EventTest extends TestCase
{
    public function test_receiving_page_refreshed_event()
    {
        //$this->expectsEvents(PageRefreshed::class);

        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $article = Article::find(1)->first();
        $article->title = 'New Title';
        $article->save();

        Event::fake();

        $pages = new PageCollection();
        $pages->filterPageUrlIs(route('articles.show', $article))->eager()->requestedPages()->all();

        Event::assertDispatched(PageRefreshed::class, function(PageRefreshed $event){
            return $event->getExchange()->getRequest()->getRequestUri() == $event->getPage()->getUrl();
        });

    }
    
}