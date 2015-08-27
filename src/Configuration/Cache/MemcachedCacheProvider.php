<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Cache\MemcachedStore;
use LaravelDoctrine\ORM\Configuration\Driver;

class MemcachedCacheProvider implements Driver
{
    /**
     * @var MemcachedStore
     */
    protected $store;

    /**
     * @param MemcachedStore $store
     */
    public function __construct(MemcachedStore $store)
    {
        $this->store = $store;
    }

    /**
     * @param array $settings
     *
     * @return MemcachedCache
     */
    public function resolve(array $settings = [])
    {
        return new Memcached(
            $this->store
        );
    }
}
