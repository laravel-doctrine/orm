<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Support\Arr;

class SimplifiedXml extends MetaData
{
    /** @param mixed[] $settings */
    public function resolve(array $settings = []): MappingDriver
    {
        return new SimplifiedXmlDriver(
            Arr::get($settings, 'paths'),
            Arr::get($settings, 'extension', SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION),
        );
    }
}
