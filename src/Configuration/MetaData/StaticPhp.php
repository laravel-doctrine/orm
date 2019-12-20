<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Illuminate\Support\Arr;

class StaticPhp extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new StaticPHPDriver(
            Arr::get($settings, 'paths')
        );
    }
}
