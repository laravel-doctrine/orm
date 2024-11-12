<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Configuration\Driver;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

use function storage_path;

class PhpFileCacheProvider implements Driver
{
    public function __construct(protected Repository $config)
    {
    }

    /** @param mixed[] $settings */
    public function resolve(array $settings = []): CacheItemPoolInterface
    {
        $path      = $settings['path'] ?? $this->config->get('cache.stores.file.path', storage_path('framework/cache'));
        $namespace = $settings['namespace'] ?? $this->config->get('doctrine.cache.namespace', 'doctrine-cache');

        return new PhpFilesAdapter(
            $namespace,
            0,
            $path,
        );
    }
}
