<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use LaravelDoctrine\ORM\Exceptions\ExtensionNotFound;
use LaravelDoctrineTest\ORM\TestCase;

use function tap;

class DoctrineServiceProviderInvalidExtensionTest extends TestCase
{
    /**
     * @param Application $app
     * phpcs:disable
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], static function (Repository $config): void {
            $config->set('doctrine.extensions', ['invalid' => 'InvalalidExtension']);
        });
    }

    public function testInvalidException(): void
    {
        $this->expectException(ExtensionNotFound::class);

        $registry = $this->app->get('registry');
    }
}
