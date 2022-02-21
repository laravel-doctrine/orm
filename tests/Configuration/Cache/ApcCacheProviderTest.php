<?php

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use LaravelDoctrine\ORM\Configuration\Cache\ApcCacheProvider;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use Mockery as m;
use Psr\Cache\CacheItemPoolInterface;

class ApcCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $repo    = m::mock(Repository::class);
        $manager = m::mock(Factory::class);
        $manager->shouldReceive('store')
                ->with('apc')
                ->once()->andReturn($repo);

        return new ApcCacheProvider(
            $manager
        );
    }

    public function getExpectedInstance()
    {
        return CacheItemPoolInterface::class;
    }
}
