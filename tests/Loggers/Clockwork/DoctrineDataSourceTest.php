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
    protected $platform;

    protected function setUp(): void
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
        $this->platform     = m::mock();


        $this->connection->shouldReceive('getDatabasePlatform')->twice(2)->andReturn(new \Doctrine\DBAL\Platforms\PostgreSQLPlatform);

        $this->source = new DoctrineDataSource($this->logger, $this->connection);
    }

    public function test_transforms_debugstack_query_log_to_clockwork_compatible_array()
    {
        $request = $this->source->resolve(new Request);

        $this->assertEquals([
            [
                'query'      => 'SELECT * FROM table WHERE condition = "value"',
                'duration'   => 1,
                'connection' => 'postgresql'
            ]
        ], $request->databaseQueries);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
