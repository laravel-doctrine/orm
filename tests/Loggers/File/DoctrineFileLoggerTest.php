<?php

use Doctrine\DBAL\Connection;
use LaravelDoctrine\ORM\Loggers\File\DoctrineFileLogger;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as Log;

class DoctrineFileLoggerTest extends TestCase
{
    /**
     * @var DoctrineFileLogger
     */
    protected $logger;

    /**
     * @var Mock
     */
    protected $writer;

    /**
     * @var Mock
     */
    protected $connection;

    protected function setUp()
    {
        $this->writer     = m::mock(Log::class);
        $this->connection = m::mock(Connection::class);
        $this->logger     = new DoctrineFileLogger(
            $this->writer,
            $this->connection
        );
    }

    public function test_transforms_debugstack_query_log_to_clockwork_compatible_array()
    {
        $this->writer->shouldReceive('debug')->once();
        $this->connection->shouldReceive('getDatabasePlatform')->once();

        $this->logger->startQuery('SELECT * FROM table WHERE condition = ?', ['value']);
        $this->logger->stopQuery();

        $this->assertEquals('SELECT * FROM table WHERE condition = "value"', $this->logger->getQuery());
    }

    protected function tearDown()
    {
        m::close();
    }
}
