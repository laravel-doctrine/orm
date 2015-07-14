<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use LaravelDoctrine\ORM\Configuration\Driver;

interface CacheProvider extends Driver
{
    /**
     * @param array $config
     *
     * @return CacheProvider
     */
    public function configure($config = []);
}
