<?php

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\ApcCacheProvider;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use Mockery as m;

class ApcCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(CacheManager::class);
        $manager->shouldReceive('driver')
                ->with('apc')
                ->once()->andReturn($repo);

        return new ApcCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return IlluminateCacheAdapter::class;
    }
}
