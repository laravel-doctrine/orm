<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\ApcCache as DoctrineApcCache;
use Illuminate\Cache\ApcStore;

class ApcCache extends DoctrineApcCache
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
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     *
     * @return string|bool The cached data or FALSE, if no cache entry exists for the given id.
     */
    protected function doFetch($id)
    {
        return $this->store->get($id);
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
        return (bool) (false !== $this->store->get($id));
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
}
