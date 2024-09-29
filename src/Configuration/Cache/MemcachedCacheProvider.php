<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

class MemcachedCacheProvider extends IlluminateCacheProvider
{
    protected string|null $store = 'memcached';
}
