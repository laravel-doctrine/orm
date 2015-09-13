<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Tools\Setup;

class StaticPhp extends MetaData
{
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
            $this->cache->driver()
        );

        $configuration->setMetadataDriverImpl(
            new StaticPHPDriver(
                array_get($settings, 'paths')
            )
        );

        return $configuration;
    }
}
