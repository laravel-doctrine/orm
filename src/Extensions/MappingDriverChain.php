<?php

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain as DoctrineMappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

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
     * @param $namespace
     */
    public function addNamespace($namespace)
    {
        $this->addDriver($this->getDefaultDriver(), $namespace);
    }

    /**
     * @param array $paths
     */
    public function addPaths(array $paths = [])
    {
        $driver = $this->getDefaultDriver();

        if (method_exists($driver, 'addPaths')) {
            $driver->addPaths($paths);
        } elseif ($driver instanceof FileDriver) {
            if ($driver->getLocator() instanceof DefaultFileLocator) {
                $driver->getLocator()->addPaths($paths);
            } elseif ($driver->getLocator() instanceof SymfonyFileLocator) {
                $driver->getLocator()->addNamespacePrefixes($paths);
            }
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

    /**
     * @return \Doctrine\Common\Annotations\Reader|null
     */
    public function getReader()
    {
        $driver = $this->getDefaultDriver();

        if ($driver instanceof AnnotationDriver) {
            return $driver->getReader();
        }
    }
}
