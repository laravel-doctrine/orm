<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;

interface DoctrineExtender
{
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager): void;
}
