<?php

namespace LaravelDoctrine\ORM\Configuration\MetaData;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use LaravelDoctrine\Fluent\Builders\Builder;
use LaravelDoctrine\Fluent\Extensions\ExtensibleClassMetadataFactory;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\ORM\Configuration\LaravelNamingStrategy;

class Fluent extends MetaData
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $settings
     *
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function resolve(array $settings = []): FluentDriver
    {
        $driver         = new FluentDriver(Arr::get($settings, 'mappings', []));

        $namingStrategy = $this->getNamingStrategy($settings);

        $driver->setFluentFactory(function (ClassMetadata $meta) use ($namingStrategy) {
            return new Builder(new ClassMetadataBuilder($meta), $namingStrategy);
        });

        return $driver;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getNamingStrategy(array $settings = []): mixed
    {
        return $this->container->make(Arr::get($settings, 'naming_strategy', LaravelNamingStrategy::class));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getQuoteStrategy(array $settings = []): mixed
    {
        return $this->container->make(Arr::get($settings, 'quote_strategy'));
    }

    public function getClassMetadataFactoryName(): string
    {
        return ExtensibleClassMetadataFactory::class;
    }
}
