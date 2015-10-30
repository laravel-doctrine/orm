<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\VoidCache;
use LaravelDoctrine\ORM\Configuration\Driver;

class VoidCacheProvider implements Driver
{
    /**
     * @param array $settings
     *
     * @return VoidCache
     */
    public function resolve(array $settings = [])
    {
        return new VoidCache();
    }
}
