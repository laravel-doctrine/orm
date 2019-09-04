<?php

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\EchoLogger;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class EchoLoggerTest extends TestCase
{
    public function test_can_register()
    {
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);

        $configuration->shouldReceive('setSQLLogger')
                      ->once();

        $logger = new EchoLogger();

        $logger->register($em, $configuration);

        $this->assertTrue(true);
    }

    protected function tearDown()
    {
        m::close();
    }
}
