<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

class ApcCacheProvider extends IlluminateCacheProvider
{
    /**
     * @var string
     */
    protected $store = 'apc';
}
