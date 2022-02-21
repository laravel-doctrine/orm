<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\Psr6\CacheAdapter;
use Psr\Cache\CacheItemPoolInterface;
use const E_USER_DEPRECATED;
use Illuminate\Contracts\Cache\Factory;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Configuration\Driver;

class IlluminateCacheProvider implements Driver
{
    /**
     * @var Factory
     */
    protected $cache;

    /**
     * @var string
     */
    protected $store;

    /**
     * @param Factory $cache
     */
    public function __construct(Factory $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param array $settings
     *
     * @return CacheItemPoolInterface
     */
    public function resolve(array $settings = []): CacheItemPoolInterface
    {
        $store = $this->store ?? $settings['store'] ?? null;

        if ($store === null) {
            throw new InvalidArgumentException('Please specify the `store` when using the "illuminate" cache driver.');
        }

        if ($this->store && isset($settings['store'])) {
            trigger_error('Using driver "' . $this->store . '" with a custom store is deprecated. Please use the "illuminate" driver.', E_USER_DEPRECATED);
        }

        return CacheAdapter::wrap(
            new IlluminateCacheAdapter($this->cache->store($store))
        );
    }
}
