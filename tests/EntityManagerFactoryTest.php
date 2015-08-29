<?php

use Barryvdh\Debugbar\LaravelDebugbar;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Cache\CacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Repository\RepositoryFactory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\EntityManagerFactory;
use Mockery as m;

class EntityManagerFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CacheManager
     */
    protected $cache;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var CacheManager
     */
    protected $connection;

    /**
     * @var MetaDataManager
     */
    protected $meta;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var EntityManagerFactory
     */
    protected $factory;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var MappingDriver
     */
    protected $mappingDriver;

    /**
     * @var array
     */
    protected $settings = [
        'meta'       => 'annotations',
        'connection' => 'mysql',
        'paths'      => ['Entities'],
        'proxies'    => [
            'path'          => 'dir',
            'auto_generate' => false,
            'namespace'     => 'namespace'
        ],
        'repository' => 'Repo'
    ];

    protected function setUp()
    {
        $this->mockApp();
        $this->mockMeta();
        $this->mockConnection();
        $this->mockCache();
        $this->mockConfig();

        $this->factory = new EntityManagerFactory(
            $this->container,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config
        );
    }

    protected function assertEntityManager(EntityManager $manager)
    {
        $this->assertInstanceOf(EntityManager::class, $manager);
        $this->assertInstanceOf(Connection::class, $manager->getConnection());
        $this->assertInstanceOf(Configuration::class, $manager->getConfiguration());
    }

    public function test_entity_manager_gets_instantiated_correctly()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_debugbar_logger_can_be_enabled()
    {
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $this->config->shouldReceive('get')
                     ->with('doctrine.debugbar', false)
                     ->once()->andReturn(true);

        $debugbar = m::mock(LaravelDebugbar::class);
        $debugbar->shouldReceive('addCollector')->once();

        $this->container->shouldReceive('make')
                  ->with('debugbar')->once()
                  ->andReturn($debugbar);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_custom_functions_can_be_enabled()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();

        $this->configuration->shouldReceive('setCustomDatetimeFunctions')
                            ->once()->with(['datetime']);
        $this->configuration->shouldReceive('setCustomNumericFunctions')
                            ->once()->with(['numeric']);
        $this->configuration->shouldReceive('setCustomStringFunctions')
                            ->once()->with(['string']);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_second_level_caching_can_be_enabled()
    {
        $this->disableDebugbar();
        $this->disableCustomFunctions();

        $this->config->shouldReceive('get')
                     ->with('cache.second_level', false)->once()
                     ->andReturn(true);

        $this->configuration->shouldReceive('setSecondLevelCacheEnabled')
                            ->with(true)->atLeast()->once();

        $cacheConfig = m::mock(\Doctrine\ORM\Cache\CacheConfiguration::class);
        $cacheConfig->shouldReceive('setCacheFactory')->once();
        $cacheConfig->shouldReceive('getRegionsConfiguration')->once()->andReturn(
            m::mock(RegionsConfiguration::class)
        );

        $cacheFactory = m::mock(CacheFactory::class);
        $cacheFactory->shouldReceive('createCache')->atLeast()->once();
        $cacheConfig->shouldReceive('getCacheFactory')
                    ->atLeast()->once()
                    ->andReturn($cacheFactory);

        $this->configuration->shouldReceive('getSecondLevelCacheConfiguration')
                            ->atLeast()->once()->andReturn($cacheConfig);

        $cache = m::mock(Cache::class);
        $this->cache->shouldReceive('driver')->once()->andReturn($cache);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(true);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_register_paths()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $this->mappingDriver->shouldReceive('addPaths')
                            ->once()
                            ->with($this->settings['paths']);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_set_filters()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $this->settings['filters'] = [
            'name' => FilterStub::class
        ];

        $this->mappingDriver->shouldReceive('addFilter')
                            ->with('name', FilterStub::class)
                            ->once();

        $this->configuration->shouldReceive('getFilterClassName')
                            ->atLeast()->once()->andReturn(FilterStub::class);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertInstanceOf(FilterCollection::class, $manager->getFilters());
        $this->assertTrue(array_key_exists('name', $manager->getFilters()->getEnabledFilters()));
    }

    public function test_can_set_listeners()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $this->settings['events']['listeners'] = [
            'name' => ListenerStub::class
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getListeners());
        $this->assertTrue(array_key_exists('name', $manager->getEventManager()->getListeners()));
    }

    public function test_can_set_subscribers()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();

        $this->settings['events']['subscribers'] = [
            'name' => SubscriberStub::class
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getListeners());
        $this->assertTrue(array_key_exists('onFlush', $manager->getEventManager()->getListeners()));
    }

    /**
     * MOCKS
     */
    protected function mockConfig()
    {
        $this->config = m::mock(Repository::class);

        $this->config->shouldReceive('get')
                     ->with('doctrine.custom_datetime_functions')
                     ->once()->andReturn(['datetime']);

        $this->config->shouldReceive('get')
                     ->with('doctrine.custom_numeric_functions')
                     ->once()->andReturn(['numeric']);

        $this->config->shouldReceive('get')
                     ->with('doctrine.custom_string_functions')
                     ->once()->andReturn(['string']);
    }

    protected function mockCache()
    {
        $this->cache = m::mock(CacheManager::class);
    }

    protected function mockConnection()
    {
        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
                         ->once()
                         ->with('mysql')
                         ->andReturn([
                             'driver' => 'pdo_mysql'
                         ]);
    }

    protected function mockMeta()
    {
        $this->mockORMConfiguration();
        $this->meta = m::mock(MetaDataManager::class);
        $this->meta->shouldReceive('driver')
                   ->once()
                   ->andReturn($this->configuration);
    }

    protected function mockApp()
    {
        $this->container = m::mock(Container::class);
    }

    protected function disableDebugbar()
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.debugbar', false)
                     ->once()->andReturn(false);
    }

    protected function disableSecondLevelCaching()
    {
        $this->config->shouldReceive('get')
                     ->with('cache.second_level', false)->atLeast()->once()
                     ->andReturn(false);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(false);
    }

    protected function disableCustomFunctions()
    {
        $this->configuration->shouldReceive('setCustomDatetimeFunctions');
        $this->configuration->shouldReceive('setCustomNumericFunctions');
        $this->configuration->shouldReceive('setCustomStringFunctions');
    }

    protected function mockORMConfiguration()
    {
        $this->configuration = m::mock(Configuration::class);
        $this->configuration->shouldReceive('setSQLLogger');
        $this->configuration->shouldReceive('setNamingStrategy');

        $this->mappingDriver = m::mock(AnnotationDriver::class)->makePartial();
        $this->configuration->shouldReceive('getMetadataDriverImpl')
                            ->atLeast()->once()
                            ->andReturn($this->mappingDriver);

        $this->configuration->shouldReceive('setMetadataDriverImpl')
                            ->atLeast()->once();

        $this->configuration->shouldReceive('getAutoCommit')
                            ->atLeast()->once()
                            ->andReturn(true);

        $this->configuration->shouldReceive('getClassMetadataFactoryName')
                            ->atLeast()->once()
                            ->andReturn('Doctrine\ORM\Mapping\ClassMetadataFactory');

        $cache = m::mock(Cache::class);
        $this->configuration->shouldReceive('getMetadataCacheImpl')
                            ->atLeast()->once()
                            ->andReturn($cache);

        $repoFactory = m::mock(RepositoryFactory::class);
        $this->configuration->shouldReceive('getRepositoryFactory')
                            ->atLeast()->once()
                            ->andReturn($repoFactory);

        $entityListenerResolver = m::mock(EntityListenerResolver::class);
        $this->configuration->shouldReceive('getEntityListenerResolver')
                            ->atLeast()->once()
                            ->andReturn($entityListenerResolver);

        $this->configuration->shouldReceive('getProxyDir')
                            ->atLeast()->once()
                            ->andReturn('dir');

        $this->configuration->shouldReceive('setProxyDir')
                            ->atLeast()->once()
                            ->with('dir');

        $this->configuration->shouldReceive('getProxyNamespace')
                            ->atLeast()->once()
                            ->andReturn('namespace');

        $this->configuration->shouldReceive('setProxyNamespace')
                            ->atLeast()->once()
                            ->with('namespace');

        $this->configuration->shouldReceive('getAutoGenerateProxyClasses')
                            ->atLeast()->once()
                            ->andReturn(false);

        $this->configuration->shouldReceive('setAutoGenerateProxyClasses')
                            ->atLeast()->once()
                            ->with(false);

        $this->configuration->shouldReceive('setDefaultRepositoryClassName')
                            ->once()
                            ->with('Repo');
    }

    protected function tearDown()
    {
        m::close();
    }
}

class FilterStub
{
}

class ListenerStub
{
}

class SubscriberStub implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush'
        ];
    }
}
