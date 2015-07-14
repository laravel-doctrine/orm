<?php

namespace Brouwers\LaravelDoctrine\Configuration\Connections;

use Brouwers\LaravelDoctrine\Configuration\Extendable;
use Brouwers\LaravelDoctrine\Configuration\ExtendableTrait;
use Brouwers\LaravelDoctrine\Exceptions\CouldNotExtend;
use Brouwers\LaravelDoctrine\Exceptions\DriverNotFound;
use Closure;

class ConnectionManager implements Extendable
{
    use ExtendableTrait;

    /**
     * @param $drivers
     *
     * @throws DriverNotFound
     */
    public static function registerConnections(array $drivers)
    {
        $manager = static::getInstance();

        foreach ($drivers as $name => $driver) {
            $class = __NAMESPACE__ . '\\' . studly_case($name) . 'Connection';

            if (class_exists($class)) {
                $driver = (new $class())->configure($driver);
                $manager->register($driver);
            } else {
                throw new DriverNotFound("Connection {$name} is not supported");
            }
        }
    }

    /**
     * @param         $driver
     * @param Closure $callback
     * @param null    $class
     *
     * @throws CouldNotExtend
     * @return Connection
     */
    public function transformToDriver($driver, Closure $callback = null, $class = null)
    {
        if ($callback) {
            $result = call_user_func($callback, $this->get($driver));

            return new CustomConnection($result, $driver);
        }

        if (class_exists($class)) {
            $result = new $class;

            if ($result instanceof Connection) {
                $result = $result->configure();
                $result->setName($driver);

                return $result;
            }
        }

        throw new CouldNotExtend('Expected an instance of Connection or Doctrine\ORM\Configuration');
    }
}
