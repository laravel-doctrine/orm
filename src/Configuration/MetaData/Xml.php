<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Support\Arr;

class Xml extends MetaData
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): MappingDriver
    {
        return new XmlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', XmlDriver::DEFAULT_FILE_EXTENSION),
        );
    }
}
