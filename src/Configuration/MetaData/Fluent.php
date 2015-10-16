<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\Fluent\Builders\Builder;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;

class Fluent extends MetaData
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $settings
     *
     * @return mixed
     */
    public function resolve(array $settings = [])
    {
        $driver         = new FluentDriver(array_get($settings, 'mappings'));

        $namingStrategy = $this->getNamingStrategy($settings);

        $driver->setFluentFactory(function (ClassMetadata $meta) use ($namingStrategy) {
            return new Builder(new ClassMetadataBuilder($meta), $namingStrategy);
        });

        return $driver;
    }

    /**
     * @param array $settings
     * @return mixed
     */
    protected function getNamingStrategy(array $settings = [])
    {
        return $this->container->make(array_get($settings, 'naming_strategy', LaravelNamingStrategy::class));
    }
}
