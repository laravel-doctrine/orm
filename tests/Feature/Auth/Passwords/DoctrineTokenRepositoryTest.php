<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Auth\Passwords;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Contracts\Hashing\Hasher;
use LaravelDoctrine\ORM\Auth\Passwords\DoctrineTokenRepository;
use LaravelDoctrineTest\ORM\Assets\Auth\Passwords\UserMock;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;
use Mockery\Mock;

class DoctrineTokenRepositoryTest extends TestCase
{
    protected AbstractSchemaManager $schema;

    protected DoctrineTokenRepository $repository;

    protected Mock $registry;

    protected Connection $connection;

    protected Hasher $hasher;

    protected QueryBuilder $builder;

    protected function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->hasher     = m::mock(Hasher::class);
        $this->builder    = m::mock(QueryBuilder::class);
        $this->schema     = m::mock(AbstractSchemaManager::class);

        $this->connection->shouldReceive('createSchemaManager')
                         ->andReturn($this->schema);

        $this->schema->shouldReceive('tablesExist')
                     ->with(['password_resets'])
                     ->andReturn(true);

        $this->repository = new DoctrineTokenRepository(
            $this->connection,
            $this->hasher,
            'password_resets',
            'hashkey',
            60,
        );

        parent::setUp();
    }

    public function testCanCreateAToken(): void
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->twice()
                         ->andReturn($this->builder);

        $this->hasher->shouldReceive('make')
                     ->once()
                     ->andReturn('token');

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with('password_resets')
                      ->andReturnSelf();

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('email = :email')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturnSelf();

        $this->builder->shouldReceive('executeStatement')
                      ->twice()
                      ->andReturn(true);

        $this->builder->shouldReceive('insert')
                      ->with('password_resets')
                      ->once()
                      ->andReturnSelf();

        $this->builder->shouldReceive('values')
                      ->with([
                          'email'      => ':email',
                          'token'      => ':token',
                          'created_at' => ':date',
                      ])
                      ->once()->andReturnSelf();

        $this->builder->shouldReceive('setParameters')
                      ->once()->andReturnSelf();

        $this->assertNotNull($this->repository->create(new UserMock()));
    }

    public function testCanCheckIfExists(): void
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->once()
                         ->andReturn($this->builder);

        $this->hasher->shouldReceive('check')
                     ->once()
                     ->with('token', 'token')
                     ->andReturn(true);

        $this->builder->shouldReceive('select')
                      ->once()
                      ->with('*')
                      ->andReturnSelf();

        $this->builder->shouldReceive('from')
                      ->once()
                      ->with('password_resets')
                      ->andReturnSelf();

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('email = :email')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setMaxResults')
                      ->once()
                      ->with(1)
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturnSelf();

        $result = m::mock(Result::class);

        $this->builder->shouldReceive('executeQuery')
                      ->once()
                      ->andReturn($result);

        $result->shouldReceive('fetchAssociative')
                      ->once()
                      ->andReturn([
                          'email'      => 'user@mockery.mock',
                          'token'      => 'token',
                          'created_at' => Carbon::now(),
                      ]);

        $this->assertTrue($this->repository->exists(new UserMock(), 'token'));
    }

    public function testCanCheckIfRecentlyCreatedToken(): void
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->once()
                         ->andReturn($this->builder);

        $this->builder->shouldReceive('select')
                      ->once()
                      ->with('*')
                      ->andReturnSelf();

        $this->builder->shouldReceive('from')
                      ->once()
                      ->with('password_resets')
                      ->andReturnSelf();

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('email = :email')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturnSelf();

        $result = m::mock(Result::class);

        $this->builder->shouldReceive('executeQuery')
            ->once()
            ->andReturn($result);

        $result->shouldReceive('fetchAssociative')
              ->once()
              ->andReturn([
                  'email'      => 'user@mockery.mock',
                  'token'      => 'token',
                  'created_at' => Carbon::now(),
              ]);

        $this->assertTrue($this->repository->recentlyCreatedToken(new UserMock()));
    }

    public function testCanDelete(): void
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->once()
                         ->andReturn($this->builder);

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with('password_resets')
                      ->andReturnSelf();

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('email = :email')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturnSelf();

        $this->builder->shouldReceive('executeStatement')
                      ->once()
                      ->andReturn(true);

        $this->repository->delete(new UserMock());

        $this->assertTrue(true);
    }

    public function testCanDeleteExpired(): void
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->once()
                         ->andReturn($this->builder);

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with('password_resets')
                      ->andReturnSelf();

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('created_at < :expiredAt')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->andReturnSelf();

        $this->builder->shouldReceive('executeStatement')
                      ->once();

        $this->repository->deleteExpired();

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
