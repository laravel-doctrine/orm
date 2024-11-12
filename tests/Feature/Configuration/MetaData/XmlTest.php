<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Configuration\MetaData;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\Xml;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;

class XmlTest extends TestCase
{
    protected Xml $meta;

    protected function setUp(): void
    {
        $this->meta = new Xml();

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
        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    public function testCanSpecifyExtensionWithoutError(): void
    {
        $resolved = $this->meta->resolve([
            'paths'     => 'entities',
            'extension' => '.orm.xml',
        ]);

        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    public function testCanNotSpecifyExtensionWithoutError(): void
    {
        $resolved = $this->meta->resolve(['paths' => 'entities']);

        $this->assertInstanceOf(XmlDriver::class, $resolved);
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }
}
