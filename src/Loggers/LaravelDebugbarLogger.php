<?php

namespace LaravelDoctrine\ORM\Loggers;

use Barryvdh\Debugbar\LaravelDebugbar;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Config\Repository;
use LaravelDoctrine\ORM\Loggers\Debugbar\DoctrineCollector;

class LaravelDebugbarLogger implements Logger
{
    /**
     * @var LaravelDebugbar
     */
    protected $debugbar;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @param LaravelDebugbar $debugbar
     * @param Repository      $config
     */
    public function __construct(LaravelDebugbar $debugbar, Repository $config)
    {
        $this->debugbar = $debugbar;
        $this->config   = $config;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Configuration          $configuration
     */
    public function register(EntityManagerInterface $em, Configuration $configuration)
    {
        if ($this->debugbar->hasCollector('doctrine')) {
            $debugStack = $this->debugbar->getCollector('doctrine')->getDebugStack();
        } else {
            $debugStack = new DebugStack;

            $this->debugbar->addCollector(
                new DoctrineCollector($debugStack, $this->config->get('doctrine.debugbar_widget_key', 'queries'))
            );
        }

        $configuration->setSQLLogger($debugStack);
    }
}
