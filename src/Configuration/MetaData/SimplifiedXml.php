<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;

class SimplifiedXml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new SimplifiedXmlDriver(
            array_get($settings, 'paths'),
            array_get($settings, 'extension', SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
