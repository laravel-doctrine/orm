<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Attributes;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class AttributesTest extends TestCase
{
    protected Attributes $meta;

    protected function setUp(): void
    {
        $this->meta = new Attributes();

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
        $this->assertInstanceOf(AttributeDriver::class, $resolved);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
