<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Illuminate\Support\Arr;

class Yaml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new YamlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', YamlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
