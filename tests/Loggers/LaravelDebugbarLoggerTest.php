<?php

use Barryvdh\Debugbar\LaravelDebugbar;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\LaravelDebugbarLogger;
use Mockery as m;

class LaravelDebugbarLoggerTest extends PHPUnit_Framework_TestCase
{
    public function test_can_register()
    {
        $debugbar      = m::mock(LaravelDebugbar::class);
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);

        $configuration->shouldReceive('setSQLLogger')
                      ->once();

        $debugbar->shouldReceive('hasCollector')->with('doctrine')->once()->andReturn(false);
        $debugbar->shouldReceive('addCollector')->once();

        $logger = new LaravelDebugbarLogger($debugbar);

        $logger->register($em, $configuration);
    }

    protected function tearDown()
    {
        m::close();
    }
}
