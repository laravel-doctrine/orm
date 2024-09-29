<?php

namespace Configuration\Cache;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheProvider;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class IlluminateCacheProviderTest extends TestCase
{
    /**
     * @var IlluminateCacheProvider
     */
    private $driver;

    /**
     * @var Repository|m\LegacyMockInterface|m\MockInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = m::mock(Repository::class);
        $manager = m::mock(Factory::class);
        $manager->shouldReceive('store')
            ->once()
            ->andReturn($this->repository);

        $this->driver = new IlluminateCacheProvider($manager);
    }

    public function test_driver_returns_provided_namespace(): void
    {
        $this->repository->shouldReceive('getMultiple')
            ->withSomeOfArgs(['namespace_item'])
            ->once();

        $cache = $this->driver->resolve(['store' => 'redis', 'namespace' => 'namespace']);
        $cache->getItem('item');

        $this->assertTrue(true);
    }

    public function test_driver_has_no_namespace_by_default(): void
    {
        $this->repository->shouldReceive('getMultiple')
            ->withSomeOfArgs(['item'])
            ->once();

        $cache = $this->driver->resolve(['store' => 'redis']);
        $cache->getItem('item');

        $this->assertTrue(true);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
