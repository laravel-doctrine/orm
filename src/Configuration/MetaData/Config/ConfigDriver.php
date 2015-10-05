<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData\Config;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;

class ConfigDriver extends YamlDriver implements MappingDriver
{
    /**
     * @var array
     */
    protected $mappings = [];

    /**
     * @param array $mappings
     */
    public function __construct(array $mappings = [])
    {
        $this->mappings = $mappings;
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames()
    {
        return array_keys($this->mappings);
    }

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
     *
     * @param string $className
     *
     * @return bool
     */
    public function isTransient($className)
    {
        return !array_key_exists($className, $this->mappings);
    }

    /**
     * Gets the element of schema meta data for the class from the mapping file.
     * This will lazily load the mapping file if it is not loaded yet.
     *
     * @param string $className
     *
     * @throws MappingException
     * @return array            The element of schema meta data.
     */
    public function getElement($className)
    {
        return array_get($this->mappings, $className);
    }
}
