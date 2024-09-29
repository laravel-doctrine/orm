<?php

declare(strict_types=1);

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
    public function __construct(protected Container $container)
    {
    }

    /** @param mixed[] $settings */
    public function resolve(array $settings = []): mixed
    {
        $driver = new FluentDriver(Arr::get($settings, 'mappings', []));

        $namingStrategy = $this->getNamingStrategy($settings);

        $driver->setFluentFactory(static function (ClassMetadataInfo $meta) use ($namingStrategy) {
            return new Builder(new ClassMetadataBuilder($meta), $namingStrategy);
        });

        return $driver;
    }

    /** @param mixed[] $settings */
    protected function getNamingStrategy(array $settings = []): mixed
    {
        return $this->container->make(Arr::get($settings, 'naming_strategy', LaravelNamingStrategy::class));
    }

    /** @param mixed[] $settings */
    protected function getQuoteStrategy(array $settings = []): mixed
    {
        return $this->container->make(Arr::get($settings, 'quote_strategy', null));
    }

    public function getClassMetadataFactoryName(): string
    {
        return ExtensibleClassMetadataFactory::class;
    }
}
