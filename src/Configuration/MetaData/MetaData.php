<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class MetaData implements Driver
{
    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @param CacheManager $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }
}
