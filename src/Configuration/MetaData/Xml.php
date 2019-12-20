<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Illuminate\Support\Arr;

class Xml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new XmlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', XmlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
