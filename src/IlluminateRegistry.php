<?php

namespace LaravelDoctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use ReflectionClass;

final class IlluminateRegistry implements ManagerRegistry
{
    /**
     * @const
     */
    const MANAGER_BINDING_PREFIX = 'doctrine.managers.';

    /**
     * @const
     */
    const CONNECTION_BINDING_PREFIX = 'doctrine.connections.';

    /**
     * @var string
     */
    protected $defaultManager = 'default';

    /**
     * @var string
     */
    protected $defaultConnection = 'default';

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var EntityManagerFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $managers = [];

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $managersMap = [];

    /**
     * @var array
     */
    protected $connectionsMap = [];

    /**
     * @param Container            $container
     * @param EntityManagerFactory $factory
     */
    public function __construct(Container $container, EntityManagerFactory $factory)
    {
        $this->container = $container;
        $this->factory   = $factory;
    }

    /**
     * @param       $manager
     * @param array $settings
     */
    public function addManager($manager, array $settings = [])
    {
        $this->container->singleton($this->getManagerBindingName($manager), function () use ($settings) {
            return $this->factory->create($settings);
        });

        $this->managers[$manager] = $manager;

        $this->addConnection($manager, $settings);
    }

    /**
     * @param       $connection
     * @param array $settings
     */
    public function addConnection($connection, array $settings = [])
    {
        $this->container->singleton($this->getConnectionBindingName($connection), function () use ($connection) {
            return $this->getManager($connection)->getConnection();
        });

        $this->connections[$connection] = $connection;
    }

    /**
     * Gets the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName()
    {
        if (isset($this->connections[$this->defaultConnection])) {
            return $this->defaultConnection;
        }

        return head($this->connections);
    }

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one).
     *
     * @return object
     */
    public function getConnection($name = null)
    {
        $name = $name ?: $this->getDefaultConnectionName();

        if (!$this->connectionExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Connection named "%s" does not exist.', $name));
        }

        if (isset($this->connectionsMap[$name])) {
            return $this->connectionsMap[$name];
        }

        return $this->connectionsMap[$name] = $this->getService(
            $this->getConnectionBindingName($this->connections[$name])
        );
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function connectionExists($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Gets an array of all registered connections.
     *
     * @return array An array of Connection instances.
     */
    public function getConnections()
    {
        $connections = [];
        foreach ($this->getConnectionNames() as $name) {
            $connections[$name] = $this->getConnection($name);
        }

        return $connections;
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getConnectionNames()
    {
        return $this->connections;
    }

    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName()
    {
        if (isset($this->managers[$this->defaultManager])) {
            return $this->defaultManager;
        }

        return head($this->managers);
    }

    /**
     * Gets a named object manager.
     *
     * @param string $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager($name = null)
    {
        $name = $name ?: $this->getDefaultManagerName();

        if (!$this->managerExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
        }

        if (isset($this->managersMap[$name])) {
            return $this->managersMap[$name];
        }

        return $this->managersMap[$name] = $this->getService(
            $this->getManagerBindingName($this->managers[$name])
        );
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function managerExists($name)
    {
        return isset($this->managers[$name]);
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getManagerNames()
    {
        return $this->managers;
    }

    /**
     * Gets an array of all registered object managers.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager[] An array of ObjectManager instances
     */
    public function getManagers()
    {
        $managers = [];
        foreach ($this->getManagerNames() as $name) {
            $managers[$name] = $this->getManager($name);
        }

        return $managers;
    }

    /**
     * Resets a named object manager.
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function resetManager($name = null)
    {
        $name = $name ?: $this->getDefaultManagerName();

        if (!$this->managerExists($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named "%s" does not exist.', $name));
        }

        // force the creation of a new document manager
        // if the current one is closed
        $this->resetService(
            $this->getManagerBindingName($this->managers[$name])
        );

        $this->resetService(
            $this->getConnectionBindingName($this->connections[$name])
        );

        unset($this->managersMap[$name]);
        unset($this->connectionsMap[$name]);
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     *
     * @throws ORMException
     * @return string       The full namespace.
     */
    public function getAliasNamespace($alias)
    {
        foreach ($this->getManagerNames() as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw ORMException::unknownEntityNamespace($alias);
    }

    /**
     * Gets the ObjectRepository for an persistent object.
     *
     * @param string $persistentObject      The name of the persistent object.
     * @param string $persistentManagerName The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    public function getManagerForClass($class)
    {
        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class, 2);
            $class                                  = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new ReflectionClass($class);
        if ($proxyClass->implementsInterface(Proxy::class)) {
            $class = $proxyClass->getParentClass()->getName();
        }

        foreach ($this->getManagerNames() as $name) {
            $manager = $this->getManager($name);

            if (!$manager->getMetadataFactory()->isTransient($class)) {
                foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                    if ($metadata->getName() === $class) {
                        return $manager;
                    }
                }
            }
        }
    }

    /**
     * Fetches/creates the given services.
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return object The instance of the given service.
     */
    protected function getService($name)
    {
        return $this->container->make($name);
    }

    /**
     * Resets the given services.
     * A service in this context is connection or a manager instance.
     *
     * @param string $name The name of the service.
     *
     * @return void
     */
    protected function resetService($name)
    {
        $this->container->forgetInstance($name);
    }

    /**
     * @param $manager
     *
     * @return string
     */
    protected function getManagerBindingName($manager)
    {
        return self::MANAGER_BINDING_PREFIX . $manager;
    }

    /**
     * @param $connection
     *
     * @return string
     */
    protected function getConnectionBindingName($connection)
    {
        return self::CONNECTION_BINDING_PREFIX . $connection;
    }

    /**
     * @param string $defaultManager
     */
    public function setDefaultManager($defaultManager)
    {
        $this->defaultManager = $defaultManager;
    }

    /**
     * @param string $defaultConnection
     */
    public function setDefaultConnection($defaultConnection)
    {
        $this->defaultConnection = $defaultConnection;
    }
}
