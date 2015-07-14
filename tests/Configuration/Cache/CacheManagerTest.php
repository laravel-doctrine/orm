<?php

use Brouwers\LaravelDoctrine\Configuration\Cache\AbstractCacheProvider;
use Brouwers\LaravelDoctrine\Configuration\Cache\CacheManager;
use Brouwers\LaravelDoctrine\Configuration\Cache\FileCacheProvider;
use Doctrine\Common\Cache\FilesystemCache;

class CacheManagerTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        CacheManager::registerDrivers([
            'file' => [
                'path' => 'path'
            ]
        ]);
    }

    public function test_register_caches()
    {
        $drivers = CacheManager::getDrivers();
        $this->assertCount(1, $drivers);
        $this->assertInstanceOf(FileCacheProvider::class, head($drivers));
    }

    public function test_cache_can_be_extended()
    {
        CacheManager::extend('file', function ($driver) {

            // Should give instance of the already registered driver
            $this->assertInstanceOf(FilesystemCache::class, $driver);

            return $driver;
        });

        $driver = CacheManager::resolve('file');

        $this->assertInstanceOf(FilesystemCache::class, $driver);
    }

    public function test_custom_cache_can_be_set()
    {
        CacheManager::extend('custom', function () {
            return new FilesystemCache('path');
        });

        $driver = CacheManager::resolve('custom');
        $this->assertInstanceOf(FilesystemCache::class, $driver);
    }

    public function test_a_string_class_can_be_use_as_extend()
    {
        CacheManager::extend('custom3', StubCacheProvider::class);

        $driver = CacheManager::resolve('custom3');
        $this->assertEquals('stub', $driver);
    }
}

class StubCacheProvider extends AbstractCacheProvider
{
    /**
     * @param array $config
     *
     * @return array
     */
    public function configure($config = [])
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function resolve()
    {
        return 'stub';
    }
}
