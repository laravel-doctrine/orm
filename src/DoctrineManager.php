<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class DoctrineManager
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container       $container
     * @param ManagerRegistry $registry
     */
    public function __construct(Container $container, ManagerRegistry $registry)
    {
        $this->registry  = $registry;
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDefaultManagerName()
    {
        return $this->registry->getDefaultManagerName();
    }

    /**
     * @param string          $connection
     * @param string|callable $callback
     */
    public function extend($connection, $callback)
    {
        $manager = $this->registry->getManager($connection);

        if (!is_callable($callback)) {
            if (!class_exists($callback)) {
                throw new InvalidArgumentException("DoctrineExtender {$callback} does not exist");
            }

            $callback = [$this->container->make($callback), 'extend'];
        }

        if (!is_callable($callback)) {
            throw new InvalidArgumentException("No valid extend callback is given. Either pass a class or closure");
        }

        call_user_func_array($callback, [
            $manager->getConfiguration(),
            $manager->getConnection(),
            $manager->getEventManager()
        ]);
    }

    /**
     * @param string|callable $callback
     */
    public function extendAll($callback)
    {
        foreach ($this->registry->getManagerNames() as $connection) {
            $this->extend($connection, $callback);
        }
    }

    /**
     * @param            $namespace
     * @param bool|false $connection
     */
    public function addNamespace($namespace, $connection = false)
    {
        $connections = $connection ? [$connection] : $this->registry->getManagerNames();

        foreach ($connections as $connection) {
            $this->getMetaDataDriver($connection)->addNamespace($namespace);
        }
    }

    /**
     * @param array      $paths
     * @param bool|false $connection
     */
    public function addPaths(array $paths = [], $connection = false)
    {
        $connections = $connection ? [$connection] : $this->registry->getManagerNames();

        foreach ($connections as $connection) {
            $this->getMetaDataDriver($connection)->addPaths($paths);
        }
    }

    /**
     * @param null $connection
     *
     * @return MappingDriver
     */
    public function getMetaDataDriver($connection = null)
    {
        $manager = $this->registry->getManager($connection);

        return $manager->getConfiguration()->getMetadataDriverImpl();
    }
}
