<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Illuminate\Support\Arr;

class Php extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new PHPDriver(
            Arr::get($settings, 'paths')
        );
    }
}
