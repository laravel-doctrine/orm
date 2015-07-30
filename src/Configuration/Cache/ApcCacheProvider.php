<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\ApcCache;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

class ApcCacheProvider extends AbstractCacheProvider
{
    /**
     * @var string
     */
    protected $name = 'apc';

    /**
     * @param array $config
     *
     * @throws DriverNotFound
     * @return ApcCacheProvider
     */
    public function configure($config = [])
    {
        return $this;
    }

    /**
     * @throws DriverNotFound
     * @return ApcCache
     */
    public function resolve()
    {
        if (extension_loaded('apc')) {
            return new ApcCache();
        }

        throw new DriverNotFound('Apc extension not loaded');
    }
}
