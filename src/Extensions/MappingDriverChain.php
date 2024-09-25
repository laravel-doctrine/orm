<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain as DoctrineMappingDriverChain;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;

class MappingDriverChain extends DoctrineMappingDriverChain implements MappingDriver
{
    /**
     * @param MappingDriver $driver
     * @param               $namespace
     */
    public function __construct(MappingDriver $driver, $namespace)
    {
        $this->addDriver($driver, $namespace);
        $this->setDefaultDriver($driver);
    }

    /**
     * @param array $paths
     */
    public function addPaths(array $paths = [])
    {
        $driver = $this->getDefaultDriver();

        if (method_exists($driver, 'addPaths')) {
            $driver->addPaths($paths);
        }
    }

    /**
     * @param array $mappings
     */
    public function addMappings(array $mappings = [])
    {
        $driver = $this->getDefaultDriver();

        if (method_exists($driver, 'addMappings')) {
            $driver->addMappings($mappings);
        }
    }
}
