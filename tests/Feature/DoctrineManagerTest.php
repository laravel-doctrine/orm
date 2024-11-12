<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use LaravelDoctrine\ORM\BootChain;
use LaravelDoctrine\ORM\DoctrineManager;
use LaravelDoctrine\ORM\EntityManagerFactory;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;
use LaravelDoctrineTest\ORM\Assets\InvalidDoctrineExtender;
use LaravelDoctrineTest\ORM\Assets\MyDoctrineExtender;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class DoctrineManagerTest extends TestCase
{
    protected Container $container;
    protected ManagerRegistry $registry;
    protected DoctrineManager $manager;
    protected EntityManagerInterface $em;
    protected EntityManagerFactory $factory;

    protected function setUp(): void
    {
        $this->container = m::mock(Container::class);
        $this->registry  = m::mock(ManagerRegistry::class);
        $this->em        = m::mock(EntityManagerInterface::class);
        $this->factory   = m::mock(EntityManagerFactory::class)->makePartial();

        $this->manager = new DoctrineManager(
            $this->container,
        );

        parent::setUp();
    }

    public function testCanExtendDoctrineOnExistingConnectionWithCallback(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->mockEmCalls();

        $this->manager->extend('default', function ($configuration, $connection, $eventManager): void {
            $this->assertExtendedCorrectly($configuration, $connection, $eventManager);
        });

        BootChain::boot($this->registry);
    }

    public function testCanExtendDoctrineOnExistingConnectionWithClass(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->container->shouldReceive('make')
                        ->once()
                        ->with(MyDoctrineExtender::class)
                        ->andReturn(new MyDoctrineExtender());

        $this->mockEmCalls();

        $this->manager->extend('default', MyDoctrineExtender::class);

        BootChain::boot($this->registry);
    }

    public function testCantExtendWithANonExistingExtenderClass(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->expectException(InvalidArgumentException::class);

        $this->manager->extend('default', 'no_class');

        BootChain::boot($this->registry);
    }

    public function testCantExtendWithAnInvalidClass(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->container->shouldReceive('make')
                        ->once()
                        ->with(InvalidDoctrineExtender::class)
                        ->andReturn(new InvalidDoctrineExtender());

        $this->expectException(InvalidArgumentException::class);

        $this->manager->extend('default', InvalidDoctrineExtender::class);

        BootChain::boot($this->registry);
    }

    public function testCanExtendAllConnections(): void
    {
        $this->registry->shouldReceive('getManagerNames')->once()->andReturn([
            'default',
            'custom',
        ]);

        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('custom')
                       ->andReturn($this->em);

        $this->em->shouldReceive('getConfiguration')
                 ->twice()->andReturn(m::mock(Configuration::class));
        $this->em->shouldReceive('getConnection')
                 ->twice()->andReturn(m::mock(Connection::class));
        $this->em->shouldReceive('getEventManager')
                 ->twice()->andReturn(m::mock(EventManager::class));

        $this->manager->extendAll(function ($configuration, $connection, $eventManager): void {
            $this->assertExtendedCorrectly($configuration, $connection, $eventManager);
        });

        BootChain::boot($this->registry);
    }

    public function testCanAddPathsToDefaultConnection(): void
    {
        $this->registry->shouldReceive('getManager')
                       ->once()
                       ->with('default')
                       ->andReturn($this->em);

        $configuration = m::mock(Configuration::class);

        $mappingDriver = m::mock(MappingDriverChain::class);
        $mappingDriver->shouldReceive('addPaths')->once()->with(['paths']);

        $configuration->shouldReceive('getMetadataDriverImpl')
                      ->once()
                      ->andReturn($mappingDriver);

        $this->em->shouldReceive('getConfiguration')
                 ->once()->andReturn($configuration);

        $this->manager->addPaths(['paths'], 'default');

        BootChain::boot($this->registry);

        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        m::close();
        BootChain::flush();

        parent::tearDown();
    }

    public function assertExtendedCorrectly(mixed $configuration, mixed $connection, mixed $eventManager): void
    {
        $this->assertInstanceOf(Configuration::class, $configuration);
        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertInstanceOf(EventManager::class, $eventManager);
    }

    protected function mockEmCalls(): void
    {
        $this->em->shouldReceive('getConfiguration')
                 ->once()->andReturn(m::mock(Configuration::class));
        $this->em->shouldReceive('getConnection')
                 ->once()->andReturn(m::mock(Connection::class));
        $this->em->shouldReceive('getEventManager')
                 ->once()->andReturn(m::mock(EventManager::class));
    }
}
