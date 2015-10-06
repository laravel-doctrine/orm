<?php

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use LaravelDoctrine\ORM\Loggers\FileLogger;
use Mockery as m;
use Psr\Log\LoggerInterface as Log;

class FileLoggerTest extends PHPUnit_Framework_TestCase
{
    public function test_can_register()
    {
        $writer        = m::mock(Log::class);
        $em            = m::mock(EntityManagerInterface::class);
        $configuration = m::mock(Configuration::class);

        $configuration->shouldReceive('setSQLLogger')
                      ->once();

        $logger = new FileLogger($writer);

        $logger->register($em, $configuration);
    }
}
