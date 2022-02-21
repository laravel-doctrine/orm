<?php

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use LaravelDoctrine\ORM\Configuration\Cache\RedisCacheProvider;
use Mockery as m;
use Psr\Cache\CacheItemPoolInterface;

class RedisCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(Factory::class);
        $manager->shouldReceive('store')
                ->with('redis')
                ->once()->andReturn($repo);

        return new RedisCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return CacheItemPoolInterface::class;
    }
}
