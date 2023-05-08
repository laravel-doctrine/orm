<?php

use Clockwork\Request\Request;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\Type;
use LaravelDoctrine\ORM\Loggers\Clockwork\DoctrineDataSource;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\Types\Types;

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

    protected function setUp(): void
    {
        $this->logger          = new DebugStack;
        $this->logger->queries = [
            [
                'sql'         => 'SELECT * FROM table WHERE condition = ?',
                'params'      => ['value'],
                'types'       => [Types::STRING],
                'executionMS' => 0.001
            ]
        ];

        $this->connection = m::mock(\Doctrine\DBAL\Connection::class);
        $this->driver     = m::mock(\Doctrine\DBAL\Driver::class);

        $this->driver->shouldReceive('getName')->once()->andReturn('mysql');

        $this->connection->shouldReceive('getDriver')->once()->andReturn($this->driver);
        $this->connection->shouldReceive('getDatabasePlatform')->once()->andReturn(\Mockery::mock(AbstractPlatform::class));

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

    public function test_transforms_a_custom_type_to_a_query(): void
    {
        Type::getTypeRegistry()->register('name', $this->getCustomType());
        $this->logger->queries = [
            [
                'sql'         => 'SELECT * FROM table WHERE condition = ?',
                'params'      => [$this->getValueObject()],
                'types'       => ['name'],
                'executionMS' => 0.001
            ]
        ];

        $request = $this->source->resolve(new Request);

        $this->assertEquals([
            [
                'query'      => 'SELECT * FROM table WHERE condition = "Asd"',
                'duration'   => 1,
                'connection' => 'mysql'
            ]
        ], $request->databaseQueries);
    }

    private function getValueObject(): object
    {
        return new class () {
            public function getName(): string
            {
                return 'Asd';
            }
        };
    }

    private function getCustomType(): Type
    {
        return new class() extends StringType
        {
            public function getName()
            {
                return 'name';
            }

            public function convertToDatabaseValue($value, AbstractPlatform $platform)
            {
                return $value->getName();
            }
        };
    }
}
