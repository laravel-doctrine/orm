<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\MetaData;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\PHPDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Php;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class PhpTest extends TestCase
{
    protected Php $meta;

    protected function setUp(): void
    {
        $this->meta = new Php();

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
        $this->assertInstanceOf(PHPDriver::class, $resolved);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
