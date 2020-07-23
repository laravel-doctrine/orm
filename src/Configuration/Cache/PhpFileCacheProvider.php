<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Doctrine\Common\Cache\PhpFileCache;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;
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
     * @return PhpFileCache
     */
    public function resolve(array $settings = [])
    {
        $path = $settings['path'] ?? $this->config->get('cache.stores.file.path', storage_path('framework/cache'));

        return new PhpFileCache(
            $path
        );
    }
}
