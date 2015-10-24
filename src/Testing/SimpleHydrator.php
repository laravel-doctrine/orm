<?php

namespace LaravelDoctrine\ORM\Testing;

use ReflectionClass;

class SimpleHydrator
{
    /**
     * @param       $class
     * @param array $attributes
     *
     * @return object
     */
    public static function hydrate($class, array $attributes = [])
    {
        $reflection = new ReflectionClass($class);
        $instance   = $reflection->newInstanceWithoutConstructor();

        foreach ($attributes as $field => $value) {
            if ($reflection->hasProperty($field)) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }
}
