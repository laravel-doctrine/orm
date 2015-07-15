<?php

namespace LaravelDoctrine\ORM\Configuration\Connections;

use Closure;
use LaravelDoctrine\ORM\Configuration\Extendable;
use LaravelDoctrine\ORM\Configuration\ExtendableTrait;
use LaravelDoctrine\ORM\Exceptions\CouldNotExtend;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

class ConnectionManager implements Extendable
{
    use ExtendableTrait;

    /**
     * @param $connections
     *
     * @throws DriverNotFound
     */
    public static function registerConnections(array $connections)
    {
        $manager = static::getInstance();

        foreach ($connections as $name => $connection) {
            $class = __NAMESPACE__ . '\\' . studly_case($connection['driver']) . 'Connection';

            if (class_exists($class)) {
                $driver = (new $class())->configure($connection);
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
