<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Cache\RedisStore;
use LaravelDoctrine\ORM\Configuration\Driver;

class RedisCacheProvider implements Driver
{
    /**
     * @var RedisStore
     */
    protected $store;

    /**
     * @param RedisStore $store
     */
    public function __construct(RedisStore $store)
    {
        $this->store = $store;
    }

    /**
     * @param array $settings
     *
     * @return RedisCache
     */
    public function resolve(array $settings = [])
    {
        return new RedisCache(
            $this->store
        );
    }
}
