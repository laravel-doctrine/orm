<?php

use Illuminate\Cache\RedisStore;
use LaravelDoctrine\ORM\Configuration\Cache\RedisCache;
use Mockery as m;

class RedisCacheTest extends AbstractCacheTest
{
    public function getCache()
    {
        return new RedisCache(
            $this->getStore()
        );
    }

    public function getStore()
    {
        return m::mock(RedisStore::class);
    }
}
