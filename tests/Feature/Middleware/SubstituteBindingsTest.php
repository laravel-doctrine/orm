<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Middleware;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\Router;
use LaravelDoctrine\ORM\Middleware\SubstituteBindings;
use LaravelDoctrineTest\ORM\Assets\Middleware\BindableEntity;
use LaravelDoctrineTest\ORM\Assets\Middleware\BindableEntityWithInterface;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;
use Mockery\Mock;

class SubstituteBindingsTest extends TestCase
{
    /** @var Mock */
    private ManagerRegistry $registry;

    /** @var Mock */
    private EntityManager $em;

    /** @var Mock */
    private ObjectRepository $repository;

    public function setUp(): void
    {
        $this->registry   = m::mock(ManagerRegistry::class);
        $this->em         = m::mock(EntityManager::class);
        $this->repository = m::mock(ObjectRepository::class);

        parent::setUp();
    }

    protected function getRouter(): Router
    {
        $container = new Container();
        $container->bind(CallableDispatcherContract::class, static fn ($app) => new CallableDispatcher($app));
        $router = new Router(new Dispatcher(), $container);

        $container->singleton(Registrar::class, static function () use ($router) {
            return $router;
        });

        $container->singleton(ManagerRegistry::class, function () {
            return $this->registry;
        });

        return $router;
    }

    protected function mockRegistry(): void
    {
        $this->registry->shouldReceive('getRepository')->once()->with('LaravelDoctrineTest\ORM\Assets\Middleware\BindableEntity')->andReturn($this->repository);
    }

    public function testEntityBinding(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnEntityName',
        ]);

        $this->mockRegistry();
        $entity       = new BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function testEntityBindingExpectEntityNotFoundException(): void
    {
        $this->expectException('Doctrine\ORM\EntityNotFoundException');

        $router = $this->getRouter();

        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnEntityName',
        ]);

        $this->mockRegistry();
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn(null);

        $router->dispatch(Request::create('foo/1', 'GET'))->getContent();
    }

    public function testEntityBindingGetNullEntity(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnEntity',
        ]);

        $this->mockRegistry();
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn(null);

        $this->assertEquals('', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function testBindingValue(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnValue',
        ]);

        $this->assertEquals(123456, $router->dispatch(Request::create('foo/123456', 'GET'))->getContent());

        $router->get('doc/trine', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@checkRequest',
        ]);

        $this->assertEquals('request', $router->dispatch(Request::create('doc/trine', 'GET'))->getContent());
    }

    public function testControllerEntityBinding(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@index',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->mockRegistry();
        $entity       = new BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function testNotIdBinding(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@interfacer',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->registry->shouldReceive('getRepository')->once()->with('LaravelDoctrineTest\ORM\Assets\Middleware\BindableEntityWithInterface')->andReturn($this->repository);
        $entity       = new BindableEntityWithInterface();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('findOneBy')->with(['name' => 'NAMEVALUE'])->andReturn($entity);

        $this->assertEquals(1, $router->dispatch(Request::create('foo/NAMEVALUE', 'GET'))->getContent());
    }

    public function testForTypedValueBinding(): void
    {
        $router = $this->getRouter();
        $router->get('foo/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnValue',
        ]);

        $this->assertEquals('test', $router->dispatch(Request::create('foo/test', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('bar/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@returnValue',
        ]);

        $this->assertEquals(123456, $router->dispatch(Request::create('bar/123456', 'GET'))->getContent());

        $router->get('doc/trine', [
            'middleware' => SubstituteBindings::class,
            'uses'       => 'LaravelDoctrineTest\ORM\Assets\Middleware\EntityController@checkRequest',
        ]);

        $this->assertEquals('request', $router->dispatch(Request::create('doc/trine', 'GET'))->getContent());
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
