<?php

namespace LaravelDoctrine\ORM\Configuration;

use Doctrine\DBAL\Types\Type;

/**
 * Class CustomTypeManager
 * @package LaravelDoctrine\ORM\Configuration
 */
class CustomTypeManager {

    /**
     * @param $name
     * @param $class
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addType($name, $class)
    {
        if (!Type::hasType($name)) {
            Type::addType($name, $class);
        } else {
            Type::overrideType($name, $class);
        }
    }

    /**
     * @param array $typeMap
     */
    public function addCustomTypes(array $typeMap)
    {
        foreach($typeMap as $name => $class)
        {
            $this->addType($name, $class);
        }
    }

}