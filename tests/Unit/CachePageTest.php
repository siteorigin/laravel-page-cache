<?php

use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\TestCase;

/**
 * Test for individual cached pages.
 *
 * Class TestCachePage
 */
class CachePageTest extends TestCase
{
    public function test_cache_page_creation_methods()
    {
        // Test a few back and forth cases.
        $page = Page::fromUrl('test/page/');
        $this->assertEquals('test/page__.html', $page->getFilename());

        $page = Page::fromFilename('test/page__.html');
        $this->assertEquals('test/page', $page->getUrl());
        $this->assertEquals('Contents of test page.', $page->getFileContents());
    }

}