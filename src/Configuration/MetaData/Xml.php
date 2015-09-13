<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Tools\Setup;

class Xml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\ORM\Configuration|mixed
     */
    public function resolve(array $settings = [])
    {
        return Setup::createXMLMetadataConfiguration(
            array_get($settings, 'paths'),
            array_get($settings, 'dev'),
            array_get($settings, 'proxies.path'),
            $this->cache->driver()
        );
    }
}
