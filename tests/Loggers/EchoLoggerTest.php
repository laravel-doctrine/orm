<?php

namespace LaravelDoctrine\Tests\Loggers;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\EchoLogger;
use Mockery as m;

class EchoLoggerTest extends \PHPUnit\Framework\TestCase
{
    public function test_can_register()
    {
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);

        $configuration->shouldReceive('setSQLLogger')
                      ->once();

        $logger = new EchoLogger();

        $logger->register($em, $configuration);
    }

    protected function tearDown()
    {
        m::close();
    }
}
