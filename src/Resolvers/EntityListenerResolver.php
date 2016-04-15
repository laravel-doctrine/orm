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

    /**
     * @var object[] Map of class name to entity listener instances.
     */
    private $instances = [];

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function resolve($className)
    {
        if (isset($this->instances[$className = trim($className, '\\')])) {
            return $this->instances[$className];
        }

        return $this->instances[$className] = $this->container->make($className);
    }

    /**
     * {@inheritdoc}
     */
    public function register($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('An object was expected, but got "%s".', gettype($object)));
        }

        $this->instances[get_class($object)] = $object;
    }
}
