<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Cache;

use LaravelDoctrine\ORM\Configuration\Manager;

class CacheManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->container->make('config')->get('doctrine.cache.default', 'array');
    }

    public function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function getClassSuffix(): string
    {
        return 'CacheProvider';
    }
}
