<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Illuminate\Support\Arr;

class SimplifiedXml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new SimplifiedXmlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
