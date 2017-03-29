<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Illuminate\Contracts\Cache\Repository;

class IlluminateCacheAdapter extends CacheProvider
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
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
        return $this->cache->get($id) ?: false;
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
        return (bool) $this->cache->has($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        if (!$lifeTime) {
            return $this->cache->forever($id, $data);
        }

        // Laravel cache system accept expire times in minutes
        // while Doctrine accept it in seconds, so we need to convert $lifeTime from seconds to minutes
        // before passing it to Laravel cache repository
        $lifeTimeMinutes = $lifeTime / 60;

        return $this->cache->put($id, $data, $lifeTimeMinutes);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->cache->forget($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->cache->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats = $this->cache->connection()->info();

        return [
            Cache::STATS_HITS         => isset($stats['keyspace_hits']) ? $stats['keyspace_hits'] : $stats['Stats']['keyspace_hits'],
            Cache::STATS_MISSES       => isset($stats['keyspace_misses']) ? $stats['keyspace_misses'] : $stats['Stats']['keyspace_misses'],
            Cache::STATS_UPTIME       => isset($stats['uptime_in_seconds']) ? $stats['uptime_in_seconds'] : $stats['Server']['uptime_in_seconds'],
            Cache::STATS_MEMORY_USAGE => isset($stats['used_memory']) ? $stats['used_memory'] : $stats['Memory']['used_memory']
        ];
    }
}
