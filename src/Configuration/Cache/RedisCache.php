<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Illuminate\Cache\RedisStore;

class RedisCache extends CacheProvider
{
    /**
     * @var RedisStore
     */
    protected $store;

    /**
     * @param RedisStore $store
     */
    public function __construct(RedisStore $store)
    {
        $this->store = $store;
    }

    /**
     * @return RedisStore
     */
    public function getRedis()
    {
        return $this->store;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     *
     * @return string|bool The cached data or FALSE, if no cache entry exists for the given id.
     */
    protected function doFetch($id)
    {
        return $this->store->get($id) ?: false;
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     *
     * @return bool TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    protected function doContains($id)
    {
        return (bool) $this->store->connection()->exists($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        return $this->store->put($id, $data, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->store->forget($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->store->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats = $this->store->connection()->info();

        return [
            Cache::STATS_HITS              => isset($stats['keyspace_hits']) ? $stats['keyspace_hits'] : $stats['Stats']['keyspace_hits'],
            Cache::STATS_MISSES            => isset($stats['keyspace_misses']) ? $stats['keyspace_misses'] : $stats['Stats']['keyspace_misses'],
            Cache::STATS_UPTIME            => isset($stats['uptime_in_seconds']) ? $stats['uptime_in_seconds'] : $stats['Server']['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE      => isset($stats['used_memory']) ? $stats['used_memory'] : $stats['Memory']['used_memory']
        ];
    }
}
