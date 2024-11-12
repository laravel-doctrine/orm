<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Support\Arr;

class Attributes extends MetaData
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): MappingDriver
    {
        return new AttributeDriver(
            Arr::get($settings, 'paths'),
        );
    }
}
