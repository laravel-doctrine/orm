<?php

namespace LaravelDoctrine\Tests\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use LaravelDoctrine\ORM\Middleware\SubstituteBindings;
use Mockery as m;
use Mockery\Mock;
use Doctrine\ORM\EntityNotFoundException;

class SubstituteBindingsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mock
     */
    private $registry;

    /**
     * @var Mock
     */
    private $em;

    /**
     * @var Mock
     */
    private $repository;

    public function setUp()
    {
        $this->registry     = m::mock(ManagerRegistry::class);
        $this->em           = m::mock(EntityManager::class);
        $this->repository   = m::mock(ObjectRepository::class);
    }

    protected function getRouter()
    {
        $container = new Container;
        $router    = new Router(new Dispatcher, $container);

        $container->singleton(Registrar::class, function () use ($router) {
            return $router;
        });

        $container->singleton(ManagerRegistry::class, function () {
            return $this->registry;
        });

        return $router;
    }

    protected function mockRegistry()
    {
        $this->registry->shouldReceive('getRepository')->once()->with(\LaravelDoctrine\Tests\Mocks\BindableEntity::class)->andReturn($this->repository);
    }

    public function test_entity_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (\LaravelDoctrine\Tests\Mocks\BindableEntity $entity) {
                return $entity->getName();
            },
        ]);

        $this->mockRegistry();
        $entity       = new \LaravelDoctrine\Tests\Mocks\BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function test_entity_binding_expect_entity_not_found_exception()
    {
        $this->expectException(EntityNotFoundException::class);

        $router = $this->getRouter();

        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (\LaravelDoctrine\Tests\Mocks\BindableEntity $entity) {
                return $entity->getName();
            },
        ]);

        $this->mockRegistry();
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn(null);

        $router->dispatch(Request::create('foo/1', 'GET'))->getContent();
    }

    public function test_entity_binding_get_null_entity()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (\LaravelDoctrine\Tests\Mocks\BindableEntity $entity = null) {
                return $entity;
            },
        ]);

        $this->mockRegistry();
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn(null);

        $this->assertEquals('', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function test_binding_value()
    {
        $router = $this->getRouter();
        $router->get('foo/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function ($value) {
                return $value;
            },
        ]);

        $this->assertEquals(123456, $router->dispatch(Request::create('foo/123456', 'GET'))->getContent());

        $router->get('doc/trine', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (Request $request) {
                return $request instanceof Request ? 'request' : 'something else';
            },
        ]);

        $this->assertEquals('request', $router->dispatch(Request::create('doc/trine', 'GET'))->getContent());
    }

    public function test_controller_entity_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'LaravelDoctrine\Tests\Mocks\EntityController@index',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->mockRegistry();
        $entity       = new \LaravelDoctrine\Tests\Mocks\BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function test_not_id_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'LaravelDoctrine\Tests\Mocks\EntityController@interfacer',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->registry->shouldReceive('getRepository')->once()->with(\LaravelDoctrine\Tests\Mocks\BindableEntityWithInterface::class)->andReturn($this->repository);
        $entity       = new \LaravelDoctrine\Tests\Mocks\BindableEntityWithInterface();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('findOneBy')->with(['name' => 'NAMEVALUE'])->andReturn($entity);

        $this->assertEquals(1, $router->dispatch(Request::create('foo/NAMEVALUE', 'GET'))->getContent());
    }

    protected function tearDown()
    {
        m::close();
    }
}
