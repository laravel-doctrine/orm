<?php

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use LaravelDoctrine\ORM\Configuration\Cache\RedisCacheProvider;
use Mockery as m;

class RedisCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(CacheManager::class);
        $manager->shouldReceive('driver')
                ->with('redis')
                ->once()->andReturn($repo);

        return new RedisCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return IlluminateCacheAdapter::class;
    }
}
