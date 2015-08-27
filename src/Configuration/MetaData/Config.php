<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Driver;
use LaravelDoctrine\ORM\Configuration\MetaData\Config\ConfigDriver;

class Config implements Driver
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
     * @return \Doctrine\ORM\Configuration|mixed
     */
    public function resolve(array $settings = [])
    {
        $configuration = Setup::createConfiguration(
            array_get($settings, 'dev'),
            array_get($settings, 'proxies.path'),
            $this->cacheManager->driver()
        );

        $configuration->setMetadataDriverImpl(
            new ConfigDriver(
                config(array_get($settings, 'mapping_file'), [])
            )
        );

        return $configuration;
    }
}
