<?php

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Illuminate\Contracts\Auth\CanResetPassword;
use LaravelDoctrine\ORM\Auth\Passwords\DoctrineTokenRepository;
use Mockery as m;
use Mockery\Mock;

class DoctrineTokenRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $schema;

    /**
     * @var DoctrineTokenRepository
     */
    protected $repository;

    /**
     * @var Mock
     */
    protected $registry;

    /**
     * @var Mock
     */
    protected $connection;

    /**
     * @var Mock
     */
    protected $builder;

    protected function setUp()
    {
        $this->connection = m::mock(Connection::class);
        $this->builder    = m::mock(QueryBuilder::class);
        $this->schema     = m::mock(AbstractSchemaManager::class);

        $this->connection->shouldReceive('getSchemaManager')
                         ->andReturn($this->schema);

        $this->schema->shouldReceive('tablesExist')
                     ->with('password_resets')
                     ->andReturn(true);

        $this->repository = new DoctrineTokenRepository(
            $this->connection,
            'password_resets',
            'hashkey',
            60
        );
    }

    public function test_can_create_a_token()
    {
        $this->connection->shouldReceive('createQueryBuilder')
                         ->twice()
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

        $this->builder->shouldReceive('execute')
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
                          'created_at' => ':date'
                      ])
                      ->once()->andReturnSelf();

        $this->builder->shouldReceive('setParameters')
                      ->once()->andReturnSelf();

        $this->assertNotNull($this->repository->create(new UserMock));
    }

    public function test_can_check_if_exists()
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

        $this->builder->shouldReceive('andWhere')
                      ->once()
                      ->with('token = :token')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturnSelf();

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('token', 'token')
                      ->andReturnSelf();

        $this->builder->shouldReceive('execute')
                      ->once()
                      ->andReturnSelf();

        $this->builder->shouldReceive('fetch')
                      ->once()
                      ->andReturn([
                          'email'      => 'user@mockery.mock',
                          'token'      => 'token',
                          'created_at' => Carbon::now()
                      ]);

        $this->assertTrue($this->repository->exists(new UserMock, 'token'));
    }

    public function test_can_delete()
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

        $this->builder->shouldReceive('execute')
                      ->once()
                      ->andReturn(true);

        $this->repository->delete(new UserMock);
    }

    public function test_can_delete_expired()
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

        $this->builder->shouldReceive('execute')
                      ->once();

        $this->repository->deleteExpired();
    }

    protected function tearDown()
    {
        m::close();
    }
}

class UserMock implements CanResetPassword
{
    /**
     * Get the e-mail address where password reset links are sent.
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return 'user@mockery.mock';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // TODO: Implement sendPasswordResetNotification() method.
    }
}
