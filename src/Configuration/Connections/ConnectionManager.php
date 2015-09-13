<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use LaravelDoctrine\ORM\Configuration\Manager;

class ConnectionManager extends Manager
{
    /**
     * Get the default driver name.
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'mysql';
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * @return string
     */
    public function getClassSuffix()
    {
        return 'Connection';
    }
}
