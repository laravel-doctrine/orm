<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\SQL\DoctrineSQLLogger;

class LaravelEventLogger implements Logger
{
    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $logger = new DoctrineSQLLogger($em, $configuration);
        $configuration->setSQLLogger($logger);
    }
}
