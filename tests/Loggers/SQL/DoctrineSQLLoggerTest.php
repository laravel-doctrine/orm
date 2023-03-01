<?php

use Doctrine\DBAL\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Events\Dispatcher;
use LaravelDoctrine\ORM\Loggers\SQL\DoctrineSQLLogger;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class DoctrineSQLLoggerTest extends TestCase
{
    /**
     * @var DoctrineSQLLogger
     */
    protected $logger;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Mock
     */
    protected $connection;

    protected function getConnectionMock()
    {
        $mock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->any())
            ->method('getWrappedConnection')
            ->will($this->returnValue(new PDO('sqlite::memory:')));

        return $mock;
    }

    protected function setUp(): void
    {
        $this->connection = $this->getConnectionMock();
        $this->dispatcher = new Dispatcher();

        $this->logger = new DoctrineSQLLogger(
            $this->connection,
            $this->dispatcher,
        );
    }

    public function test_transforms_query_to_event()
    {
        $query  = 'SELECT * FROM table WHERE condition = ?';
        $params = ['value'];

        $this->dispatcher->listen(QueryExecuted::class, function ($event) use ($query, $params) {
            $this->assertInstanceOf(QueryExecuted::class, $event);
            $this->assertEquals($query, $event->sql);
            $this->assertEquals($params, $event->bindings);
            $this->assertGreaterThan(0, $event->time);
        });

        $this->logger->startQuery($query, $params);
        usleep(10);
        $this->logger->stopQuery();
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
