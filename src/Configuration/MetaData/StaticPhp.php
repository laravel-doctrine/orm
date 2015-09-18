<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;

class StaticPhp extends MetaData
{
    /**
     * @param array $settings
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    public function resolve(array $settings = [])
    {
        return new StaticPHPDriver(
            array_get($settings, 'paths')
        );
    }
}
