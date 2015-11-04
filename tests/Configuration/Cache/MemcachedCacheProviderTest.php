<?php

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use LaravelDoctrine\ORM\Configuration\Cache\MemcachedCacheProvider;
use Mockery as m;

class MemcachedCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(CacheManager::class);
        $manager->shouldReceive('driver')
                ->with('memcached')
                ->once()->andReturn($repo);

        return new MemcachedCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return IlluminateCacheAdapter::class;
    }
}
