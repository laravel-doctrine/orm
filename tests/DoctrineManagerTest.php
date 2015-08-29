<?php

use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\DoctrineExtender;
use LaravelDoctrine\ORM\DoctrineManager;
use Mockery as m;

class DoctrineManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var DoctrineManager
     */
    protected $manager;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    protected function setUp()
    {
        $this->container = m::mock(Container::class);
        $this->registry  = m::mock(ManagerRegistry::class);
        $this->em        = m::mock(EntityManagerInterface::class);

        $this->manager = new DoctrineManager(
            $this->container,
            $this->registry
        );
    }

    public function test_can_extend_doctrine_on_existing_connection_with_callback()
    {
        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->mockEmCalls();

        $this->manager->extend('default', function ($configuration, $connection, $eventManager) {
            $this->assertExtendedCorrectly($configuration, $connection, $eventManager);
        });
    }

    public function test_can_extend_doctrine_on_existing_connection_with_class()
    {
        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->container->shouldReceive('make')
                        ->once()
                        ->with(MyDoctrineExtender::class)
                        ->andReturn(new MyDoctrineExtender);

        $this->mockEmCalls();

        $this->manager->extend('default', MyDoctrineExtender::class);
    }

    public function test_cant_extend_with_a_non_existing_extender_class()
    {
        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->setExpectedException(InvalidArgumentException::class);

        $this->manager->extend('default', 'no_class');
    }

    public function test_cant_extend_with_an_invalid_class()
    {
        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->container->shouldReceive('make')
                        ->once()
                        ->with(InvalidDoctrineExtender::class)
                        ->andReturn(new InvalidDoctrineExtender);

        $this->setExpectedException(InvalidArgumentException::class);

        $this->manager->extend('default', InvalidDoctrineExtender::class);
    }

    public function test_can_extend_all_connections()
    {
        $this->registry->shouldReceive('getManagerNames')->once()->andReturn([
            'default',
            'custom'
        ]);

        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->registry->shouldReceive('getConnection')
                       ->once()
                       ->with('custom')
                       ->andReturn($this->em);

        $this->em->shouldReceive('getConfiguration')
                 ->twice()->andReturn(m::mock(Configuration::class));
        $this->em->shouldReceive('getConnection')
                 ->twice()->andReturn(m::mock(Connection::class));
        $this->em->shouldReceive('getEventManager')
                 ->twice()->andReturn(m::mock(EventManager::class));

        $this->manager->extendAll(function ($configuration, $connection, $eventManager) {
            $this->assertExtendedCorrectly($configuration, $connection, $eventManager);
        });
    }

    protected function tearDown()
    {
        m::close();
    }

    public function assertExtendedCorrectly($configuration, $connection, $eventManager)
    {
        $this->assertInstanceOf(Configuration::class, $configuration);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(EventManager::class, $eventManager);
    }

    protected function mockEmCalls()
    {
        $this->em->shouldReceive('getConfiguration')
                 ->once()->andReturn(m::mock(Configuration::class));
        $this->em->shouldReceive('getConnection')
                 ->once()->andReturn(m::mock(Connection::class));
        $this->em->shouldReceive('getEventManager')
                 ->once()->andReturn(m::mock(EventManager::class));
    }
}

class MyDoctrineExtender implements DoctrineExtender
{
    /**
     * @param Configuration $configuration
     * @param Connection    $connection
     * @param EventManager  $eventManager
     */
    public function extend(Configuration $configuration, Connection $connection, EventManager $eventManager)
    {
        (new DoctrineManagerTest)->assertExtendedCorrectly($configuration, $connection, $eventManager);
    }
}

class InvalidDoctrineExtender
{
}
