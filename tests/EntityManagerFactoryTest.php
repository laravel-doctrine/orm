<?php

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection as DoctrinePrimaryReadReplicaConnection;
use Doctrine\ORM\Cache\CacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Configuration\Cache\CacheManager;
use LaravelDoctrine\ORM\Configuration\Cache\IlluminateCacheAdapter;
use LaravelDoctrine\ORM\Configuration\Connections\ConnectionManager;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\EntityManagerFactory;
use LaravelDoctrine\ORM\Loggers\Logger;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver as LaravelDoctrineEntityListenerResolver;
use LaravelDoctrine\ORM\Testing\ConfigRepository;
use Mockery as m;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class EntityManagerFactoryTest extends TestCase
{
    /**
     * @var CacheManager|Mock
     */
    protected $cache;

    /**
     * @var Repository|Mock
     */
    protected $config;

    /**
     * @var ConnectionManager
     */
    protected $connection;

    /**
     * @var MetaDataManager
     */
    protected $meta;

    /**
     * @var Container|Mock
     */
    protected $container;

    /**
     * @var EntityManagerFactory
     */
    protected $factory;

    /**
     * @var Configuration|Mock
     */
    protected $configuration;

    /**
     * @var LaravelDoctrineEntityListenerResolver|Mock
     */
    protected $listenerResolver;

    /**
     * @var MappingDriver
     */
    protected $mappingDriver;

    protected $setup;

    /**
     * @var array
     */
    protected $caches = [ 'query', 'result', 'metadata' ];

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

    protected function setUp(): void
    {
        $this->mockApp();
        $this->mockMeta();
        $this->mockConnection();
        $this->mockCache();
        $this->mockResolver();
        $this->mockConfig();

        $this->setup = m::mock(Setup::class);
        $this->setup->shouldReceive('createConfiguration')->once()->andReturn($this->configuration);

        $this->factory = new EntityManagerFactory(
            $this->container,
            $this->setup,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config,
            $this->listenerResolver
        );
    }

    protected function assertEntityManager(EntityManagerInterface $manager)
    {
        $this->assertInstanceOf(EntityManagerInterface::class, $manager);
        $this->assertInstanceOf(Connection::class, $manager->getConnection());
        $this->assertInstanceOf(Configuration::class, $manager->getConfiguration());
    }

    public function test_entity_manager_gets_instantiated_correctly()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_debugbar_logger_can_be_enabled()
    {
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->config->shouldReceive('get')
                     ->with('doctrine.logger', false)
                     ->twice()->andReturn('LoggerMock');

        $logger = m::mock(Logger::class);

        $this->container->shouldReceive('make')
                        ->with('LoggerMock')->once()
                        ->andReturn($logger);

        $logger->shouldReceive('register')->once();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_custom_functions_can_be_enabled()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->enableLaravelNamingStrategy();

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
        $this->enableLaravelNamingStrategy();
        $this->disableCustomCacheNamespace();

        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.second_level', false)->once()
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

        $cacheImpl = m::mock(Cache::class);
        $this->cache->shouldReceive('driver')
                    ->once()->andReturn($cacheImpl);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(true);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_custom_cache_namespace_can_be_set()
    {
        $this->disableDebugbar();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();
        $this->disableSecondLevelCaching();

        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.namespace')
                     ->andReturn('namespace');

        foreach ($this->caches as $cache) {
            $this->config->shouldNotReceive('get')
                         ->with('doctrine.cache.' . $cache, [])
                         ->once()
                         ->andReturn([
                             'namespace' => $cache,
                         ])->byDefault();
        }

        $cache = m::mock(Cache::class);

        $this->cache->shouldReceive('driver')
                ->withArgs(function ($driver, $settings) {
                    return $settings['namespace'] === 'namespace';
                })
                ->andReturn($cache);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_register_paths()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_set_filters()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['filters'] = [
            'name' => FilterStub::class
        ];

        $this->configuration->shouldReceive('addFilter')
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
        $this->container->shouldReceive('make')
                ->with(ListenerStub::class)
                ->once()
                ->andReturn(new ListenerStub());

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = [
            'name' => ListenerStub::class
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getListeners());
        $this->assertTrue(array_key_exists('name', $manager->getEventManager()->getListeners()));
    }

    public function test_can_set_multiple_listeners()
    {
        $this->container->shouldReceive('make')
                        ->with(ListenerStub::class)
                        ->once()
                        ->andReturn(new ListenerStub())
                        ->shouldReceive('make')
                        ->with(AnotherListenerStub::class)
                        ->once()
                        ->andReturn(new AnotherListenerStub());

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = [
            'name' => [
                ListenerStub::class,
                AnotherListenerStub::class
            ]
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getListeners());
        $this->assertTrue(array_key_exists('name', $manager->getEventManager()->getListeners()));
        $this->assertCount(2, $manager->getEventManager()->getListeners('name'));
    }

    public function test_setting_non_existent_listener_throws_exception()
    {
        $reflectionException = new ReflectionException();

        $this->container->shouldReceive('make')
                ->with('ClassDoesNotExist')
                ->once()
                ->andThrow($reflectionException);

        $this->expectException(InvalidArgumentException::class);

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['listeners'] = [
            'name' => 'ClassDoesNotExist'
        ];

        $this->factory->create($this->settings);
    }

    public function test_can_set_subscribers()
    {
        $this->container->shouldReceive('make')
                ->with(SubscriberStub::class)
                ->once()
                ->andReturn(new SubscriberStub);

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['subscribers'] = [
            'name' => SubscriberStub::class
        ];

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertCount(1, $manager->getEventManager()->getListeners());
        $this->assertTrue(array_key_exists('onFlush', $manager->getEventManager()->getListeners()));
    }

    public function test_setting_non_existent_subscriber_throws_exception()
    {
        $reflectionException = new ReflectionException();

        $this->container->shouldReceive('make')
                        ->with('ClassDoesNotExist')
                        ->once()
                        ->andThrow($reflectionException);

        $this->expectException(InvalidArgumentException::class);

        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['events']['subscribers'] = [
            'name' => 'ClassDoesNotExist'
        ];

        $this->factory->create($this->settings);
    }

    public function test_can_set_custom_naming_strategy()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();

        $this->settings['naming_strategy'] = 'Doctrine\ORM\Mapping\DefaultNamingStrategy';

        $strategy = m::mock('Doctrine\ORM\Mapping\DefaultNamingStrategy');

        $this->container->shouldReceive('make')
                        ->with('Doctrine\ORM\Mapping\DefaultNamingStrategy')
                        ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setNamingStrategy')->once()->with($strategy);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_set_custom_quote_strategy()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['quote_strategy'] = 'Doctrine\ORM\Mapping\AnsiQuoteStrategy';

        $strategy = m::mock('Doctrine\ORM\Mapping\AnsiQuoteStrategy');

        $this->container->shouldReceive('make')
            ->with('Doctrine\ORM\Mapping\AnsiQuoteStrategy')
            ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setQuoteStrategy')->once()->with($strategy);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_can_decorate_the_entity_manager()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['decorator'] = Decorator::class;

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
        $this->assertInstanceOf(Decorator::class, $manager);
        $this->assertInstanceOf(EntityManagerDecorator::class, $manager);
    }

    public function test_can_set_repository_factory()
    {
        $this->disableDebugbar();
        $this->disableSecondLevelCaching();
        $this->disableCustomCacheNamespace();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['repository_factory'] = 'RepositoryFactory';

        $repositoryFactory = m::mock(RepositoryFactory::class);

        $this->container->shouldReceive('make')
            ->with('RepositoryFactory')
            ->once()->andReturn($repositoryFactory);

        $this->configuration->shouldReceive('setRepositoryFactory')
            ->once()
            ->with($repositoryFactory);

        $manager = $this->factory->create($this->settings);

        $this->assertEntityManager($manager);
    }

    public function test_illuminate_cache_provider_custom_store()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'driver' => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'illuminate',
                        'store'  => 'myStoreName'
                    ]
                ]
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $cache = M::mock(Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(\Illuminate\Contracts\Cache\Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Illuminate\Contracts\Cache\Factory::class, function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(IlluminateCacheAdapter::class, $manager->getConfiguration()->getMetadataCacheImpl());
    }

    public function test_illuminate_cache_provider_redis()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'driver' => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'redis',
                    ]
                ]
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $cache = M::mock(Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(\Illuminate\Contracts\Cache\Factory::class);
        $factory->shouldReceive('store')->with('redis')->andReturn($cache);

        $container->singleton(Illuminate\Contracts\Cache\Factory::class, function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(IlluminateCacheAdapter::class, $manager->getConfiguration()->getMetadataCacheImpl());
    }

    public function test_illuminate_cache_provider_invalid_store()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'driver' => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'illuminate',
                    ]
                ]
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $cache = M::mock(Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(\Illuminate\Contracts\Cache\Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Illuminate\Contracts\Cache\Factory::class, function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Please specify the `store` when using the "illuminate" cache driver.');
        $factory->create($config->get('doctrine'));
    }

    public function test_php_file_cache_custom_path()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'driver' => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],

                'cache' => [
                    'metadata' => [
                        'driver' => 'php_file',
                        'path'   => 'myCustomPath'
                    ]
                ]
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $cache = M::mock(Illuminate\Contracts\Cache\Repository::class);

        $factory = M::mock(\Illuminate\Contracts\Cache\Factory::class);
        $factory->shouldReceive('store')->with('myStoreName')->andReturn($cache);

        $container->singleton(Illuminate\Contracts\Cache\Factory::class, function () use ($factory) {
            return $factory;
        });

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(\Symfony\Component\Cache\Adapter\PhpFilesAdapter::class, $manager->getConfiguration()->getMetadataCache());
        //$this->assertStringEndsWith('myCustomPath', $manager->getConfiguration()->getMetadataCache()->getNamespace());
    }

    public function test_wrapper_connection()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'wrapperClass' => FakeConnection::class,
                'driver'       => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(FakeConnection::class, $manager->getConnection());
    }

    public function test_custom_event_manager()
    {
        m::resetContainer();

        $config = new ConfigRepository([
            'database.connections.mysql' => [
                'driver'       => 'mysql'
            ],
            'doctrine' => [
                'meta'       => 'annotations',
                'connection' => 'mysql',
                'paths'      => ['Entities'],
                'proxies'    => [
                    'path'          => 'dir',
                    'auto_generate' => false,
                    'namespace'     => 'namespace'
                ],
                'event_manager' => 'my_event_manager'
            ],
            'doctrine.custom_datetime_functions' => [],
            'doctrine.custom_numeric_functions'  => [],
            'doctrine.custom_string_functions'   => []
        ]);

        $container = new \Illuminate\Container\Container();
        $container->singleton(Repository::class, function () use ($config) {
            return $config;
        });

        $container->alias(FakeEventManager::class, 'my_event_manager');

        $factory = new EntityManagerFactory(
            $container,
            new Setup(),
            new MetaDataManager($container),
            new ConnectionManager($container),
            new CacheManager($container),
            $config,
            new EntityListenerResolver($container)
        );

        $manager = $factory->create($config->get('doctrine'));

        $this->assertInstanceOf(FakeEventManager::class, $manager->getEventManager());
    }

    /**
     * MOCKS
     *
     * @param array $driverConfig
     * @param bool  $strictCallCountChecking
     */
    protected function mockConfig($driverConfig = ['driver' => 'mysql'], $strictCallCountChecking = true)
    {
        $this->config = m::mock(Repository::class);

        $this->config->shouldReceive('get')
            ->with('doctrine.cache.default', 'array')
            ->andReturn('array');


        foreach ($this->caches as $cache) {
            $expectation = $this->config->shouldReceive('get')
                         ->with('doctrine.cache.' . $cache, [])
                         ->andReturn(['driver' => 'array']);

            $strictCallCountChecking ? $expectation->once() : $expectation->never();
        }

        $this->config->shouldReceive('has')
                     ->with('database.connections.mysql')
                     ->once()
                     ->andReturn(true);

        $this->config->shouldReceive('get')
                     ->with('database.connections.mysql')
                     ->once()
                     ->andReturn($driverConfig);

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_datetime_functions')
                     ->andReturn(['datetime']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_numeric_functions')
                     ->andReturn(['numeric']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_string_functions')
                     ->andReturn(['string']);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

        $expectation = $this->config->shouldReceive('get')
                     ->with('doctrine.custom_hydration_modes', [])
                     ->andReturn([]);

        $strictCallCountChecking ? $expectation->once() : $expectation->never();

    }

    protected function mockCache()
    {
        $this->cache = m::mock(CacheManager::class);

        $this->cache->shouldReceive('driver')
                    ->times(count($this->caches)) // one for each cache driver
                    ->andReturn(new ArrayAdapter());
    }

    protected function mockConnection()
    {
        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
                         ->once()
                         ->with('mysql', [
                             'driver' => 'mysql'
                         ])
                         ->andReturn([
                             'driver' => 'pdo_mysql'
                         ]);
    }

    protected function mockMeta()
    {
        $this->mappingDriver = m::mock(MappingDriver::class);
        $this->mappingDriver->shouldReceive('addPaths')->with($this->settings['paths']);

        $this->mockORMConfiguration();

        $this->meta = m::mock(MetaDataManager::class);
        $this->meta->shouldReceive('driver')
                   ->once()
                   ->andReturn($this->mappingDriver);
    }

    protected function mockApp()
    {
        $this->container = m::mock(Container::class);
    }

    protected function mockResolver()
    {
        $this->listenerResolver = m::mock(LaravelDoctrineEntityListenerResolver::class);
    }

    protected function disableDebugbar()
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.logger', false)
                     ->once()->andReturn(false);
    }

    protected function disableSecondLevelCaching()
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.second_level', false)->atLeast()->once()
                     ->andReturn(false);

        $this->configuration->shouldReceive('isSecondLevelCacheEnabled')
                            ->atLeast()->once()
                            ->andReturn(false);
    }

    protected function disableCustomCacheNamespace()
    {
        $this->config->shouldReceive('get')
                     ->with('doctrine.cache.namespace')
                     ->atLeast()->once()
                     ->andReturn(null);
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

        $this->configuration->shouldReceive('getMetadataDriverImpl')
                            ->andReturn($this->mappingDriver);

        $this->configuration->shouldReceive('setMetadataDriverImpl')
                            ->atLeast()->once();

        $this->configuration->shouldReceive('getAutoCommit')
                            ->atLeast()->once()
                            ->andReturn(true);

        $this->configuration->shouldReceive('getClassMetadataFactoryName')
                            ->atLeast()->once()
                            ->andReturn('Doctrine\ORM\Mapping\ClassMetadataFactory');

        $this->configuration->shouldReceive('setMetadataCache')->once();
        $this->configuration->shouldReceive('setQueryCache')->once();
        $this->configuration->shouldReceive('setResultCache')->once();
        $this->configuration->shouldReceive('getMiddlewares')->once();

        $this->configuration->shouldReceive('getMetadataCache')->zeroOrMoreTimes();

        $cache = m::mock(Cache::class);
        $this->configuration->shouldReceive('getMetadataCacheImpl')
                            ->atLeast()->once()
                            ->andReturn($cache);

        $repoFactory = m::mock(RepositoryFactory::class);
        $this->configuration->shouldReceive('getRepositoryFactory')
                            ->atLeast()->once()
                            ->andReturn($repoFactory);

        $entityListenerResolver = m::mock(LaravelDoctrineEntityListenerResolver::class);
        $this->configuration->shouldReceive('getEntityListenerResolver')
                            ->atLeast()->once()
                            ->andReturn($entityListenerResolver);

        $this->configuration->shouldReceive('setEntityListenerResolver')
                            ->atLeast()->once()
                            ->with(m::type(LaravelDoctrineEntityListenerResolver::class));

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

    protected function enableLaravelNamingStrategy()
    {
        $strategy = m::mock(LaravelNamingStrategy::class);

        $this->container->shouldReceive('make')
                        ->with(LaravelNamingStrategy::class)
                        ->once()->andReturn($strategy);

        $this->configuration->shouldReceive('setNamingStrategy')->once()->with($strategy);
    }

    /**
     * Data provider for testMasterSlaveConnection.
     *
     * @return array
     */
    public function getTestMasterSlaveConnectionData()
    {
        $out = [];

        // Case #0. Simple valid configuration, everything should go well.
        $out[] = [$this->getDummyBaseInputConfig()];

        //Case #1. No read DBs set.
        $inputConfig = $this->getDummyBaseInputConfig();
        unset($inputConfig['read']);

        $out[] = [
            $inputConfig,
            \InvalidArgumentException::class,
            "Parameter 'read' must be set for read/write config."
        ];

        //Case #2. 'read' isn't an array
        $inputConfig         = $this->getDummyBaseInputConfig();
        $inputConfig['read'] = 'test';

        $out[] = [
            $inputConfig,
            \InvalidArgumentException::class,
            "Parameter 'read' must be an array containing multiple arrays."
        ];

        //Case #3. 'read' has non array entries.
        $inputConfig           = $this->getDummyBaseInputConfig();
        $inputConfig['read'][] = 'test';

        $out[] = [
            $inputConfig,
            \InvalidArgumentException::class,
            "Parameter 'read' must be an array containing multiple arrays."
        ];

        //Case #4. 'read' has empty entries.
        $inputConfig           = $this->getDummyBaseInputConfig();
        $inputConfig['read'][] = [];

        $out[] = [
            $inputConfig,
            \InvalidArgumentException::class,
            "Parameter 'read' config no. 2 is empty."
        ];

        //Case #5. 'read' has empty first entry. (reported by maxbrokman.)
        $inputConfig            = $this->getDummyBaseInputConfig();
        $inputConfig['read'][0] = [];

        $out[] = [
            $inputConfig,
            \InvalidArgumentException::class,
            "Parameter 'read' config no. 0 is empty."
        ];

        return $out;
    }

    /**
     * Check if config is handled correctly.
     *
     * @param array  $inputConfig
     * @param string $expectedException
     * @param string $msg
     *
     * @dataProvider getTestMasterSlaveConnectionData
     */
    public function testMasterSlaveConnection(
        array $inputConfig,
        $expectedException = '',
        $msg = ''
    ) {
        m::resetContainer();

        $this->mockApp();
        $this->mockResolver();
        $this->mockConfig($inputConfig, empty($expectedException));

//        $this->cache = m::mock(CacheManager::class);
//        $this->cache->shouldReceive('driver')
//            ->times(empty($expectedException) ? 3 : 0)
//            ->andReturn(new ArrayAdapter());

        $this->setup = m::mock(Setup::class);
        $this->setup->shouldReceive('createConfiguration')->once()->andReturn($this->configuration);

        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
            ->once()
            ->with('mysql', $inputConfig)
            ->andReturn(['driver' => 'pdo_mysql']);

        $factory = new EntityManagerFactory(
            $this->container,
            $this->setup,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config,
            $this->listenerResolver
        );

        if (!empty($expectedException)) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($msg);
        } else {
            $this->disableDebugbar();
            $this->disableCustomCacheNamespace();
            $this->disableSecondLevelCaching();
            $this->disableCustomFunctions();
            $this->enableLaravelNamingStrategy();
        }

        $this->settings['connection'] = 'mysql';
        $factory->create($this->settings);

        $this->assertTrue(true);
    }

    /**
     * doctrine/dbal 2.11 has MasterSlaveConnection class deprecated in favour of PrimaryReadReplicaConnection
     */
    public function testPrimaryReadReplicaConnectionIsUsedWhenAvailable()
    {
        if (!class_exists(DoctrinePrimaryReadReplicaConnection::class)) {
            $this->markTestSkipped('Skipped for doctrine/dbal < 2.11');
        }

        m::resetContainer();

        $this->mockApp();
        $this->mockResolver();
        $this->mockConfig($this->getDummyBaseInputConfig());

        $this->cache = m::mock(CacheManager::class);
        $this->cache->shouldReceive('driver')
            ->times(3)
            ->andReturn(new ArrayAdapter());

        $this->setup = m::mock(Setup::class);
        $this->setup->shouldReceive('createConfiguration')->once()->andReturn($this->configuration);

        $this->connection = m::mock(ConnectionManager::class);
        $this->connection->shouldReceive('driver')
            ->once()
            ->with('mysql', $this->getDummyBaseInputConfig())
            ->andReturn(['driver' => 'pdo_mysql']);

        $factory = new EntityManagerFactory(
            $this->container,
            $this->setup,
            $this->meta,
            $this->connection,
            $this->cache,
            $this->config,
            $this->listenerResolver
        );

        $this->disableDebugbar();
        $this->disableCustomCacheNamespace();
        $this->disableSecondLevelCaching();
        $this->disableCustomFunctions();
        $this->enableLaravelNamingStrategy();

        $this->settings['connection'] = 'mysql';
        $entityManager                = $factory->create($this->settings);

        $this->assertInstanceOf(DoctrinePrimaryReadReplicaConnection::class, $entityManager->getConnection());
    }

    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Returns dummy base config for testing.
     *
     * @return array
     */
    private function getDummyBaseInputConfig()
    {
        return [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'test',
            'username'  => 'homestead',
            'password'  => 'secret',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
            'write'     => [
                'port' => 3307,
            ],
            'read' => [
                [
                    'port'     => 3308,
                    'database' => 'test2',
                ],
                [
                    'host' => 'localhost2',
                    'port' => 3309
                ],
            ],
        ];
    }
}

class FakeEventManager extends \Doctrine\Common\EventManager
{
}

class FakeConnection extends Connection
{
}

class FilterStub
{
}

class ListenerStub
{
}

class AnotherListenerStub
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

class Decorator extends EntityManagerDecorator
{
}
