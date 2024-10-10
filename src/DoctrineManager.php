<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

use function assert;
use function call_user_func_array;
use function class_exists;
use function is_callable;

class DoctrineManager
{
    public function __construct(protected Container $container)
    {
    }

    public function getDefaultManagerName(): string
    {
        return $this->container->make('registry')->getDefaultManagerName();
    }

    public function onResolve(callable $callback): void
    {
        BootChain::add(function (ManagerRegistry $registry) use ($callback): void {
            call_user_func_array($callback, [$registry, $this]);
        });
    }

    public function extend(string|null $connection, string|callable $callback): void
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $callback): void {
            $this->callExtendOn($connection, $callback, $registry);
        });
    }

    public function extendAll(string|callable $callback): void
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($callback): void {
            foreach ($registry->getManagerNames() as $connection) {
                $this->callExtendOn($connection, $callback, $registry);
            }
        });
    }

    private function callExtendOn(string|null $connection, string|callable $callback, ManagerRegistry $registry): void
    {
        $manager = $registry->getManager($connection);
        assert($manager instanceof EntityManagerInterface);

        if (! is_callable($callback)) {
            if (! class_exists($callback)) {
                throw new InvalidArgumentException('DoctrineExtender ' . $callback . ' does not exist');
            }

            $callback = [$this->container->make($callback), 'extend'];
        }

        if (! is_callable($callback)) {
            throw new InvalidArgumentException('No valid extend callback is given. Either pass a class or closure');
        }

        call_user_func_array($callback, [
            $manager->getConfiguration(),
            $manager->getConnection(),
            $manager->getEventManager(),
        ]);
    }

    /** @param mixed[] $paths */
    public function addPaths(array $paths = [], string|null $connection = null): void
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $paths): void {
            $this->getMetaDataDriver($connection, $registry)->addPaths($paths);
        });
    }

    /** @param mixed[] $mappings */
    public function addMappings(array $mappings = [], string|null $connection = null): void
    {
        $this->onResolve(function (ManagerRegistry $registry) use ($connection, $mappings): void {
            $this->getMetaDataDriver($connection, $registry)->addMappings($mappings);
        });
    }

    public function getMetaDataDriver(string|null $connection, ManagerRegistry $registry): MappingDriver
    {
        $registry = $registry ?: $this->container->make('registry');
        $manager  = $registry->getManager($connection);

        return $manager->getConfiguration()->getMetadataDriverImpl();
    }
}
