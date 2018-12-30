<?php

namespace LaravelDoctrine\Tests\Mocks;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

class ObjectType extends JsonType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode(get_object_vars($value));
    }

    public function getName()
    {
        return 'object_type';
    }
}