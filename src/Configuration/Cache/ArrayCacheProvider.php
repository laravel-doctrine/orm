<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use LaravelDoctrine\ORM\Configuration\Driver;

class ArrayCacheProvider implements Driver
{
    public function resolve(array $settings = []): CacheItemPoolInterface
    {
        return new ArrayAdapter;
    }
}
