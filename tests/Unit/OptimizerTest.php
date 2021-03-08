<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use SiteOrigin\PageCache\Listeners\OptimizeHtml;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\TestCase;

class OptimizerTest extends TestCase
{
    use DatabaseMigrations;

    static bool $hasEvents = true;

    public function setUp(): void
    {
        parent::setUp();

        // Change settings for Critical
        Config::set('page-cache.optimizers.critical.command', __DIR__ . '/../../node_modules/.bin/critical');
        Config::set('page-cache.optimizers.critical.css', __DIR__ . '/../public/bootstrap.css');

        // Change settings for the HTML Minifier
        Config::set('page-cache.optimizers.minifier.command', __DIR__ . '/../../node_modules/.bin/html-minifier');
    }

    public function test_optimized_file()
    {
        // Test that a middleware request creates the proper page cache
        $response = $this->get('site/page');
        $page = Page::fromUrl('site/page');

        $this->assertTrue(Storage::disk('page-cache')->exists('site/page__.html'), 'Cache file does not exist.');
        $this->assertTrue(Storage::disk('page-cache')->exists('site/page__.min.html'), 'Minified file does not exist.');

        $optimized = Storage::disk('page-cache')->get('site/page__.min.html');
        $this->assertStringContainsString('.btn-primary', $optimized);
    }

    /**
     * This is a test of HTML content from Briefer
     */
    public function test_optimized_briefer_vue_output()
    {
        $filename = tempnam('', '');
        $original = file_get_contents(__DIR__ . '/../html/nonviolent-communication__.html');
        file_put_contents($filename, $original);

        $optimizer = new OptimizeHtml();
        $optimizer->getOptimizers($filename)->each(fn($o) => $o->handle());
        $content = file_get_contents($filename);

        $this->assertLessThan(strlen($original), strlen($content), 'No minification took place.');
        $this->assertStringContainsString('@click.prevent', $content, 'Vue attributes stripped during minification.');
        $this->assertStringContainsString('@click.exact.prevent', $content, 'Vue attributes stripped during minification.');
        $this->assertStringContainsString('v-bind:id="dynamicId"', $content, 'Vue attributes stripped during minification.');

        unlink($filename);
    }

}