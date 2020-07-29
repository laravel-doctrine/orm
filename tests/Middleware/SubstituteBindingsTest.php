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

    /**
     * @var Mock
     */
    private $repository;

    public function setUp()
    {
        $this->registry     = m::mock(ManagerRegistry::class);
        $this->em           = m::mock(EntityManager::class);
        $this->repository   = m::mock(\Doctrine\Common\Persistence\ObjectRepository::class);
    }

    protected function getRouter()
    {
        $container = new Container;
        $router    = new Router(new Dispatcher, $container);

        $container->singleton(Registrar::class, function () use ($router) {
            return $router;
        });

        $container->alias(ManagerRegistry::class, \Doctrine\Persistence\ManagerRegistry::class);

        $container->singleton(ManagerRegistry::class, function () {
            return $this->registry;
        });

        return $router;
    }

    protected function mockRegistry()
    {
        $this->registry->shouldReceive('getRepository')->once()->with('BindableEntity')->andReturn($this->repository);
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
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

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
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn(null);

        $router->dispatch(Request::create('foo/1', 'GET'))->getContent();
    }

    public function test_entity_binding_get_null_entity()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (BindableEntity $entity = null) {
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
            'uses'       => 'EntityController@index',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->mockRegistry();
        $entity       = new BindableEntity();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('find')->once()->with(1)->andReturn($entity);

        $this->assertEquals('namevalue', $router->dispatch(Request::create('foo/1', 'GET'))->getContent());
    }

    public function test_not_id_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'EntityController@interfacer',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->registry->shouldReceive('getRepository')->once()->with('BindableEntityWithInterface')->andReturn($this->repository);
        $entity       = new BindableEntityWithInterface();
        $entity->id   = 1;
        $entity->name = 'NAMEVALUE';
        $this->repository->shouldReceive('findOneBy')->with(['name' => 'NAMEVALUE'])->andReturn($entity);

        $this->assertEquals(1, $router->dispatch(Request::create('foo/NAMEVALUE', 'GET'))->getContent());
    }

    public function test_not_id_binding_alternate()
    {
        $router = $this->getRouter();
        $router->get('foo/{entity}', [
            'uses'       => 'EntityController@interfaceralternate',
            'middleware' => SubstituteBindings::class,
        ]);

        $this->registry->shouldReceive('getRepository')->once()->with('BindableEntityWithAlternateInterface')->andReturn($this->repository);
        $this->registry->shouldReceive('getManagerForClass')->once()->with('BindableEntityWithAlternateInterface')->andReturn($this->em);
        $this->em->shouldReceive('getClassMetadata')->once()->with('BindableEntityWithAlternateInterface')->andReturn(new \Doctrine\ORM\Mapping\ClassMetadata(BindableEntityWithAlternateInterface::class));
        $entity       = new BindableEntityWithAlternateInterface();
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

class BindableEntityWithInterface implements \LaravelDoctrine\ORM\Contracts\UrlRoutable
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

    public static function getRouteKeyName(): string
    {
        return 'name';
    }
}

class BindableEntityWithAlternateInterface implements \Illuminate\Contracts\Routing\UrlRoutable
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

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    public function getRouteKey()
    {
        $fields = EntityManager::getClassMetadata(__CLASS__)->getIdentifierFieldNames();
        if(count($fields) == 1) {
            return $fields[0];
        }
        throw new \InvalidArgumentException(sprintf("%s has %d identifier fields, 1 expected", __CLASS__, count($fields)));
    }

    public function resolveRouteBinding($value)
    {
        return EntityManager::getRepository(__CLASS__)->findOneBy([$this->getRouteKeyName() => $value]);
    }
}

class EntityController
{
    public function index(BindableEntity $entity)
    {
        return $entity->getName();
    }

    public function interfacer(BindableEntityWithInterface $entity)
    {
        return $entity->getId();
    }

    public function interfaceralternate(BindableEntityWithAlternateInterface $entity)
    {
        return $entity->getId();
    }
}