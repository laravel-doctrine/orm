<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration;

use Doctrine\DBAL\Types\Type;

class CustomTypeManager
{
    public function addType(string $name, mixed $class): void
    {
        if (! Type::hasType($name)) {
            Type::addType($name, $class);
        } else {
            Type::overrideType($name, $class);
        }
    }

    /** @param mixed[] $typeMap */
    public function addCustomTypes(array $typeMap): void
    {
        foreach ($typeMap as $name => $class) {
            $this->addType($name, $class);
        }
    }

    public function getType(mixed $type): Type
    {
        return Type::getType($type);
    }
}
