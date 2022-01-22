<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

class ArrayCacheProvider extends IlluminateCacheProvider
{
    /**
     * @var string
     */
    protected $store = 'array';
}
