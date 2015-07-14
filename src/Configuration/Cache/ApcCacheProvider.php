<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

use Brouwers\LaravelDoctrine\Exceptions\DriverNotFound;
use Doctrine\Common\Cache\ApcCache;

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
