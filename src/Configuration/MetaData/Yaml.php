<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\YamlDriver;

class Yaml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new YamlDriver(
            array_get($settings, 'paths'),
            array_get($settings, 'extension', YamlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
