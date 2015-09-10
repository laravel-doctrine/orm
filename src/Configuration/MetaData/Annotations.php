<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Driver;

class Annotations implements Driver
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param array $settings
     *
     * @return \Doctrine\ORM\Configuration
     */
    public function resolve(array $settings = [])
    {
        return Setup::createAnnotationMetadataConfiguration(
            array_get($settings, 'paths', []),
            array_get($settings, 'dev', false),
            array_get($settings, 'proxies.path'),
            $this->cacheManager->driver(),
            array_get($settings, 'simple', false)
        );
    }
}
