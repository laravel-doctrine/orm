<?php

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Events\Dispatcher;
use LaravelDoctrine\ORM\Loggers\LaravelEventLogger;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LaravelEventLoggerTest extends TestCase
{
    public function test_can_register()
    {
        $writer        = m::mock(Dispatcher::class);
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);

        $configuration->shouldReceive('setSQLLogger')
            ->once();

        $em->shouldReceive('getConnection')
            ->once()->andReturn(m::mock(Connection::class));

        $logger = new LaravelEventLogger($writer);

        $logger->register($em, $configuration);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
