<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\FilesystemCache;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;

class FileCacheProvider implements Driver
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $settings
     *
     * @return FilesystemCache
     */
    public function resolve(array $settings = [])
    {
        return new FilesystemCache(
            $this->config->get('cache.stores.file.path', storage_path('framework/cache'))
        );
    }
}
