<?php

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use LaravelDoctrine\ORM\Configuration\Cache\MemcachedCacheProvider;
use Mockery as m;
use Psr\Cache\CacheItemPoolInterface;

class MemcachedCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(Factory::class);
        $manager->shouldReceive('store')
                ->with('memcached')
                ->once()->andReturn($repo);

        return new MemcachedCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return CacheItemPoolInterface::class;
    }
}
