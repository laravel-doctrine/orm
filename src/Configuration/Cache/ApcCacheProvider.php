<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Cache\CacheManager as Manager;
use LaravelDoctrine\ORM\Configuration\Driver;

class ApcCacheProvider implements Driver
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
     * @return ApcCache
     */
    public function resolve(array $settings = [])
    {
        return new IlluminateCacheAdapter(
            $this->cache->driver('apc')
        );
    }
}
