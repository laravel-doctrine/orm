<?php

namespace LaravelDoctrine\ORM\Loggers;

use Clockwork\Clockwork;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\Clockwork\DoctrineDataSource;

class ClockworkLogger implements Logger
{
    /**
     * @var Clockwork
     */
    protected $clockwork;

    /**
     * @param Clockwork $clockwork
     */
    public function __construct(Clockwork $clockwork)
    {
        $this->clockwork = $clockwork;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        $debugStack = new DebugStack;
        $configuration->setSQLLogger($debugStack);
        $this->clockwork->addDataSource(
            new DoctrineDataSource(
                $debugStack,
                $em->getConnection()->getDriver()->getName()
            )
        );
    }
}
