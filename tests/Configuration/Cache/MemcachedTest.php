<?php

use Illuminate\Cache\MemcachedStore;
use LaravelDoctrine\ORM\Configuration\Cache\Memcached;
use Mockery as m;

class MemcachedTest extends AbstractCacheTest
{
    public function getCache()
    {
        return new Memcached(
            $this->getStore()
        );
    }

    public function getStore()
    {
        return m::mock(MemcachedStore::class);
    }
}
