<?php

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use LaravelDoctrine\ORM\Middleware\SubstituteBindings;
use Mockery as m;
use Mockery\Mock;

class SubstituteBindingsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mock
     */
    private $registry;

    /**
     * @var Mock
     */
    private $em;

    public function setUp()
    {
        $this->registry = m::mock(ManagerRegistry::class);
        $this->em       = m::mock(EntityManager::class);
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
        $this->registry->shouldReceive('getManagerForClass')->once()->with('BindableEntity')->andReturn($this->em);
    }

    public function test_entity_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (BindableEntity $entity) {
                return $entity->getName();
            },
        ]);

        $this->mockRegistry();
        $entity       = new BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->em->shouldReceive('find')->once()->with('BindableEntity', 1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function test_entity_binding_expect_entity_not_found_exception()
    {
        $this->setExpectedException('Doctrine\ORM\EntityNotFoundException');

        $router = $this->getRouter();

        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (BindableEntity $entity) {
                return $entity->getName();
            },
        ]);

        $this->mockRegistry();
        $this->em->shouldReceive('find')->once()->with('BindableEntity', 1)->andReturn(null);

        $router->dispatch(Request::create('foo/1', 'GET'))->getContent();
    }

    public function test_entity_binding_get_null_entity()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (BindableEntity $entity = null) {
                return $entity->getName();
            },
        ]);

        $this->mockRegistry();
        $this->em->shouldReceive('find')->once()->with('BindableEntity', 1)->andReturn(null);

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
            'uses'       => 'EntityController@index',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->mockRegistry();
        $entity       = new BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->em->shouldReceive('find')->once()->with('BindableEntity', 1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    protected function tearDown()
    {
        m::close();
    }
}

class BindableEntity
{
    public $id;

    public $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return strtolower($this->name);
    }
}

class EntityController
{
    public function index(BindableEntity $entity)
    {
        return $entity->getName();
    }
}
