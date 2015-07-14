<?php

namespace Brouwers\LaravelDoctrine;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Illuminate\Contracts\Container\Container;

final class IlluminateRegistry extends AbstractManagerRegistry implements ManagerRegistry
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param string    $name
     * @param array     $connections
     * @param array     $managers
     * @param string    $defaultConnection
     * @param string    $defaultManager
     * @param string    $proxyInterfaceName
     * @param Container $container
     */
    public function __construct(
        $name,
        array $connections,
        array $managers,
        $defaultConnection,
        $defaultManager,
        $proxyInterfaceName,
        Container $container
    ) {
        parent::__construct($name, $connections, $managers, $defaultConnection, $defaultManager, $proxyInterfaceName);
        $this->container = $container;
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
        return $this->container->make(self::getManagerNamePrefix() . $name);
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
        $this->container->forgetInstance(self::getManagerNamePrefix() . $name);
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
        foreach (array_keys($this->getManagers()) as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }
        throw ORMException::unknownEntityNamespace($alias);
    }

    /**
     * @param string $class
     *
     * @return mixed|object
     */
    public function getManagerForClass($class = 'default')
    {
        return $this->getService($class);
    }

    /**
     * @return string
     */
    public static function getManagerNamePrefix()
    {
        return 'doctrine.manager.';
    }

    /**
     * @return string
     */
    public static function getConnectionNamePrefix()
    {
        return 'doctrine.connection.';
    }
}
