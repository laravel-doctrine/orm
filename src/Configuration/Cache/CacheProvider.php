<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

use Brouwers\LaravelDoctrine\Configuration\Driver;

interface CacheProvider extends Driver
{
    /**
     * @param array $config
     *
     * @return CacheProvider
     */
    public function configure($config = []);
}
