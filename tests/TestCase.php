<?php

namespace SiteOrigin\PageCache\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SiteOrigin\KernelCrawler\Facades\Crawler;
use SiteOrigin\PageCache\Crawler\Observer\PageCacheCrawlObserver;
use SiteOrigin\PageCache\CacheServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        $this->resetPageCache();
        $this->withoutExceptionHandling();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetPageCache();
    }

    protected function getPackageProviders($app)
    {
        return [
            CacheServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = $app['config'];
        $config->set('filesystems.disks.page-cache', [
            'driver' => 'local',
            'root' => __DIR__ . '/storage/app/public/page-cache',
        ]);
        $config->set('view.paths', [
            __DIR__ . '/views'
        ]);

        include __DIR__ . '/routes/web.php';

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'SiteOrigin\PageCache\Tests\Database\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    /**
     * Delete all old cache files.
     */
    public function resetPageCache()
    {
        $fs = Storage::disk('page-cache');

        $toDelete = array_merge($fs->allFiles('site'), $fs->allFiles('nocache'));
        foreach($toDelete as $f) {
            $fs->delete($f);
        }
    }

    /**
     * Crawl the site from a starting URL. Triggers caching.
     *
     * @param $startUrl
     */
    public function crawlSite($startUrl)
    {
        Crawler::addUrlsToQueue([$startUrl]);
        Crawler::addObserver(PageCacheCrawlObserver::class);
        Crawler::start();
    }
}