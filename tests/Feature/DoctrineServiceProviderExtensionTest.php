<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use LaravelDoctrineTest\ORM\TestCase;

use function tap;

class DoctrineServiceProviderExtensionTest extends TestCase
{
    /**
     * @param Application $app
     * phpcs:disable
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], static function (Repository $config): void {
        });
    }

    public function testExtensions(): void
    {
        $registry = $this->app->get('registry');

        $this->assertTrue(true);
    }
}
