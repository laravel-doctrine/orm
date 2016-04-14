<?php

namespace LaravelDoctrine\ORM\Resolvers;

use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Illuminate\Contracts\Container\Container;

class EntityListenerResolver implements ResolverContract
{
    /**
     * @var Container
     */
    private $container;

    private $instances = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Clear all instances from the set, or a specific class when given.
     *
     * @param string $className The fully-qualified class name
     *
     * @return void
     */
    public function clear($className = null)
    {
        if ($className) {
            unset($this->instances[$className = trim($className, '\\')]);

            return;
        }

        $this->instances = [];
    }

    /**
     * Returns a entity listener instance for the given class name.
     *
     * @param string $className The fully-qualified class name
     *
     * @return object An entity listener
     */
    public function resolve($className)
    {
        if (isset($this->instances[$className = trim($className, '\\')])) {
            return $this->instances[$className];
        }

        return $this->instances[$className] = $this->container->make($className);
    }

    /**
     * Register a entity listener instance.
     *
     * @param object $object An entity listener
     */
    public function register($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('An object was expected, but got "%s".', gettype($object)));
        }

        $this->instances[get_class($object)] = $object;
    }
}
