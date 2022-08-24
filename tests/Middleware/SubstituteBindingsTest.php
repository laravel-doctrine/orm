<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use LaravelDoctrine\ORM\Middleware\SubstituteBindings;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;

class SubstituteBindingsTest extends TestCase
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

    /**
     * @var Mock
     */
    private $classMetadata;

    /**
     * @var Mock
     */
    private $objectManager;

    public function setUp(): void
    {
        $this->registry      = m::mock(ManagerRegistry::class);
        $this->em            = m::mock(EntityManager::class);
        $this->repository    = m::mock(\Doctrine\Persistence\ObjectRepository::class);
        $this->classMetadata = m::mock(ClassMetadataInfo::class);
        $this->objectManager = m::mock(ObjectManager::class);
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

    protected function mockClassMetadata(): void
    {
        $this->classMetadata
            ->shouldReceive('getSingleIdentifierFieldName')
            ->once()
            ->andReturn('id');
        $this->classMetadata
            ->shouldReceive('getTypeOfField')
            ->once()
            ->with('EntityIdName')
            ->andReturn('integer');
    }

    protected function mockObjectManager(): void
    {
        $this->mockClassMetadata();

        $this->objectManager
            ->shouldReceive('getClassMetadata')
            ->once()
            ->with('BindableEntity')
            ->andReturn($this->classMetadata);
    }

    protected function mockRegistry()
    {
        $this->mockObjectManager();

        $this->registry->shouldReceive('getManager')->once()->with('BindableEntity')->andReturn($this->objectManager);
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
        $this->expectException('Doctrine\ORM\EntityNotFoundException');

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

    public function test_for_typed_value_binding()
    {
        $router = $this->getRouter();
        $router->get('foo/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (string $value) {
                return $value;
            },
        ]);

        $this->assertEquals('test', $router->dispatch(Request::create('foo/test', 'GET'))->getContent());

        $router = $this->getRouter();
        $router->get('bar/{value}', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (int $value) {
                return $value;
            },
        ]);

        $this->assertEquals(123456, $router->dispatch(Request::create('bar/123456', 'GET'))->getContent());

        $router->get('doc/trine', [
            'middleware' => SubstituteBindings::class,
            'uses'       => function (Request $request) {
                return $request instanceof Request ? 'request' : 'something else';
            },
        ]);

        $this->assertEquals('request', $router->dispatch(Request::create('doc/trine', 'GET'))->getContent());
    }

    protected function tearDown(): void
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
}
