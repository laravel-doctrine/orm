<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use LaravelDoctrine\Fluent\Builders\Builder;
use LaravelDoctrine\Fluent\Extensions\ExtensibleClassMetadataFactory;
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
        $driver         = new FluentDriver(Arr::get($settings, 'mappings', []));

        $namingStrategy = $this->getNamingStrategy($settings);

        $driver->setFluentFactory(function (ClassMetadataInfo $meta) use ($namingStrategy) {
            return new Builder(new ClassMetadataBuilder($meta), $namingStrategy);
        });

        return $driver;
    }

    /**
     * @param  array $settings
     * @return mixed
     */
    protected function getNamingStrategy(array $settings = [])
    {
        return $this->container->make(Arr::get($settings, 'naming_strategy', LaravelNamingStrategy::class));
    }

    /**
     * @return string
     */
    public function getClassMetadataFactoryName()
    {
        return ExtensibleClassMetadataFactory::class;
    }
}
