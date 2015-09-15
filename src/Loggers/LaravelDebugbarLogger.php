<?php

namespace LaravelDoctrine\ORM\Loggers;

use Barryvdh\Debugbar\LaravelDebugbar;
use DebugBar\Bridge\DoctrineCollector;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class LaravelDebugbarLogger implements Logger
{
    /**
     * @var LaravelDebugbar
     */
    protected $debugbar;

    /**
     * @param LaravelDebugbar $debugbar
     */
    public function __construct(LaravelDebugbar $debugbar)
    {
        $this->debugbar = $debugbar;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        if (!$this->debugbar->hasCollector('doctrine')) {
            $debugStack = new DebugStack;
            $configuration->setSQLLogger($debugStack);

            $this->debugbar->addCollector(
                new DoctrineCollector($debugStack)
            );
        }
    }
}
