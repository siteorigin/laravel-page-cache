<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use SiteOrigin\PageCache\Jobs\Optimizers\HtmlMinifier;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\TestCase;

class HtmlMinifierTest extends TestCase
{
    use DatabaseMigrations;

    static bool $hasEvents = true;

    public function setUp(): void
    {
        parent::setUp();

        // Change settings for Critical
        Config::set('page-cache.optimizers.critical.command', __DIR__ . '/../../node_modules/.bin/critical');

        // Change settings for the HTML Minifier
        Config::set('page-cache.optimizers.minifier.command', __DIR__ . '/../../node_modules/.bin/html-minifier');
    }

    public function test_minfier_doesnt_strip_vue()
    {
        $filename = tempnam('', '');
        file_put_contents($filename, file_get_contents(__DIR__ . '/../html/vue-page.html'));

        $minifier = new HtmlMinifier($filename, config('page-cache.optimizers.minifier'));
        $minifier->handle();

        $content = file_get_contents($filename);
        $this->assertLessThan(strlen(file_get_contents(__DIR__ . '/../html/vue-page.html')), strlen($content), 'No minification took place.');
        $this->assertStringContainsString('@click.prevent', $content, 'Vue attributes stripped during minification.');
        $this->assertStringContainsString('@click.exact.prevent', $content, 'Vue attributes stripped during minification.');
        unlink($filename);
    }

}