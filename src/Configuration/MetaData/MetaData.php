<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class MetaData implements Driver
{
    /**
     * @return string
     */
    public function getClassMetadataFactoryName()
    {
        return ClassMetadataFactory::class;
    }
}
