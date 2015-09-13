<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Hashing\Hasher;
use LaravelDoctrine\ORM\Auth\Authenticatable;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
use LaravelDoctrine\ORM\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Mockery as m;
use Mockery\Mock;

class DoctrineUserProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    protected $hasher;

    /**
     * @var Mock
     */
    protected $em;

    /**
     * @var DoctrineUserProvider
     */
    protected $provider;

    /**
     * @var Mock
     */
    protected $repo;

    protected function setUp()
    {
        $this->hasher = m::mock(Hasher::class);
        $this->em     = m::mock(EntityManagerInterface::class);
        $this->repo   = m::mock(EntityRepository::class);

        $this->provider = new DoctrineUserProvider(
            $this->hasher,
            $this->em,
            AuthenticableMock::class
        );
    }

    public function test_can_retrieve_by_id()
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock;
        $this->repo->shouldReceive('find')
                   ->once()->with(1)
                   ->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveById(1));
    }

    public function test_can_retrieve_by_token()
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock;
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'id'            => 1,
                       'rememberToken' => 'myToken'
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveByToken(1, 'myToken'));
    }

    public function test_can_update_remember_token()
    {
        $user = new AuthenticableMock;

        $this->em->shouldReceive('persist')->once()->with($user);
        $this->em->shouldReceive('flush')->once()->with($user);

        $this->provider->updateRememberToken($user, 'newToken');

        $this->assertEquals('newToken', $user->getRememberToken());
    }

    public function test_can_retrieve_by_credentials()
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock;
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'email' => 'email',
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveByCredentials([
            'email'    => 'email',
            'password' => 'password'
        ]));
    }

    public function test_can_validate_credentials()
    {
        $user = new AuthenticableMock;

        $this->hasher->shouldReceive('check')->once()
                     ->with('myPassword', 'myPassword')
                     ->andReturn(true);

        $this->assertTrue($this->provider->validateCredentials(
            $user,
            ['password' => 'myPassword']
        ));
    }

    protected function mockGetRepository()
    {
        $this->em->shouldReceive('getRepository')
                 ->with(AuthenticableMock::class)
                 ->once()->andReturn($this->repo);
    }

    protected function tearDown()
    {
        m::close();
    }
}

class AuthenticableMock implements AuthenticatableContract
{
    use Authenticatable;

    public function __construct()
    {
        $this->password = 'myPassword';
    }
}
