<?php

namespace SiteOrigin\PageCache\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SiteOrigin\PageCache\Page;
use SiteOrigin\PageCache\Tests\TestCase;

class ExchangeTest extends TestCase
{
    public function test_exchange()
    {
        $page = Page::fromUrl('test/page');
        $exchange = $page->getExchange();

        $this->assertEquals('test/page__.html', $exchange->cachePath());
        $this->assertEquals('test/page__.html', $exchange->getPage()->getFilename());
        $this->assertInstanceOf(Request::class, $exchange->getRequest());
        $this->assertInstanceOf(Response::class, $exchange->getResponse());
    }

}