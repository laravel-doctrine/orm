<?php

namespace LaravelDoctrine\ORM\Loggers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use LaravelDoctrine\ORM\Loggers\SQL\DoctrineSQLLogger;

class LaravelEventLogger implements Logger
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $logger = new DoctrineSQLLogger($em->getConnection(), $this->dispatcher);
        $configuration->setSQLLogger($logger);
    }
}
