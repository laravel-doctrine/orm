<?php

namespace LaravelDoctrine\ORM\Configuration\Cache;

use Closure;
use LaravelDoctrine\ORM\Configuration\Extendable;
use LaravelDoctrine\ORM\Configuration\ExtendableTrait;
use LaravelDoctrine\ORM\Exceptions\CouldNotExtend;
use LaravelDoctrine\ORM\Exceptions\DriverNotFound;

class CacheManager implements Extendable
{
    use ExtendableTrait;

    /**
     * @var array
     */
    protected $excluded = [
        'database'
    ];

    /**
     * @param array $drivers
     *
     * @throws DriverNotFound
     */
    public static function registerDrivers(array $drivers = [])
    {
        $manager = static::getInstance();

        foreach ($drivers as $name => $driver) {
            if (!in_array($name, $manager->excluded)) {
                $class = __NAMESPACE__ . '\\' . studly_case($name) . 'CacheProvider';

                if (class_exists($class)) {
                    $driver = (new $class())->configure($driver);
                    $manager->register($driver);
                } else {
                    throw new DriverNotFound("Cache driver {$name} is not supported");
                }
            }
        }
    }

    /**
     * @param          $driver
     * @param callable $callback
     * @param null     $class
     *
     * @throws CouldNotExtend
     * @return CustomCacheProvider
     */
    public function transformToDriver($driver, Closure $callback = null, $class = null)
    {
        if ($callback) {
            $result = call_user_func($callback, $this->get($driver));

            return new CustomCacheProvider($result, $driver);
        }

        if (class_exists($class)) {
            $result = new $class;

            if ($result instanceof CacheProvider) {
                $result->configure();
                $result->setName($driver);

                return $result;
            }
        }

        throw new CouldNotExtend('Expected an instance of Cache or Doctrine\Common\Cache');
    }
}
