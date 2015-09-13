<?php

use Illuminate\Cache\ApcStore;
use LaravelDoctrine\ORM\Configuration\Cache\ApcCache;
use LaravelDoctrine\ORM\Configuration\Cache\ApcCacheProvider;
use Mockery as m;

class ApcCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $store = m::mock(ApcStore::class);

        return new ApcCacheProvider(
            $store
        );
    }

    public function getExpectedInstance()
    {
        return ApcCache::class;
    }
}
