<?php

use Illuminate\Cache\MemcachedStore;
use LaravelDoctrine\ORM\Configuration\Cache\Memcached;
use LaravelDoctrine\ORM\Configuration\Cache\MemcachedCacheProvider;
use Mockery as m;

class MemcachedCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $store = m::mock(MemcachedStore::class);

        return new MemcachedCacheProvider(
            $store
        );
    }

    public function getExpectedInstance()
    {
        return Memcached::class;
    }
}
