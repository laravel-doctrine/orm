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
            static::hydrateReflection($reflection, $instance, $field, $value);
        }

        return $instance;
    }

    /**
     * @param ReflectionClass $reflection
     * @param object          $instance
     * @param string          $field
     * @param mixed           $value
     */
    private static function hydrateReflection(ReflectionClass $reflection, $instance, $field, $value)
    {
        if ($reflection->hasProperty($field)) {
            $property = $reflection->getProperty($field);
            $property->setAccessible(true);
            $property->setValue($instance, $value);
        } elseif ($parent = $reflection->getParentClass()) {
            self::hydrateReflection($parent, $instance, $field, $value);
        }
    }
}
