<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Illuminate\Support\Arr;

class StaticPhp extends MetaData
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): MappingDriver
    {
        return new StaticPHPDriver(
            Arr::get($settings, 'paths'),
        );
    }
}
