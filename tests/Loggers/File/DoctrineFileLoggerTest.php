<?php

use LaravelDoctrine\ORM\Loggers\File\DoctrineFileLogger;
use Mockery as m;
use Mockery\Mock;
use Psr\Log\LoggerInterface as Log;

class DoctrineFileLoggerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineFileLogger
     */
    protected $logger;

    /**
     * @var Mock
     */
    protected $writer;

    protected function setUp()
    {
        $this->writer = m::mock(Log::class);
        $this->logger = new DoctrineFileLogger(
            $this->writer
        );
    }

    public function test_transforms_debugstack_query_log_to_clockwork_compatible_array()
    {
        $this->writer->shouldReceive('debug')->once();

        $this->logger->startQuery('SELECT * FROM table WHERE condition = ?', ['value']);
        $this->logger->stopQuery();

        $this->assertEquals('SELECT * FROM table WHERE condition = "value"', $this->logger->getQuery());
    }

    protected function tearDown()
    {
        m::close();
    }
}
