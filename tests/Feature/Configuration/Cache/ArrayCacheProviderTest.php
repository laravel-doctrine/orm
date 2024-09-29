<?php

use LaravelDoctrine\ORM\Configuration\Cache\ArrayCacheProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayCacheProviderTest extends AbstractCacheProviderTest
{
    public function getProvider()
    {
        return new ArrayCacheProvider;
    }

    public function getExpectedInstance()
    {
        return ArrayAdapter::class;
    }
}
