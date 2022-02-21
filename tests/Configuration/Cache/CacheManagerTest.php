<?php

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\Cache\ArrayCacheProvider;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Cache\FileCacheProvider;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheManagerTest extends TestCase
{
    /**
     * @var CacheManager
     */
    protected $manager;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Repository
     */
    protected $config;

    protected function setUp(): void
    {
        $this->app = m::mock(Container::class);
        $this->app->shouldReceive('make')->andReturn(m::self());
        $this->app->shouldReceive('get')->with('doctrine.cache.default', 'array')->andReturn('array');

        $this->manager = new CacheManager(
            $this->app
        );
    }

    public function test_driver_returns_the_default_driver()
    {
        $this->app->shouldReceive('resolve')->andReturn(new ArrayCacheProvider());

        $this->assertInstanceOf(ArrayCacheProvider::class, $this->manager->driver());
        $this->assertInstanceOf(ArrayAdapter::class, $this->manager->driver()->resolve());
    }

    public function test_driver_can_return_a_given_driver()
    {
        $config = m::mock(Repository::class);

        $this->app->shouldReceive('resolve')->andReturn(new FileCacheProvider(
            $config
        ));

        $this->assertInstanceOf(FileCacheProvider::class, $this->manager->driver());
    }

    public function test_cant_resolve_unsupported_drivers()
    {
        $this->expectException(DriverNotFound::class);
        $this->manager->driver('non-existing');
    }

    public function test_can_create_custom_drivers()
    {
        $this->manager->extend('new', function () {
            return 'provider';
        });

        $this->assertEquals('provider', $this->manager->driver('new'));
    }

    public function test_can_use_application_when_extending()
    {
        $this->manager->extend('new', function ($app) {
            $this->assertInstanceOf(Container::class, $app);
        });

        $this->assertTrue(true);
    }

    public function test_can_replace_an_existing_driver()
    {
        $this->manager->extend('memcache', function () {
            return 'provider';
        });

        $this->assertEquals('provider', $this->manager->driver('memcache'));
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
