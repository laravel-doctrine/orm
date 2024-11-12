<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Pagination;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Result;
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
use LaravelDoctrineTest\ORM\TestCase;
use Mockery;
use Mockery\Mock;
use stdClass;

use function assert;

use const PHP_INT_MAX;

class PaginatorAdapterTest extends TestCase
{
    public function testMakesLaravelsPaginatorFromParams(): void
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM LaravelDoctrineTest\ORM\Assets\Entity\Foo f');
        $adapter = PaginatorAdapter::fromParams($query, 15, 2);

        $paginator = $adapter->make();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(2, $paginator->currentPage());
    }

    public function testMakesLaravelsPaginatorFromRequest(): void
    {
        AbstractPaginator::currentPageResolver(static function () {
            return 13;
        });

        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM LaravelDoctrineTest\ORM\Assets\Entity\Foo f');
        $adapter = PaginatorAdapter::fromRequest($query);

        $paginator = $adapter->make();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(13, $paginator->currentPage());
    }

    public function testQueryParametersAreProducedInUrlFromParams(): void
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM LaravelDoctrineTest\ORM\Assets\Entity\Foo f');
        $adapter = PaginatorAdapter::fromParams($query, 15, 2, false)
            ->queryParams(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $adapter->getQueryParams());

        $paginator = $adapter->make();

        $this->assertStringContainsString('foo=bar', $paginator->url(1));
    }

    public function testQueryParametersAreProducedInUrlFromRequest(): void
    {
        $em      = $this->mockEntityManager();
        $query   = (new Query($em))->setDQL('SELECT f FROM LaravelDoctrineTest\ORM\Assets\Entity\Foo f');
        $adapter = PaginatorAdapter::fromRequest($query)
            ->queryParams(['foo' => 'bar']);

        $paginator = $adapter->make();

        $this->assertStringContainsString('foo=bar', $paginator->url(1));
    }

    private function mockEntityManager(): EntityManagerInterface
    {
        $em = Mockery::mock(EntityManagerInterface::class);
        assert($em instanceof EntityManagerInterface || $em instanceof Mock);
        $config     = Mockery::mock(Configuration::class);
        $metadata   = Mockery::mock(ClassMetadata::class);
        $connection = Mockery::mock(Connection::class);
        $platform   = Mockery::mock(AbstractPlatform::class);
        $hydrator   = Mockery::mock(AbstractHydrator::class);

        $config->shouldReceive('getDefaultQueryHints')->andReturn([]);
        $config->shouldReceive('isSecondLevelCacheEnabled')->andReturn(false);
        $config->shouldReceive('getQueryCacheImpl')->andReturn(null);
        $config->shouldReceive('getQueryCache')->andReturn(null);
        $config->shouldReceive('getQuoteStrategy')->andReturn(new DefaultQuoteStrategy());

        $id             = new stdClass();
        $id->fieldName  = 'id';
        $id->columnName = 'id';
        $id->type       = Types::INTEGER;
        $id->id         = true;
        $id->options    = ['unsigned' => true];

        $name             = new stdClass();
        $name->fieldName  = 'name';
        $name->columnName = 'name';
        $name->type       = Types::STRING;

        $metadata->fieldMappings = [
            'id' => $id,
            'name' => $name,
        ];

        $metadata->subClasses                = [];
        $metadata->name                      = 'Foo';
        $metadata->containsForeignIdentifier = false;
        $metadata->identifier                = ['id'];

        $metadata->table = [
            'name'              => 'foos',
            'schema'            => '',
            'indexes'           => [],
            'uniqueConstraints' => [],
        ];

        $metadata->shouldReceive('isInheritanceTypeSingleTable')->andReturn(false);
        $metadata->shouldReceive('isInheritanceTypeJoined')->andReturn(false);
        $metadata->shouldReceive('getTableName')->andReturn('fooes');
        $metadata->shouldReceive('getTypeOfField')->andReturn(Types::INTEGER);

        $connection->shouldReceive('getDatabasePlatform')->andReturn($platform);
        $connection->shouldReceive('executeQuery')->andReturn($this->createMock(Result::class));
        $connection->shouldReceive('getParams')->andReturn([]);

        $platform->shouldReceive('appendLockHint')->andReturnUsing(static function ($a) {
            return $a;
        });
        $platform->shouldReceive('getMaxIdentifierLength')->andReturn(PHP_INT_MAX);
        $platform->shouldReceive('getSQLResultCasing')->andReturnUsing(static function ($a) {
            return $a;
        });
        $platform->shouldReceive('getName')->andReturn('You shouldnt care');
        $platform->shouldReceive('getCountExpression')->andReturnUsing(static function ($col) {
            return 'COUNT(' . $col . ')';
        });
        $platform->shouldReceive('supportsLimitOffset')->andReturn(true);

        $hydrator->shouldReceive('hydrateAll')->andReturn([]);

        $em->shouldReceive('getConfiguration')->andReturn($config);
        $em->shouldReceive('getClassMetadata')->with('LaravelDoctrineTest\ORM\Assets\Entity\Foo')->andReturn($metadata);
        $em->shouldReceive('getConnection')->andReturn($connection);
        $em->shouldReceive('hasFilters')->andReturn(false);
        $em->shouldReceive('newHydrator')->andReturn($hydrator);

        return $em;
    }
}
