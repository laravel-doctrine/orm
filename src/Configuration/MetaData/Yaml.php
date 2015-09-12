<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;

class Yaml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\ORM\Configuration
     */
    public function resolve(array $settings = [])
    {
        return Setup::createYAMLMetadataConfiguration(
            array_get($settings, 'paths'),
            array_get($settings, 'dev'),
            array_get($settings, 'proxies.path'),
            $this->cache->driver()
        );
    }
}
