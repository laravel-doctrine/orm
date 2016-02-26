<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

class RedisCacheProvider extends IlluminateCacheProvider
{
    /**
     * @var string
     */
    protected $store = 'redis';
}
