<?php

use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Contracts\Auth\CanResetPassword;
use LaravelDoctrine\ORM\Auth\Passwords\DoctrineTokenRepository;
use LaravelDoctrine\ORM\Auth\Passwords\PasswordReminder;
use Mockery as m;
use Mockery\Mock;

class DoctrineTokenRepositoryTest extends PHPUnit_Framework_TestCase
{
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
    protected $em;

    /**
     * @var Mock
     */
    protected $builder;

    protected function setUp()
    {
        $this->em = m::mock(EntityManagerInterface::class);

        $this->builder = m::mock(QueryBuilder::class);

        $this->repository = new DoctrineTokenRepository(
            $this->em,
            'hashkey',
            60
        );
    }

    public function test_can_create_a_token()
    {
        $this->em->shouldReceive('createQueryBuilder')
                 ->once()
                 ->andReturn($this->builder);

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with(PasswordReminder::class, 'o')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('o.email = :email')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('getQuery')
                      ->once()
                      ->andReturn(m::self());

        $this->builder->shouldReceive('execute')
                      ->once()
                      ->andReturn(true);

        $this->em->shouldReceive('persist')
                 ->once();

        $this->em->shouldReceive('flush')
                 ->once();

        $this->assertNotNull($this->repository->create(new UserMock));
    }

    public function test_can_check_if_exists()
    {
        $this->em->shouldReceive('createQueryBuilder')
                 ->once()
                 ->andReturn($this->builder);

        $this->builder->shouldReceive('select')
                      ->once()
                      ->with('o')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('from')
                      ->once()
                      ->with(PasswordReminder::class, 'o')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('o.email = :email')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('andWhere')
                      ->once()
                      ->with('o.token = :token')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('email', 'user@mockery.mock')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('token', 'token')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('getQuery')
                      ->once()
                      ->andReturn(m::self());

        $this->builder->shouldReceive('getOneOrNullResult')
                      ->once()
                      ->andReturn(new PasswordReminder('user@mockery.mock', 'token'));

        $this->assertTrue($this->repository->exists(new UserMock, 'token'));
    }

    public function test_can_delete()
    {
        $this->em->shouldReceive('createQueryBuilder')
                 ->once()
                 ->andReturn($this->builder);

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with(PasswordReminder::class, 'o')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('o.token = :token')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('token', 'token')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('getQuery')
                      ->once()
                      ->andReturn(m::self());

        $this->builder->shouldReceive('execute')
                      ->once();

        $this->repository->delete('token');
    }

    public function test_can_delete_expired()
    {
        $this->em->shouldReceive('createQueryBuilder')
                 ->once()
                 ->andReturn($this->builder);

        $this->builder->shouldReceive('delete')
                      ->once()
                      ->with(PasswordReminder::class, 'o')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('where')
                      ->once()
                      ->with('o.createdAt < :expired')
                      ->andReturn(m::self());

        $this->builder->shouldReceive('setParameter')
                      ->once()
                      ->with('expired', (string) Carbon::now()->subSeconds(3600))
                      ->andReturn(m::self());

        $this->builder->shouldReceive('getQuery')
                      ->once()
                      ->andReturn(m::self());

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
}
