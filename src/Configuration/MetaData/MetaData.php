<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use LaravelDoctrine\ORM\Configuration\Driver;

abstract class MetaData implements Driver
{
    public function getClassMetadataFactoryName(): string
    {
        return ClassMetadataFactory::class;
    }
}
