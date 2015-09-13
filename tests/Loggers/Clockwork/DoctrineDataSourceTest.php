<?php

use Clockwork\Request\Request;
use Doctrine\DBAL\Logging\DebugStack;
use LaravelDoctrine\ORM\Loggers\Clockwork\DoctrineDataSource;

class DoctrineDataSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DebugStack
     */
    protected $logger;

    /**
     * @var DoctrineDataSource
     */
    protected $source;

    protected function setUp()
    {
        $this->logger          = new DebugStack;
        $this->logger->queries = [
            [
                'sql'         => 'SELECT * FROM table WHERE condition = ?',
                'params'      => ['value'],
                'executionMS' => 1
            ]
        ];

        $this->source = new DoctrineDataSource($this->logger, 'mysql');
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
