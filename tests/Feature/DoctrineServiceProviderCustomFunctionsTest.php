<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use LaravelDoctrineTest\ORM\TestCase;

use function tap;

class DoctrineServiceProviderCustomFunctionsTest extends TestCase
{
    /**
     * @param Application $app
     * phpcs:disable
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], static function (Repository $config): void {
            // Custom functions are tested in the extensions repository
        });
    }

    public function testRegistryIsRegistered(): void
    {
        $registry = $this->app->get('registry');

        $this->assertInstanceOf(
            ManagerRegistry::class,
            $registry,
        );
    }
}
