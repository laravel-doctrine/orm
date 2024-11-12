<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ManagerRegistry;
use LaravelDoctrineTest\ORM\TestCase;

class DoctrineServiceProviderTest extends TestCase
{
    public function testRegistryIsRegistered(): void
    {
        $registry = $this->app->get('registry');

        $this->assertInstanceOf(
            ManagerRegistry::class,
            $registry,
        );
    }

    public function testEntityManagerSingleton(): void
    {
        $em1 = $this->app->get('em');
        $em2 = $this->app->get('em');

        $this->assertSame($em1, $em2);
    }

    public function testMetaDataFactory(): void
    {
        $metaDataFactory = $this->app->get(ClassMetadataFactory::class);

        $this->assertInstanceOf(
            ClassMetadataFactory::class,
            $metaDataFactory,
        );
    }
}
