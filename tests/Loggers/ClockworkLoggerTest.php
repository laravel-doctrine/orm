<?php

use Clockwork\Clockwork;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\ClockworkLogger;
use Mockery as m;

class ClockworkLoggerTest extends PHPUnit_Framework_TestCase
{
    public function test_can_register()
    {
        $clockwork     = m::mock(Clockwork::class);
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);
        $connection    = m::mock(Connection::class);
        $driver        = m::mock(Driver::class);

        $em->shouldReceive('getConnection')->andReturn($connection);
        $connection->shouldReceive('getDriver')->andReturn($driver);
        $driver->shouldReceive('getName')->andReturn('mysql');

        $configuration->shouldReceive('setSQLLogger')
                      ->once();

        $clockwork->shouldReceive('addDataSource')->once();

        $logger = new ClockworkLogger($clockwork);

        $logger->register($em, $configuration);
    }
}
