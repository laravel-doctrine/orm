<?php

use Illuminate\Cache\RedisStore;
use LaravelDoctrine\ORM\Configuration\Cache\RedisCache;
use LaravelDoctrine\ORM\Configuration\Cache\RedisCacheProvider;
use Mockery as m;

class RedisCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        $store = m::mock(RedisStore::class);

        return new RedisCacheProvider(
            $store
        );
    }

    public function getExpectedInstance()
    {
        return RedisCache::class;
    }
}
