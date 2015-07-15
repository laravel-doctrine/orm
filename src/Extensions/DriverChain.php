<?php

namespace LaravelDoctrine\ORM\Extensions;

use Closure;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;

class DriverChain
{
    /**
     * @var MappingDriver
     */
    protected $driver;

    /**
     * @var MappingDriverChain
     */
    protected $chain;

    /**
     * @var self
     */
    protected $instance;

    /**
     * @param MappingDriver $driver
     */
    public function __construct(MappingDriver $driver)
    {
        $this->setDriver($driver);
        $this->chain = new MappingDriverChain();
        $this->chain->setDefaultDriver($this->getDriver());

        $this->boot();
    }

    /**
     * Boot the driver chain
     */
    public function boot()
    {
        event('doctrine.driver-chain::booted', [
            $this
        ]);
    }

    /**
     * @param Closure $callback
     */
    public static function booted(Closure $callback)
    {
        app('events')->listen('doctrine.driver-chain::booted', $callback);
    }

    /**
     * @param array $paths
     *
     * @return $this
     */
    public function addPaths(array $paths = [])
    {
        if (method_exists($this->getDriver(), 'addPaths')) {
            $this->getDriver()->addPaths($paths);
        } elseif (method_exists($this->getDriver(), 'getLocator')) {
            $this->getDriver()->getLocator()->addPaths($paths);
        }

        return $this;
    }

    /**
     * @param MappingDriver $driver
     * @param               $namespace
     *
     * @return $this
     */
    public function addDriver(MappingDriver $driver, $namespace)
    {
        $this->getChain()->addDriver($driver, $namespace);

        return $this;
    }

    /**
     * @param $namespace
     *
     * @return $this
     */
    public function addNamespace($namespace)
    {
        $this->getChain()->addDriver($this->getDriver(), $namespace);

        return $this;
    }

    /**
     * @return MappingDriver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param MappingDriver $driver
     */
    public function setDriver(MappingDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return MappingDriverChain
     */
    public function getChain()
    {
        return $this->chain;
    }

    /**
     * @param MappingDriverChain $chain
     */
    public function setChain($chain)
    {
        $this->chain = $chain;
    }

    /**
     * @return mixed
     */
    public function getReader()
    {
        if (method_exists($this->getDriver(), 'getReader')) {
            return $this->getDriver()->getReader();
        }
    }
}
