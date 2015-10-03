<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;

class Xml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new XmlDriver(
            array_get($settings, 'paths'),
            array_get($settings, 'extension', XmlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
