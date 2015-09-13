<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;

interface DoctrineExtender
{
    /**
     * @param Configuration $configuration
     * @param Connection    $connection
     * @param EventManager  $eventManager
     */
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager);
}
