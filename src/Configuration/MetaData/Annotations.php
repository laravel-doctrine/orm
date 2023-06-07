<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\ORMSetup;
use Illuminate\Support\Arr;

/**
 * @deprecated
 */
class Annotations extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return ORMSetup::createDefaultAnnotationDriver(
            Arr::get($settings, 'paths', [])
        );
    }
}
