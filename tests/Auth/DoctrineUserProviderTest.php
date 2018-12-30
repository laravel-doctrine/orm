<?php

namespace LaravelDoctrine\Tests\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Hashing\Hasher;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
use LaravelDoctrine\Tests\Mocks\AuthenticatableMock;
use LaravelDoctrine\Tests\Mocks\AuthenticatableWithNonEmptyConstructorMock;
use Mockery as m;
use Mockery\Mock;

class DoctrineUserProviderTest extends \PHPUnit\Framework\TestCase
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
     * @var DoctrineUserProvider
     */
    protected $providerNonEmpty;

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
            AuthenticatableMock::class
        );
        $this->providerNonEmpty = new DoctrineUserProvider(
            $this->hasher,
            $this->em,
            AuthenticatableWithNonEmptyConstructorMock::class
        );
    }

    public function test_can_retrieve_by_id()
    {
        $this->mockGetRepository();

        $user = new AuthenticatableMock;
        $this->repo->shouldReceive('find')
                   ->once()->with(1)
                   ->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveById(1));
    }

    public function test_can_retrieve_by_token()
    {
        $this->mockGetRepository();

        $user = new AuthenticatableMock;
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'id'            => 1,
                       'rememberToken' => 'myToken'
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveByToken(1, 'myToken'));
    }

    public function test_can_retrieve_by_token_with_non_empty_constructor()
    {
        $this->mockGetRepository(AuthenticatableWithNonEmptyConstructorMock::class);

        $user = new AuthenticatableWithNonEmptyConstructorMock(['myPassword']);
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'id'            => 1,
                       'rememberToken' => 'myToken'
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->providerNonEmpty->retrieveByToken(1, 'myToken'));
    }

    public function test_can_update_remember_token()
    {
        $user = new AuthenticatableMock;

        $this->em->shouldReceive('persist')->once()->with($user);
        $this->em->shouldReceive('flush')->once()->with($user);

        $this->provider->updateRememberToken($user, 'newToken');

        $this->assertEquals('newToken', $user->getRememberToken());
    }

    public function test_can_retrieve_by_credentials()
    {
        $this->mockGetRepository();

        $user = new AuthenticatableMock;
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
        $user = new AuthenticatableMock;

        $this->hasher->shouldReceive('check')->once()
                     ->with('myPassword', 'myPassword')
                     ->andReturn(true);

        $this->assertTrue($this->provider->validateCredentials(
            $user,
            ['password' => 'myPassword']
        ));
    }

    protected function mockGetRepository($class = AuthenticatableMock::class)
    {
        $this->em->shouldReceive('getRepository')
                 ->with($class)
                 ->once()->andReturn($this->repo);
    }

    protected function tearDown()
    {
        m::close();
    }
}
