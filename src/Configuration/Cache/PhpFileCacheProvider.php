<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\PhpFileCache;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use function storage_path;

class PhpFileCacheProvider implements Driver
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
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    public function resolve(array $settings = [])
    {
        $namespace = $settings['namespace'] ?? $this->config->get('doctrine.cache.namespace', 'doctrine-cache');

        return new PhpFilesAdapter(
            $namespace
        );
    }
}
