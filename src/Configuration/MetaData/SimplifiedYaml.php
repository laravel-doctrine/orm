<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;

class SimplifiedYaml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new SimplifiedYamlDriver(
            array_get($settings, 'paths'),
            array_get($settings, 'extension', SimplifiedYamlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
