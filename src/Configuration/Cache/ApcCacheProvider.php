<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Cache\ApcStore;
use LaravelDoctrine\ORM\Configuration\Driver;

class ApcCacheProvider implements Driver
{
    /**
     * @var ApcStore
     */
    protected $store;

    /**
     * @param ApcStore $store
     */
    public function __construct(ApcStore $store)
    {
        $this->store = $store;
    }

    /**
     * @param array $settings
     *
     * @return ApcCache
     */
    public function resolve(array $settings = [])
    {
        return new ApcCache(
            $this->store
        );
    }
}
