<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

class MemcachedCacheProvider extends IlluminateCacheProvider
{
    /**
     * @var string
     */
    protected $store = 'memcached';
}
