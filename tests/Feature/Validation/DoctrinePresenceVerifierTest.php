<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Validation;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;
use LaravelDoctrineTest\ORM\Assets\Mock\CountableEntityMock;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class DoctrinePresenceVerifierTest extends TestCase
{
    protected ManagerRegistry $registry;
    protected DoctrinePresenceVerifier $verifier;
    protected EntityManagerInterface $em;
    protected QueryBuilder $builder;
    protected Query $query;

    protected function setUp(): void
    {
        $this->em       = m::mock(EntityManagerInterface::class);
        $this->registry = m::mock(ManagerRegistry::class);
        $this->builder  = m::mock(QueryBuilder::class);
        $this->query    = m::mock(Query::class);

        $this->verifier = new DoctrinePresenceVerifier(
            $this->registry,
        );

        parent::setUp();
    }

    public function testCanGetCount(): void
    {
        $this->defaultGetCountMocks();

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com');

        $this->assertTrue(true);
    }

    public function testCanGetCountWithExcludedIds(): void
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.id <> :id');

        $this->query->shouldReceive('setParameter')->once()->with('id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1);

        $this->assertTrue(true);
    }

    public function testCanGetCountWithExclucdedIdsWithCustomIdColumn(): void
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.new_id <> :new_id');

        $this->query->shouldReceive('setParameter')->once()->with('new_id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1, 'new_id');

        $this->assertTrue(true);
    }

    public function testCanGetCountWithExtraConditions(): void
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition1 = :condition1');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition2 = :condition2');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition3 != :condition3');

        $this->builder->shouldReceive('setParameter')->once()->with('condition1', 'value1');
        $this->builder->shouldReceive('setParameter')->once()->with('condition2', 'value2');
        $this->builder->shouldReceive('setParameter')->once()->with('condition3', 'value3');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', null, null, [
            'condition1' => 'value1',
            'condition2' => 'value2',
            'condition3' => '!value3',
        ]);

        $this->assertTrue(true);
    }

    public function testCanGetCountWithExtraConditionsWithNull(): void
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition1 = :condition1');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition2 = :condition2');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition3 IS NULL');

        $this->builder->shouldReceive('setParameter')->once()->with('condition1', 'value1');
        $this->builder->shouldReceive('setParameter')->once()->with('condition2', 'value2');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', null, null, [
            'condition1' => 'value1',
            'condition2' => 'value2',
            'condition3' => 'NULL',
        ]);

        $this->assertTrue(true);
    }

    public function testCanGetCountWithExtraConditionsWithNotNull(): void
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition1 = :condition1');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition2 = :condition2');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition3 IS NOT NULL');

        $this->builder->shouldReceive('setParameter')->once()->with('condition1', 'value1');
        $this->builder->shouldReceive('setParameter')->once()->with('condition2', 'value2');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', null, null, [
            'condition1' => 'value1',
            'condition2' => 'value2',
            'condition3' => 'NOT_NULL',
        ]);

        $this->assertTrue(true);
    }

    public function testCanGetMultiCount(): void
    {
        $this->defaultGetMultiCountMocks();

        $this->verifier->getMultiCount(CountableEntityMock::class, 'email', ['test@email.com']);

        $this->assertTrue(true);
    }

    public function testCanGetMultiCountWithExtraConditions(): void
    {
        $this->defaultGetMultiCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition1 = :condition1');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition2 = :condition2');

        $this->builder->shouldReceive('setParameter')->once()->with('condition1', 'value1');
        $this->builder->shouldReceive('setParameter')->once()->with('condition2', 'value2');

        $this->verifier->getMultiCount(CountableEntityMock::class, 'email', ['test@email.com'], [
            'condition1' => 'value1',
            'condition2' => 'value2',
        ]);

        $this->assertTrue(true);
    }

    public function testCountingInvalidEntityThrowsException(): void
    {
        $this->registry->shouldReceive('getManagerForClass')
            ->with(CountableEntityMock::class)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No Entity Manager could be found for [LaravelDoctrineTest\ORM\Assets\Mock\CountableEntityMock].');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com');
    }

    protected function defaultGetCountMocks(): void
    {
        $this->registry->shouldReceive('getManagerForClass')
                       ->with(CountableEntityMock::class)
                       ->andReturn($this->em);

        $this->em->shouldReceive('createQueryBuilder')
                 ->once()->andReturn($this->builder);

        $this->builder->shouldReceive('select')
                      ->with('count(e)')->once()
                      ->andReturn($this->builder);

        $this->builder->shouldReceive('from')
                      ->with(CountableEntityMock::class, 'e')
                      ->once();

        $this->builder->shouldReceive('where')
                      ->with('e.email = :email')
                      ->once();

        $this->builder->shouldReceive('getQuery')
                      ->once()->andReturn($this->query);

        $this->query->shouldReceive('setParameter')->once()->with('email', 'test@email.com');

        $this->query->shouldReceive('getSingleScalarResult');
    }

    protected function defaultGetMultiCountMocks(): void
    {
        $this->registry->shouldReceive('getManagerForClass')
                       ->with(CountableEntityMock::class)
                       ->andReturn($this->em);

        $this->em->shouldReceive('createQueryBuilder')
                 ->once()->andReturn($this->builder);

        $this->builder->shouldReceive('select')
                      ->with('count(e)')->once()
                      ->andReturn($this->builder);

        $this->builder->shouldReceive('from')
                      ->with(CountableEntityMock::class, 'e')
                      ->once();

        $this->builder->shouldReceive('where')
                      ->once();

        $this->builder->shouldReceive('expr')->andReturn(new Expr());
        $this->builder->shouldReceive('in')->with('e.email', ['test@email.com']);

        $this->builder->shouldReceive('getQuery')
                      ->once()->andReturn($this->query);

        $this->query->shouldReceive('getSingleScalarResult');
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
