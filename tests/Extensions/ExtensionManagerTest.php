<?php

namespace LaravelDoctrine\Tests\Extensions;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Query\FilterCollection;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Extensions\ExtensionManager;
use Mockery as m;
use Mockery\Mock;
use LaravelDoctrine\Tests\Mocks\ExtensionMock;

class ExtensionManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Mock
     */
    protected $registry;

    /**
     * @var ExtensionManager
     */
    protected $manager;

    /**
     * @var Mock
     */
    protected $em;

    /**
     * @var Mock
     */
    protected $configuration;

    /**
     * @var Mock
     */
    protected $evm;

    /**
     * @var Mock
     */
    protected $driver;

    /**
     * @var Mock
     */
    protected $reader;

    /**
     * @var Mock
     */
    protected $container;

    protected function setUp()
    {
        $this->registry      = m::mock(ManagerRegistry::class);
        $this->container     = m::mock(Container::class);
        $this->em            = m::mock(EntityManagerInterface::class);
        $this->evm           = m::mock(EventManager::class);
        $this->configuration = m::mock(Configuration::class);
        $this->driver        = m::mock(AnnotationDriver::class);
        $this->reader        = m::mock(Reader::class);

        $this->manager = $this->newManager();
    }

    public function test_register_extension()
    {
        $extension = new ExtensionMock;

        $this->manager->register($extension);

        $this->assertContains($extension, $this->manager->getExtensions());
    }

    public function test_boot_manager_with_one_manager_and_one_extension()
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        $this->configuration->shouldReceive('getMetadataDriverImpl')->once()->andReturn($this->driver);
        $this->driver->shouldReceive('getReader')->once()->andReturn($this->reader);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->once()->andReturn(new ExtensionMock);
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue($booted['default'][ExtensionMock::class]);
    }

    public function test_boot_manager_with_two_managers_and_one_extension()
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em,
            'custom'  => $this->em
        ]);

        $this->em->shouldReceive('getEventManager')->twice()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->twice()->andReturn($this->configuration);

        $this->configuration->shouldReceive('getMetadataDriverImpl')->twice()->andReturn($this->driver);
        $this->driver->shouldReceive('getReader')->twice()->andReturn($this->reader);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->twice()->andReturn(new ExtensionMock);
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue($booted['default'][ExtensionMock::class]);
        $this->assertTrue($booted['custom'][ExtensionMock::class]);
    }

    public function test_boot_manager_with_one_manager_and_two_extensions()
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em
        ]);

        $this->em->shouldReceive('getEventManager')->twice()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->twice()->andReturn($this->configuration);

        $this->configuration->shouldReceive('getMetadataDriverImpl')->twice()->andReturn($this->driver);
        $this->driver->shouldReceive('getReader')->twice()->andReturn($this->reader);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->once()->andReturn(new ExtensionMock);
        $this->manager->register(ExtensionMock::class);

        $this->container->shouldReceive('make')->with(\LaravelDoctrine\Tests\Mocks\ExtensionMock2::class)->once()->andReturn(new \LaravelDoctrine\Tests\Mocks\ExtensionMock2);
        $this->manager->register(\LaravelDoctrine\Tests\Mocks\ExtensionMock2::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue($booted['default'][ExtensionMock::class]);
        $this->assertTrue($booted['default'][\LaravelDoctrine\Tests\Mocks\ExtensionMock2::class]);
    }

    public function test_extension_will_only_be_booted_once()
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        $this->configuration->shouldReceive('getMetadataDriverImpl')->once()->andReturn($this->driver);
        $this->driver->shouldReceive('getReader')->once()->andReturn($this->reader);

        // Register
        $this->container->shouldReceive('make')->with(ExtensionMock::class)->times(3)->andReturn(new ExtensionMock);
        $this->manager->register(ExtensionMock::class);
        $this->manager->register(ExtensionMock::class);
        $this->manager->register(ExtensionMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue($booted['default'][ExtensionMock::class]);
    }

    public function test_filters_get_registered_on_boot()
    {
        $this->registry->shouldReceive('getManagers')->andReturn([
            'default' => $this->em
        ]);

        $this->em->shouldReceive('getEventManager')->once()->andReturn($this->evm);
        $this->em->shouldReceive('getConfiguration')->once()->andReturn($this->configuration);

        $this->configuration->shouldReceive('getMetadataDriverImpl')->once()->andReturn($this->driver);
        $this->driver->shouldReceive('getReader')->once()->andReturn($this->reader);

        $collection = m::mock(FilterCollection::class);

        $this->configuration->shouldReceive('addFilter')->once()->with('filter', 'FilterMock');
        $this->configuration->shouldReceive('addFilter')->once()->with('filter2', 'FilterMock');

        $this->em->shouldReceive('getFilters')->twice()->andReturn($collection);

        $collection->shouldReceive('enable')->once()->with('filter');
        $collection->shouldReceive('enable')->once()->with('filter2');

        // Register
        $this->container->shouldReceive('make')->with(\LaravelDoctrine\Tests\Mocks\ExtensionWithFiltersMock::class)->once()->andReturn(new \LaravelDoctrine\Tests\Mocks\ExtensionWithFiltersMock);
        $this->manager->register(\LaravelDoctrine\Tests\Mocks\ExtensionWithFiltersMock::class);

        $this->manager->boot($this->registry);

        // Should be inside booted extensions now
        $booted = $this->manager->getBootedExtensions();
        $this->assertTrue($booted['default'][\LaravelDoctrine\Tests\Mocks\ExtensionWithFiltersMock::class]);
    }

    protected function tearDown()
    {
        m::close();

        $this->manager = $this->newManager();
    }

    protected function newManager()
    {
        return new ExtensionManager($this->container);
    }
}
