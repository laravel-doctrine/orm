<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\StaticPhp;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class StaticPhpTest extends TestCase
{
    protected StaticPhp $meta;

    protected function setUp(): void
    {
        $this->meta = new StaticPhp();

        parent::setUp();
    }

    public function testCanResolve(): void
    {
        $resolved = $this->meta->resolve([
            'paths'   => ['entities'],
            'dev'     => true,
            'proxies' => ['path' => 'path'],
        ]);

        $this->assertInstanceOf(MappingDriver::class, $resolved);
        $this->assertInstanceOf(StaticPHPDriver::class, $resolved);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
