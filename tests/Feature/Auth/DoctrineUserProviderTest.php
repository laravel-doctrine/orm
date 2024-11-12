<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Hashing\Hasher;
use LaravelDoctrine\ORM\Auth\DoctrineUserProvider;
use LaravelDoctrineTest\ORM\Assets\Auth\AuthenticableMock;
use LaravelDoctrineTest\ORM\Assets\Auth\AuthenticableWithNonEmptyConstructorMock;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class DoctrineUserProviderTest extends TestCase
{
    protected Hasher $hasher;

    protected EntityManagerInterface $em;

    protected DoctrineUserProvider $provider;

    protected DoctrineUserProvider $providerNonEmpty;

    protected EntityRepository $repo;

    protected function setUp(): void
    {
        $this->hasher = m::mock(Hasher::class);
        $this->em     = m::mock(EntityManagerInterface::class);
        $this->repo   = m::mock(EntityRepository::class);

        $this->provider         = new DoctrineUserProvider(
            $this->hasher,
            $this->em,
            AuthenticableMock::class,
        );
        $this->providerNonEmpty = new DoctrineUserProvider(
            $this->hasher,
            $this->em,
            AuthenticableWithNonEmptyConstructorMock::class,
        );

        parent::setUp();
    }

    public function testCanRetrieveById(): void
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock();
        $this->repo->shouldReceive('find')
                   ->once()->with(1)
                   ->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveById(1));
    }

    public function testCanRetrieveByToken(): void
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock();
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'id'            => 1,
                       'rememberToken' => 'myToken',
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveByToken(1, 'myToken'));
    }

    public function testCanRetrieveByTokenWithNonEmptyConstructor(): void
    {
        $this->mockGetRepository(AuthenticableWithNonEmptyConstructorMock::class);

        $user = new AuthenticableWithNonEmptyConstructorMock(['myPassword']);
        $this->repo->shouldReceive('findOneBy')
                   ->with([
                       'id'            => 1,
                       'rememberToken' => 'myToken',
                   ])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->providerNonEmpty->retrieveByToken(1, 'myToken'));
    }

    public function testCanUpdateRememberToken(): void
    {
        $user = new AuthenticableMock();

        $this->em->shouldReceive('persist')->once()->with($user);
        $this->em->shouldReceive('flush')->once()->withNoArgs();

        $this->provider->updateRememberToken($user, 'newToken');

        $this->assertEquals('newToken', $user->getRememberToken());
    }

    public function testCanRetrieveByCredentials(): void
    {
        $this->mockGetRepository();

        $user = new AuthenticableMock();
        $this->repo->shouldReceive('findOneBy')
                   ->with(['email' => 'email'])
                   ->once()->andReturn($user);

        $this->assertEquals($user, $this->provider->retrieveByCredentials([
            'email'    => 'email',
            'password' => 'password',
        ]));
    }

    public function testCanValidateCredentials(): void
    {
        $user = new AuthenticableMock();

        $this->hasher->shouldReceive('check')->once()
                     ->with('myPassword', 'myPassword')
                     ->andReturn(true);

        $this->assertTrue($this->provider->validateCredentials(
            $user,
            ['password' => 'myPassword'],
        ));
    }

    public function testRehashPasswordIfRequriredRehash(): void
    {
        $user = new AuthenticableMock();

        $this->hasher->shouldReceive('needsRehash')->once()->andReturn(true);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashedPassword');
        $this->em->shouldReceive('persist')->once();
        $this->em->shouldReceive('flush')->once();

        $this->provider->rehashPasswordIfRequired($user, ['password' => 'rawPassword'], false);
        $this->assertEquals('hashedPassword', $user->getPassword());
    }

    public function testRehashPasswordIfRequiredRehashForce(): void
    {
        $user = new AuthenticableMock();

        $this->hasher->shouldReceive('needsRehash')->once()->andReturn(false);
        $this->hasher->shouldReceive('make')->once()->andReturn('hashedPassword');
        $this->em->shouldReceive('persist')->once();
        $this->em->shouldReceive('flush')->once();

        $this->provider->rehashPasswordIfRequired($user, ['password' => 'rawPassword'], true);
        $this->assertEquals('hashedPassword', $user->getPassword());
    }

    public function testRehashPasswordIfRequiredRehashNorehashNeeded(): void
    {
        $user = new AuthenticableMock();
        $user->setPassword('originalPassword');

        $this->hasher->shouldReceive('needsRehash')->once()->andReturn(false);

        $this->provider->rehashPasswordIfRequired($user, ['password' => 'rawPassword'], false);
        $this->assertEquals('originalPassword', $user->getPassword());
    }

    protected function mockGetRepository(string $class = AuthenticableMock::class): void
    {
        $this->em->shouldReceive('getRepository')
                 ->with($class)
                 ->once()->andReturn($this->repo);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
