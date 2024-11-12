<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Configuration\Connections;

use LaravelDoctrine\ORM\Configuration\Manager;

class ConnectionManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return 'mysql';
    }

    public function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function getClassSuffix(): string
    {
        return 'Connection';
    }
}
