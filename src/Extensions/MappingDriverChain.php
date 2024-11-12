<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Extensions;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain as DoctrineMappingDriverChain;

use function method_exists;

class MappingDriverChain extends DoctrineMappingDriverChain implements MappingDriver
{
    public function __construct(MappingDriver $driver, string $namespace)
    {
        $this->addDriver($driver, $namespace);
        $this->setDefaultDriver($driver);
    }

    /** @param mixed[] $paths */
    public function addPaths(array $paths = []): void
    {
        $driver = $this->getDefaultDriver();

        if (! method_exists($driver, 'addPaths')) {
            return;
        }

        $driver->addPaths($paths);
    }

    /** @param mixed[] $mappings */
    public function addMappings(array $mappings = []): void
    {
        $driver = $this->getDefaultDriver();

        if (! method_exists($driver, 'addMappings')) {
            return;
        }

        $driver->addMappings($mappings);
    }
}
