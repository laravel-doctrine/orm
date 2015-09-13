<?php

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use LaravelDoctrine\ORM\Validation\DoctrinePresenceVerifier;
use Mockery as m;
use Mockery\Mock;

class DoctrinePresenceVerifierTest extends PHPUnit_Framework_TestCase
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
    }

    public function test_can_get_count_with_excluded_ids()
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.id <> :id');

        $this->query->shouldReceive('setParameter')->once()->with('id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1);
    }

    public function test_can_get_count_with_excluded_ids_with_custom_id_column()
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.new_id <> :new_id');

        $this->query->shouldReceive('setParameter')->once()->with('new_id', 1);

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', 1, 'new_id');
    }

    public function test_can_get_count_with_extra_conditions()
    {
        $this->defaultGetCountMocks();

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition1 = :condition1');

        $this->builder->shouldReceive('andWhere')
                      ->once()->with('e.condition2 = :condition2');

        $this->builder->shouldReceive('setParameter')->once()->with('condition1', 'value1');
        $this->builder->shouldReceive('setParameter')->once()->with('condition2', 'value2');

        $this->verifier->getCount(CountableEntityMock::class, 'email', 'test@email.com', null, null, [
            'condition1' => 'value1',
            'condition2' => 'value2'
        ]);
    }

    public function test_can_get_multi_count()
    {
        $this->defaultGetMultiCountMocks();

        $this->verifier->getMultiCount(CountableEntityMock::class, 'email', ['test@email.com']);
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
