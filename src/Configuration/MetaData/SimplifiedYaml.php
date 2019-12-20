<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Illuminate\Support\Arr;

class SimplifiedYaml extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new SimplifiedYamlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', SimplifiedYamlDriver::DEFAULT_FILE_EXTENSION)
        );
    }
}
