<?php

namespace LaravelDoctrine\ORM\DBAL\Middleware\LaravelDebugbarLogging;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;

class Middleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new Driver($driver);
    }
}
