<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Logging\Log;
use LaravelDoctrine\ORM\Loggers\File\DoctrineFileLogger;

class FileLogger implements Logger
{
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $logger = new DoctrineFileLogger($this->logger);
        $configuration->setSQLLogger($logger);
    }
}
