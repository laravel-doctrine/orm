<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Query;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\AbstractPaginator;
use LaravelDoctrine\ORM\Pagination\PaginatorAdapter;
use PHPUnit\Framework\TestCase;

class PaginatorAdapterTest extends TestCase
{
    public function testMakesLaravelsPaginatorFromParams()
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM Foo f');
        $adapter = PaginatorAdapter::fromParams($query, 15, 2);

        $paginator = $adapter->make();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(2, $paginator->currentPage());
    }

    public function testMakesLaravelsPaginatorFromRequest()
    {
        AbstractPaginator::currentPageResolver(function () {
            return 13;
        });

        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM Foo f');
        $adapter = PaginatorAdapter::fromRequest($query);

        $paginator = $adapter->make();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(13, $paginator->currentPage());
    }

    public function testQueryParametersAreProducedInUrlFromParams()
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM Foo f');
        $adapter = PaginatorAdapter::fromParams($query, 15, 2, false)
            ->queryParams(['foo' => 'bar']);

        $paginator = $adapter->make();

        $this->assertStringContainsString('foo=bar', $paginator->url(1));
    }

    public function testQueryParametersAreProducedInUrlFromRequest()
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM Foo f');
        $adapter = PaginatorAdapter::fromRequest($query)
            ->queryParams(['foo' => 'bar']);

        $paginator = $adapter->make();

        $this->assertStringContainsString('foo=bar', $paginator->url(1));
    }

    /**
     * @return EntityManagerInterface|\Mockery\Mock
     */
    private function mockEntityManager()
    {
        /** @var EntityManagerInterface|\Mockery\Mock $em */
        $em         = \Mockery::mock(EntityManagerInterface::class);
        $config     = \Mockery::mock(Configuration::class);
        $metadata   = \Mockery::mock(ClassMetadata::class);
        $connection = \Mockery::mock(Connection::class);
        $platform   = \Mockery::mock(AbstractPlatform::class);
        $hydrator   = \Mockery::mock(AbstractHydrator::class);

        $result = \Mockery::mock(\Doctrine\DBAL\Result::class);

        $config->shouldReceive('getDefaultQueryHints')->andReturn([]);
        $config->shouldReceive('isSecondLevelCacheEnabled')->andReturn(false);
        $config->shouldReceive('getQueryCacheImpl')->andReturn(null);
        $config->shouldReceive('getQueryCache')->andReturn(null);
        $config->shouldReceive('getQuoteStrategy')->andReturn(new DefaultQuoteStrategy);

        $metadata->fieldMappings = [
            'id' => [
                'fieldName'  => 'id',
                'columnName' => 'id',
                'type'       => Types::INTEGER,
                'id'         => true,
                'options'    => ['unsigned' => true],
            ],
            'name' => [
                'fieldName'  => 'name',
                'columnName' => 'name',
                'type'       => Types::STRING,
            ],
        ];

        $metadata->subClasses                = [];
        $metadata->name                      = 'Foo';
        $metadata->containsForeignIdentifier = false;
        $metadata->identifier                = ['id'];

        $metadata->table = [
            'name'              => 'foos',
            'schema'            => '',
            'indexes'           => [],
            'uniqueConstraints' => []
        ];

        $metadata->shouldReceive('isInheritanceTypeSingleTable')->andReturn(false);
        $metadata->shouldReceive('isInheritanceTypeJoined')->andReturn(false);
        $metadata->shouldReceive('getTableName')->andReturn('fooes');
        $metadata->shouldReceive('getTypeOfField')->andReturn(Types::INTEGER);

        $connection->shouldReceive('getDatabasePlatform')->andReturn($platform);
        $connection->shouldReceive('executeQuery')->andReturn($result);
        $connection->shouldReceive('getParams')->andReturn([]);

        $platform->shouldReceive('appendLockHint')->andReturnUsing(function ($a) {
            return $a;
        });
        $platform->shouldReceive('getMaxIdentifierLength')->andReturn(PHP_INT_MAX);
        $platform->shouldReceive('getSQLResultCasing')->andReturnUsing(function ($a) {
            return $a;
        });
        $platform->shouldReceive('getName')->andReturn('You shouldnt care');
        $platform->shouldReceive('getCountExpression')->andReturnUsing(function ($col) {
            return "COUNT($col)";
        });
        $platform->shouldReceive('supportsLimitOffset')->andReturn(true);

        $hydrator->shouldReceive('hydrateAll')->andReturn([]);

        $em->shouldReceive('getConfiguration')->andReturn($config);
        $em->shouldReceive('getClassMetadata')->with('Foo')->andReturn($metadata);
        $em->shouldReceive('getConnection')->andReturn($connection);
        $em->shouldReceive('hasFilters')->andReturn(false);
        $em->shouldReceive('newHydrator')->andReturn($hydrator);

        return $em;
    }
}

class Foo
{
    private $id;

    private $name;
}
