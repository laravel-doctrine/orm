<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;

class Annotations extends MetaData
{
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
            $this->cache->driver(),
            array_get($settings, 'simple', false)
        );
    }
}
