<?php

use Illuminate\Cache\ApcStore;
use LaravelDoctrine\ORM\Configuration\Cache\ApcCache;
use Mockery as m;

class ApcCacheTest extends AbstractCacheTest
{
    public function getCache()
    {
        return new ApcCache(
            $this->getStore()
        );
    }

    public function getStore()
    {
        return m::mock(ApcStore::class);
    }
}
