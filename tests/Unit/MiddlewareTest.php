<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\Database\Factories\ArticleFactory;
use SiteOrigin\PageCache\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    public function test_page_cache_created()
    {
        // Test that a middleware request creates the proper page cache
        $response = $this->get('site/home');

        $page = Page::fromUrl('site/home');
        $this->assertTrue(Storage::disk('page-cache')->exists('site/home__.html'), 'Cache file does not exist.');
        $this->assertStringContainsString('This is a test home page', $page->getFileContents());
    }

    public function test_middleware_skipped()
    {
        $response = $this->get('nocache/home');
        $page = Page::fromUrl('nocache/home');
        $this->assertFalse(Storage::disk('page-cache')->exists('nocache/home__.html'), 'File should not have been cached.');
    }

    public function test_deleted_page()
    {
        $this->withExceptionHandling();
        Article::factory()->count(1)->create();
        $article = Article::find(1)->first();

        $this->get(route('articles.show', $article));
        $page = Page::fromUrl(route('articles.show', $article));

        //$this->assertTrue($page->fileExists());

        $article->delete();
        $response = $this->get(route('articles.show', $article));
        $this->assertFalse($page->fileExists());
    }

}