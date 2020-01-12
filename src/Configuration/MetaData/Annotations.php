<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Configuration;
use Illuminate\Support\Arr;

class Annotations extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return (new Configuration())->newDefaultAnnotationDriver(
            Arr::get($settings, 'paths', []),
            Arr::get($settings, 'simple', false)
        );
    }
}
