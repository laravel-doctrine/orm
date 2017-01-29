<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class DoctrineManager
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDefaultManagerName()
    {
        return $this->container->make('registry')->getDefaultManagerName();
    }

    /**
     * @param $callback
     */
    public function onResolve(callable $callback)
    {
        BootChain::add(function (ManagerRegistry $registry) use ($callback) {
            call_user_func_array($callback, [$registry, $this]);
        });
    }

    /**
     * @param string|null     $connection
     * @param string|callable $callback
     */
    public function extend($connection = null, $callback)
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $callback) {
            $this->callExtendOn($connection, $callback, $registry);
        });
    }

    /**
     * @param string|callable $callback
     */
    public function extendAll($callback)
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($callback) {
            foreach ($registry->getManagerNames() as $connection) {
                $this->callExtendOn($connection, $callback, $registry);
            }
        });
    }

    /**
     * @param string|null     $connection
     * @param string|callback $callback
     * @param ManagerRegistry $registry
     */
    private function callExtendOn($connection = null, $callback, ManagerRegistry $registry)
    {
        $manager = $registry->getManager($connection);

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
     * @param             $namespace
     * @param string|null $connection
     */
    public function addNamespace($namespace, $connection = null)
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $namespace) {
            $this->getMetaDataDriver($connection, $registry)->addNamespace($namespace);
        });
    }

    /**
     * @param array       $paths
     * @param string|null $connection
     */
    public function addPaths(array $paths = [], $connection = null)
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $paths) {
            $this->getMetaDataDriver($connection, $registry)->addPaths($paths);
        });
    }

    /**
     * @param array       $mappings
     * @param string|null $connection
     */
    public function addMappings(array $mappings = [], $connection = null)
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $mappings) {
            $this->getMetaDataDriver($connection, $registry)->addMappings($mappings);
        });
    }

    /**
     * @param null $connection
     *
     * @param  ManagerRegistry $registry
     * @return MappingDriver
     */
    public function getMetaDataDriver($connection = null, ManagerRegistry $registry)
    {
        $registry = $registry ?: $this->container->make('registry');
        $manager  = $registry->getManager($connection);

        return $manager->getConfiguration()->getMetadataDriverImpl();
    }
}
