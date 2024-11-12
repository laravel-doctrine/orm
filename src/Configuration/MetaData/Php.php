<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use Illuminate\Support\Arr;

class Php extends MetaData
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): MappingDriver
    {
        return new PHPDriver(
            Arr::get($settings, 'paths'),
        );
    }
}
