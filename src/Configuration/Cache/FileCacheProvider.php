<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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
     * @return CacheItemPoolInterface
     */
    public function resolve(array $settings = [])
    {
        $path = $settings['path'] ?? $this->config->get('cache.stores.file.path', storage_path('framework/cache'));
        $namespace = $settings['namespace'] ?? $this->config->get('doctrine.cache.namespace', 'doctrine-cache');

        return new FilesystemAdapter(
            $namespace,
            0,
            $path
        );
    }
}
