<?php

use Clockwork\Request\Request;
use Doctrine\DBAL\Logging\DebugStack;
use LaravelDoctrine\ORM\Loggers\Clockwork\DoctrineDataSource;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class DoctrineDataSourceTest extends TestCase
{
    /**
     * @var DebugStack
     */
    protected $logger;

    /**
     * @var DoctrineDataSource
     */
    protected $source;

    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var Mock
     */
    protected $driver;

    protected function setUp()
    {
        $this->logger          = new DebugStack;
        $this->logger->queries = [
            [
                'sql'         => 'SELECT * FROM table WHERE condition = ?',
                'params'      => ['value'],
                'executionMS' => 0.001
            ]
        ];

        $this->connection = m::mock(\Doctrine\DBAL\Connection::class);
        $this->driver     = m::mock(\Doctrine\DBAL\Driver::class);

        $this->driver->shouldReceive('getName')->once()->andReturn('mysql');

        $this->connection->shouldReceive('getDriver')->once()->andReturn($this->driver);
        $this->connection->shouldReceive('getDatabasePlatform')->once()->andReturn('mysql');

        $this->source = new DoctrineDataSource($this->logger, $this->connection);
    }

    public function test_transforms_debugstack_query_log_to_clockwork_compatible_array()
    {
        $request = $this->source->resolve(new Request);

        $this->assertEquals([
            [
                'query'      => 'SELECT * FROM table WHERE condition = "value"',
                'duration'   => 1,
                'connection' => 'mysql'
            ]
        ], $request->databaseQueries);
    }
}
