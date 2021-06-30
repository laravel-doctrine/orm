<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Illuminate\Support\Arr;

class Attributes extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new AttributeDriver(
            Arr::get($settings, 'paths')
        );
    }
}
