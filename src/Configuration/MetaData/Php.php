<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Illuminate\Support\Arr;

class Php extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new PHPDriver(
            Arr::get($settings, 'paths')
        );
    }
}
