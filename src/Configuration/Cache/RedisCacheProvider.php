<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Cache\CacheManager as Manager;
use LaravelDoctrine\ORM\Configuration\Driver;

class RedisCacheProvider implements Driver
{
    /**
     * @var Manager
     */
    protected $cache;

    /**
     * @param Manager $cache
     */
    public function __construct(Manager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param array $settings
     *
     * @return RedisCache
     */
    public function resolve(array $settings = [])
    {
        return new IlluminateCacheAdapter(
            $this->cache->driver('redis')
        );
    }
}
