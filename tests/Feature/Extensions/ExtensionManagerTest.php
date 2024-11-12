<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Extensions;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock;
use LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock2;
use LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionWithFiltersMock;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;
use Mockery\Mock;

class ExtensionManagerTest extends TestCase
{
    protected ManagerRegistry|Mock $registry;

    protected ExtensionManager $manager;

    protected EntityManagerInterface $em;

    protected Configuration $configuration;

    protected EventManager $evm;

    protected XmlDriver $driver;

    protected Container|Mock $container;

    protected function setUp(): void
    {
        $this->registry      = m::mock(ManagerRegistry::class);
        $this->container     = m::mock(Container::class);
        $this->em            = m::mock(EntityManagerInterface::class);
        $this->evm           = m::mock(EventManager::class);
        $this->configuration = m::mock(Configuration::class);
        $this->driver        = m::mock(XmlDriver::class);

        $this->manager = $this->newManager();

        parent::setUp();
    }

    public function testRegisterExtension(): void
    {
        $extension = new ExtensionMock();

        $this->manager->register($extension);

        $this->assertContains($extension, $this->manager->getExtensions());
    }

    public function testBootManagerWithOneManagerAndOneExtension(): void
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->once()->andReturn(new ExtensionMock());
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock']);
    }

    public function testBootManagerWithTwoManagersAndOneExtension(): void
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
            'custom'  => $this->em,
        ]);

        $this->em->shouldReceive('getEventManager')->twice()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->twice()->andReturn($this->configuration);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->twice()->andReturn(new ExtensionMock());
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock']);
        $this->assertTrue((bool) $booted['custom']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock']);
    }

    public function testBootManagerWithOneManagerAndTwoExtensions(): void
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
        ]);

        $this->em->shouldReceive('getEventManager')->twice()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->twice()->andReturn($this->configuration);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->once()->andReturn(new ExtensionMock());
        $this->manager->register(ExtensionMock::class);

        $this->container->shouldReceive('make')->with(ExtensionMock2::class)->once()->andReturn(new ExtensionMock2());
        $this->manager->register(ExtensionMock2::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock']);
        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock2']);
    }

    public function testExtensionWillOnlyBeBootedOnce(): void
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->times(3)->andReturn(new ExtensionMock());
        $this->manager->register(ExtensionMock::class);
        $this->manager->register(ExtensionMock::class);
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionMock']);
    }

    public function testFiltersGetRegisteredOnBoot(): void
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        $collection = m::mock(FilterCollection::class);

        $this->configuration->shouldReceive('addFilter')->once()->with('filter', 'FilterMock');
        $this->configuration->shouldReceive('addFilter')->once()->with('filter2', 'FilterMock');

        $this->em->shouldReceive('getFilters')->twice()->andReturn($collection);

        $collection->shouldReceive('enable')->once()->with('filter');
        $collection->shouldReceive('enable')->once()->with('filter2');

        // Register
        $this->container->shouldReceive('make')->with(ExtensionWithFiltersMock::class)->once()->andReturn(new ExtensionWithFiltersMock());
        $this->manager->register(ExtensionWithFiltersMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();

        $this->assertTrue((bool) $booted['default']['LaravelDoctrineTest\ORM\Assets\Extensions\ExtensionWithFiltersMock']);
    }

    protected function tearDown(): void
    {
        m::close();

        $this->manager = $this->newManager();

        parent::tearDown();
    }

    protected function newManager(): ExtensionManager
    {
        return new ExtensionManager($this->container);
    }
}
