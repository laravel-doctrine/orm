<?php

use Doctrine\Common\Cache\VoidCache;
use LaravelDoctrine\ORM\Configuration\Cache\VoidCacheProvider;

class VoidCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        return new VoidCacheProvider;
    }

    public function getExpectedInstance()
    {
        return VoidCache::class;
    }
}
