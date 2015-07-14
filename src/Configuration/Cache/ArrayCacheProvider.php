<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

use Doctrine\Common\Cache\ArrayCache;

class ArrayCacheProvider extends AbstractCacheProvider
{
    /**
     * @var string
     */
    protected $name = 'array';

    /**
     * @param array $config
     *
     * @throws DriverNotFound
     * @return ArrayCacheProvider
     */
    public function configure($config = [])
    {
        return $this;
    }

    /**
     * @return ArrayCache
     */
    public function resolve()
    {
        return new ArrayCache();
    }
}
