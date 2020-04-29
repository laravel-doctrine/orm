<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Contracts\Cache\Factory;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class IlluminateCacheProvider implements Driver
{
    /**
     * @var Factory
     */
    protected $cache;

    /**
     * @var string
     */
    protected $store;

    /**
     * @param Factory $cache
     */
    public function __construct(Factory $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param array $settings
     *
     * @return IlluminateCacheAdapter
     */
    public function resolve(array $settings = [])
    {
        $store = $settings['store'] ?? $this->store;

        return new IlluminateCacheAdapter(
            $this->cache->store($store)
        );
    }
}
