<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class EchoLogger implements Logger
{
    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $configuration->setSQLLogger(new EchoSQLLogger());
    }
}
