<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Assets;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use LaravelDoctrine\ORM\DoctrineExtender;
use LaravelDoctrineTest\ORM\Feature\DoctrineManagerTest;

class MyDoctrineExtender implements DoctrineExtender
{
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager): void
    {
        (new DoctrineManagerTest('test'))->assertExtendedCorrectly($configuration, $connection, $eventManager);
    }
}
