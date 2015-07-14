<?php

namespace Brouwers\LaravelDoctrine\Configuration\Cache;

use Doctrine\Common\Cache\FilesystemCache;

class FileCacheProvider extends AbstractCacheProvider
{
    /**
     * @var string
     */
    protected $name = 'file';

    /**
     * @param array $config
     *
     * @throws DriverNotFound
     * @return FileCacheProvider
     */
    public function configure($config = [])
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return FilesystemCache
     */
    public function resolve()
    {
        return new FilesystemCache($this->config['path']);
    }
}
