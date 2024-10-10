<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

use LaravelDoctrine\ORM\Configuration\Driver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayCacheProvider implements Driver
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): CacheItemPoolInterface
    {
        return new ArrayAdapter();
    }
}
