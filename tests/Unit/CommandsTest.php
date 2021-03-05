<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\App\Article;
use SiteOrigin\PageCache\Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_install_instruction_commands()
    {
        $this->artisan('page-cache:install:nginx')
            ->expectsOutput("Update your location block's try_files directive to include a check in the page-cache directory:\n");

        $this->artisan('page-cache:install:apache')
            ->expectsOutput('Open the file ' . public_path('.htaccess'));
    }

    public function test_refresh_cache_command()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $article = Article::find(1)->first();
        $article->title = 'New Title';
        $article->save();

        // This should refresh the main page, and the 2 versions of the first articles page
        $this->artisan('page-cache:refresh')
            ->expectsOutput('Pages refreshed: 3');
    }

    public function test_clear_cache_command()
    {
        Article::factory()->count(10)->create();
        $this->crawlSite('site/articles');

        $article = Article::find(1)->first();
        $article->title = 'New Title';
        $article->save();

        // This should refresh the main page, and the 2 versions of the first articles page
        $this->artisan('page-cache:clear');
        $page = Page::fromUrl(route('articles.show', $article));

        $this->assertFalse($page->fileExists());

        $this->artisan('page-cache:clear --touch');
        $this->assertTrue($page->fileExists());
    }

}