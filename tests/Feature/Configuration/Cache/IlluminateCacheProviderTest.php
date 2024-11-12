<?php

declare(strict_types=1);

//namespace Configuration\Cache;

namespace LaravelDoctrineTest\ORM\Feature\Configuration\Cache;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheProvider;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class IlluminateCacheProviderTest extends TestCase
{
    private IlluminateCacheProvider $driver;

    private Repository|m\LegacyMockInterface|m\MockInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = m::mock(Repository::class);
        $manager          = m::mock(Factory::class);
        $manager->shouldReceive('store')
            ->once()
            ->andReturn($this->repository);

        $this->driver = new IlluminateCacheProvider($manager);

        parent::setUp();
    }

    public function testDriverReturnsProvidedNamespace(): void
    {
        $this->repository->shouldReceive('getMultiple')
            ->withSomeOfArgs(['namespace_item'])
            ->once();

        $cache = $this->driver->resolve(['store' => 'redis', 'namespace' => 'namespace']);
        $cache->getItem('item');

        $this->assertTrue(true);
    }

    public function testDriverHasNoNamespaceByDefault(): void
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

        parent::tearDown();
    }
}
