<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use LaravelDoctrine\ORM\Configuration\Driver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayCacheProvider implements Driver
{
    /**
     * @param array $settings
     *
     * @return CacheItemPoolInterface
     */
    public function resolve(array $settings = [])
    {
        return new ArrayAdapter();
    }
}
