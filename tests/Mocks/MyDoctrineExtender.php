<?php

namespace LaravelDoctrine\Tests\Mocks;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\DoctrineExtender;
use LaravelDoctrine\Tests\DoctrineManagerTest;

class MyDoctrineExtender implements DoctrineExtender
{
    /**
     * @param Configuration $configuration
     * @param Connection $connection
     * @param EventManager $eventManager
     */
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager)
    {
        (new DoctrineManagerTest)->assertExtendedCorrectly($configuration, $connection, $eventManager);
    }
}