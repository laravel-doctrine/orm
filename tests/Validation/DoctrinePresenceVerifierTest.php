<?php

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class DoctrinePresenceVerifierTest extends TestCase
{
    /**
     * @var Mock
     */
    protected $registry;

    /**
     * @var DoctrinePresenceVerifier
     */
    protected $verifier;

    /**
     * @var Mock
     */
    protected $em;

    /**
     * @var Mock
     */
    protected $builder;

    /**
     * @var Mock
     */
    protected $query;

    protected function setUp()
    {
        $this->em       = m::mock(EntityManagerInterface::class);
        $this->registry = m::mock(ManagerRegistry::class);
        $this->builder  = m::mock(QueryBuilder::class);
        $this->query    = m::mock(AbstractQuery::class);

        $this->verifier = new DoctrinePresenceVerifier(
            $this->registry
        );
    }

    public function test_can_get_count()
    {
        $this->defaultGetCountMocks();

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com');

        $this->assertTrue(true);
    }

    public function test_can_get_count_with_excluded_ids()
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.id <> :id');

        $this->query->shouldReceive('setParameter')->once()->with('id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1);

        $this->assertTrue(true);
    }

    public function test_can_get_count_with_excluded_ids_with_custom_id_column()
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.new_id <> :new_id');

        $this->query->shouldReceive('setParameter')->once()->with('new_id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1, 'new_id');

        $this->assertTrue(true);
    }

    public function test_can_get_count_with_extra_conditions()
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
            'condition3' => '!value3'
        ]);

        $this->assertTrue(true);
    }

    public function test_can_get_count_with_extra_conditions_with_null()
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
            'condition3' => 'NULL'
        ]);

        $this->assertTrue(true);
    }

    public function test_can_get_count_with_extra_conditions_with_not_null()
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
            'condition3' => 'NOT_NULL'
        ]);

        $this->assertTrue(true);
    }

    public function test_can_get_multi_count()
    {
        $this->defaultGetMultiCountMocks();

        $this->verifier->getMultiCount(CountableEntityMock::class, 'email', ['test@email.com']);

        $this->assertTrue(true);
    }

    public function test_can_get_multi_count_with_extra_conditions()
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
            'condition2' => 'value2'
        ]);

        $this->assertTrue(true);
    }

    public function test_counting_invalid_entity_throws_exception()
    {
        $this->registry->shouldReceive('getManagerForClass')
            ->with(CountableEntityMock::class)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No Entity Manager could be found for [CountableEntityMock].');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com');
    }

    protected function defaultGetCountMocks()
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

    protected function defaultGetMultiCountMocks()
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

        $this->builder->shouldReceive('expr')->andReturn($this->builder);
        $this->builder->shouldReceive('in')->with("e.email", ['test@email.com']);

        $this->builder->shouldReceive('getQuery')
                      ->once()->andReturn($this->query);

        $this->query->shouldReceive('getSingleScalarResult');
    }

    protected function tearDown()
    {
        m::close();
    }
}

class CountableEntityMock
{
}
