<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Doctrine\DBAL\Connection;
use Illuminate\Database\ConnectionResolverInterface;

class ConnectionManager
{
    /**
     * The registered custom driver creators.
     * @var array
     */
    protected $customCreators = [];

    /**
     * The array of created "drivers".
     * @var array
     */
    protected $drivers = [];

    /**
     * @var ConnectionResolverInterface
     */
    private $resolver;

    /**
     * @param ConnectionResolverInterface $resolver
     */
    public function __construct(ConnectionResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get a driver instance.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @param  bool                            $resolve
     * @return \Illuminate\Database\Connection
     */
    public function driver($driver = null, array $settings = [], $resolve = true)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        return $this->createDriver($driver, $settings, $resolve);
    }

    /**
     * Create a new driver instance.
     *
     * @param  string     $driver
     * @param  array      $settings
     * @return Connection
     */
    protected function createDriver($driver, array $settings = [])
    {
        // We'll check to see if a creator method exists for the given driver. If not we
        // will check for a custom driver creator, which allows developers to create
        // drivers using their own customized driver creator Closure to create it.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver, $settings);
        }

        return $this->resolver->connection($driver)->getDoctrineConnection();
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @param array  $settings
     *
     * @return mixed
     */
    protected function callCustomCreator($driver, array $settings = [])
    {
        return $this->customCreators[$driver]($settings);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param string   $driver
     * @param callable $callback
     *
     * @return $this
     */
    public function extend($driver, callable $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Get all of the created "drivers".
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->driver(), $method], $parameters);
    }

    /**
     * Get the default driver name.
     * @return string
     */
    public function getDefaultDriver()
    {
        return 'mysql';
    }
}
