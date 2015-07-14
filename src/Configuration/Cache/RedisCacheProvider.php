<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

use Brouwers\LaravelDoctrine\Exceptions\DriverNotFound;
use Doctrine\Common\Cache\RedisCache;
use Redis;

class RedisCacheProvider extends AbstractCacheProvider
{
    /**
     * @var string
     */
    protected $name = 'redis';

    /**
     * @param array $config
     *
     * @throws DriverNotFound
     * @return RedisCacheProvider
     */
    public function configure($config = [])
    {
        $redisConfig = config('database.redis.' . $config['connection']);

        $this->config = [
            'host'     => $redisConfig['host'],
            'port'     => $redisConfig['port'],
            'database' => $redisConfig['database']
        ];

        return $this;
    }

    /**
     * @throws DriverNotFound
     * @return RedisCache
     */
    public function resolve()
    {
        if (extension_loaded('redis')) {
            $cache = new RedisCache();
            $redis = new Redis();
            $redis->connect($this->config['host'], $this->config['port']);
            $redis->select($this->config['database']);

            $cache->setRedis($redis);

            return $cache;
        }

        throw new DriverNotFound('Redis extension was not found');
    }
}
