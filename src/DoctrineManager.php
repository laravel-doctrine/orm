<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
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
     * @param string          $connection
     * @param string|callable $callback
     */
    public function extend($connection, $callback)
    {
        $manager = $this->registry->getConnection($connection);

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
}
