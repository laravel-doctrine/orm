<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Configuration;

class Annotations extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return (new Configuration())->newDefaultAnnotationDriver(
            array_get($settings, 'paths', []),
            array_get($settings, 'simple', false)
        );
    }
}
