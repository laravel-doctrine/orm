<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Testing;

use ReflectionClass;

class SimpleHydrator
{
    /** @param mixed[] $attributes */
    public static function hydrate(mixed $class, array $attributes = []): object
    {
        $reflection = new ReflectionClass($class);
        $instance   = $reflection->newInstanceWithoutConstructor();

        foreach ($attributes as $field => $value) {
            static::hydrateReflection($reflection, $instance, $field, $value);
        }

        return $instance;
    }

    private static function hydrateReflection(ReflectionClass $reflection, object $instance, string $field, mixed $value): void
    {
        if ($reflection->hasProperty($field)) {
            $property = $reflection->getProperty($field);
            $property->setAccessible(true);
            $property->setValue($instance, $value);
        } else {
            $parent = $reflection->getParentClass();
            if ($parent) {
                self::hydrateReflection($parent, $instance, $field, $value);
            }
        }
    }
}
